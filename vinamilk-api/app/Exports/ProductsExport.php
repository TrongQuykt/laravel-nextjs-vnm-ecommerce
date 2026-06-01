<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Database\Eloquent\Builder;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->query->with(['category', 'brand'])->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Tên sản phẩm',
            'Slug',
            'Danh mục',
            'Thương hiệu',
            'Giá',
            'Giá sale',
            'Trạng thái',
            'Nổi bật',
            'Ngày tạo',
        ];
    }

    /**
     * @param Product $product
     * @return array
     */
    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->slug,
            $product->category->name ?? 'N/A',
            $product->brand->name ?? 'N/A',
            number_format($product->price, 0, ',', '.') . ' đ',
            number_format($product->sale_price ?? 0, 0, ',', '.') . ' đ',
            $product->status,
            $product->is_home_featured ? 'Có' : 'Không',
            $product->created_at->format('d/m/Y H:i'),
        ];
    }
}
