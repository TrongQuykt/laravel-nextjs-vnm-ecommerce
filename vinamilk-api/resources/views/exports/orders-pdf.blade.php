<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo Cáo Đơn Hàng</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #001c9a; color: white; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #001c9a; margin: 0; }
        .summary { margin: 20px 0; padding: 15px; background: #f5f5f5; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Vinamilk - Báo Cáo Đơn Hàng</h1>
        <p>Ngày xuất: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        <strong>Tổng số đơn hàng:</strong> {{ $orders->count() }}<br>
        <strong>Tổng doanh thu:</strong> {{ number_format($orders->sum('total_amount'), 0, ',', '.') }} đ
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Mã đơn hàng</th>
                <th>Khách hàng</th>
                <th>Email</th>
                <th>Số điện thoại</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{ $order->order_number }}</td>
                <td>{{ $order->user->name ?? 'N/A' }}</td>
                <td>{{ $order->user->email ?? 'N/A' }}</td>
                <td>{{ $order->user->phone ?? 'N/A' }}</td>
                <td>{{ number_format($order->total_amount, 0, ',', '.') }} đ</td>
                <td>{{ $order->status }}</td>
                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
