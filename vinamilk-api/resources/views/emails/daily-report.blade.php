<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vinamilk Daily Report</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background: #001c9a; color: white; padding: 20px; text-align: center;">
            <h1 style="margin: 0; color: white;">Vinamilk Daily Report</h1>
            <p style="margin: 10px 0 0 0; color: white;">{{ $stats['date'] }}</p>
        </div>
        
        <div style="background: #f5f5f5; padding: 20px; margin-top: 20px;">
            <h2 style="color: #001c9a; margin-top: 0;">Thống kê hôm nay</h2>
            
            <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #ddd;"><strong>Tổng đơn hàng:</strong></td>
                    <td style="padding: 10px; border-bottom: 1px solid #ddd; text-align: right;">{{ number_format($stats['total_orders']) }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #ddd;"><strong>Tổng doanh thu:</strong></td>
                    <td style="padding: 10px; border-bottom: 1px solid #ddd; text-align: right;">{{ number_format($stats['total_revenue'], 0, ',', '.') }} đ</td>
                </tr>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #ddd;"><strong>Đơn hoàn thành:</strong></td>
                    <td style="padding: 10px; border-bottom: 1px solid #ddd; text-align: right;">{{ number_format($stats['completed_orders']) }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #ddd;"><strong>Đơn chờ xử lý:</strong></td>
                    <td style="padding: 10px; border-bottom: 1px solid #ddd; text-align: right;">{{ number_format($stats['pending_orders']) }}</td>
                </tr>
            </table>
        </div>
        
        <div style="margin-top: 20px; text-align: center;">
            <p style="color: #666; font-size: 12px;">Đây là báo cáo tự động từ hệ thống Vinamilk E-Commerce.</p>
        </div>
    </div>
</body>
</html>
