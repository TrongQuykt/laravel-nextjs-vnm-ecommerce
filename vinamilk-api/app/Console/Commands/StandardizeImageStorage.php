<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\ProductImage;

class StandardizeImageStorage extends Command
{
    protected $signature = 'storage:standardize {--dry-run : Run without moving files}';
    protected $description = 'Renames and reorganizes product images based on product slug and removes orphans.';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $usedFiles = [];

        $this->info("Collecting referenced files from database...");

        // 1. Collect from Products
        $products = Product::all();
        foreach ($products as $product) {
            $slug = Str::slug($product->name);

            // Main Image
            if ($product->main_image) {
                $newPath = "products/main/{$slug}-main." . pathinfo($product->main_image, PATHINFO_EXTENSION);
                $this->processFile($product->main_image, $newPath, 'products', 'main_image', $product->id, $dryRun, $usedFiles);
            }

            // Features Image
            if ($product->features_main_image) {
                $newPath = "products/features/{$slug}-features." . pathinfo($product->features_main_image, PATHINFO_EXTENSION);
                $this->processFile($product->features_main_image, $newPath, 'products', 'features_main_image', $product->id, $dryRun, $usedFiles);
            }

            // Description Image
            if ($product->description_image) {
                $newPath = "products/description/{$slug}-description." . pathinfo($product->description_image, PATHINFO_EXTENSION);
                $this->processFile($product->description_image, $newPath, 'products', 'description_image', $product->id, $dryRun, $usedFiles);
            }

            // Gallery
            $galleryImages = ProductImage::where('product_id', $product->id)->orderBy('position')->get();
            foreach ($galleryImages as $index => $img) {
                $num = $index + 1;
                $newPath = "products/gallery/{$slug}-gallery-{$num}." . pathinfo($img->path, PATHINFO_EXTENSION);
                $this->processFile($img->path, $newPath, 'product_images', 'path', $img->id, $dryRun, $usedFiles);
            }
        }

        // 2. Collect from other tables (to avoid deleting icons/banners)
        $this->collectUsedFiles('special_highlights', 'icon', $usedFiles);
        $this->collectUsedFiles('brands', 'logo', $usedFiles);
        $this->collectUsedFiles('banners', 'image', $usedFiles);
        $this->collectUsedFiles('blogs', 'thumbnail', $usedFiles);

        // 3. Cleanup Orphan Files
        $this->info("\nChecking for orphan files...");
        $allFiles = Storage::disk('public')->allFiles();
        $orphanCount = 0;

        foreach ($allFiles as $file) {
            // Normalize path for comparison
            if (!in_array($file, $usedFiles)) {
                // Ignore temporary files and .gitignore
                if (str_starts_with($file, 'livewire-tmp') || basename($file) === '.gitignore' || basename($file) === '.htaccess') {
                    continue;
                }

                $this->warn("Orphan detected: {$file}");
                if (!$dryRun) {
                    Storage::disk('public')->delete($file);
                }
                $orphanCount++;
            }
        }

        $this->info("\nCleanup completed. Orphans removed: {$orphanCount}");
    }

    private function processFile($oldPath, $newPath, $table, $column, $id, $dryRun, &$usedFiles)
    {
        if (!Storage::disk('public')->exists($oldPath)) {
            $this->error("File not found: {$oldPath} (ID: {$id} in {$table})");
            return;
        }

        if ($oldPath === $newPath) {
            $this->line("Skipping (already correct): {$oldPath}");
            $usedFiles[] = $oldPath;
            return;
        }

        $this->info("Moving: {$oldPath} -> {$newPath}");

        if (!$dryRun) {
            // Ensure directory exists
            Storage::disk('public')->makeDirectory(dirname($newPath));
            
            // Move file
            Storage::disk('public')->move($oldPath, $newPath);

            // Update DB
            DB::table($table)->where('id', $id)->update([$column => $newPath]);
        }

        $usedFiles[] = $newPath;
    }

    private function collectUsedFiles($table, $column, &$usedFiles)
    {
        if (Schema::hasTable($table)) {
            $files = DB::table($table)->whereNotNull($column)->pluck($column)->toArray();
            foreach ($files as $file) {
                $usedFiles[] = $file;
            }
        }
    }
}
