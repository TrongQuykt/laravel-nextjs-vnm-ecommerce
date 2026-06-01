<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Database\Eloquent\Builder;

class OrdersExport implements FromCollection, WithHeadings, WithMapping
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
        return $this->query->with(['user', 'items'])->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Mã đơn hàng',
            'Khách hàng',
            'Email',
            'Số điện thoại',
            'Tổng tiền',
            'Trạng thái',
            'Trạng thái thanh toán',
            'Ngày tạo',
        ];
    }

    /**
     * @param Order $order
     * @return array
     */
    public function map($order): array
    {
        return [
            $order->id,
            $order->order_number,
            $order->user->name ?? 'N/A',
            $order->user->email ?? 'N/A',
            $order->user->phone ?? 'N/A',
            number_format($order->total_amount, 0, ',', '.') . ' đ',
            $order->status,
            $order->payment_status,
            $order->created_at->format('d/m/Y H:i'),
        ];
    }
}
