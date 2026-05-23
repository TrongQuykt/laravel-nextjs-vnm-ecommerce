<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    
    // Truncate all tables related to orders
    DB::table('order_items')->truncate();
    if (DB::getSchemaBuilder()->hasTable('order_status_logs')) {
        DB::table('order_status_logs')->truncate();
    }
    if (DB::getSchemaBuilder()->hasTable('payment_logs')) {
        DB::table('payment_logs')->truncate();
    }
    if (DB::getSchemaBuilder()->hasTable('payments')) {
        DB::table('payments')->truncate();
    }
    DB::table('orders')->truncate();
    
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    echo "SUCCESS: Cleared all order database data successfully!\n";
} catch (\Exception $e) {
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    echo "ERROR: " . $e->getMessage() . "\n";
}
