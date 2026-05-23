<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Hóa đơn điện tử VAT Vinamilk</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <div style="background-color: #002094; padding: 20px; text-align: center;">
            <h2 style="color: #ffffff; margin: 0;">Thông báo Phát hành Hóa đơn VAT</h2>
        </div>
        <div style="padding: 30px;">
            <p>Kính gửi <strong>{{ $order->invoice_info['name'] ?? 'Quý khách' }}</strong>,</p>
            <p>Vinamilk xin trân trọng thông báo Hóa đơn giá trị gia tăng (GTGT) điện tử cho Đơn hàng <strong>#{{ $order->order_number }}</strong> của Quý khách đã được phát hành thành công.</p>
            
            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; margin: 20px 0;">
                <h4 style="margin-top: 0; color: #002094;">Thông tin hóa đơn:</h4>
                <ul style="list-style: none; padding: 0; margin: 0; line-height: 1.6;">
                    <li><strong>Mã số thuế:</strong> {{ $order->invoice_info['tax_code'] ?? 'Không có' }}</li>
                    <li><strong>Tên đơn vị/Cá nhân:</strong> {{ $order->invoice_info['name'] ?? 'Không có' }}</li>
                    <li><strong>Địa chỉ:</strong> {{ $order->invoice_info['address'] ?? 'Không có' }}</li>
                    <li><strong>Tổng giá trị:</strong> {{ number_format($order->total_amount, 0, ',', '.') }} VNĐ</li>
                </ul>
            </div>
            
            <p>Vui lòng đăng nhập vào cổng thông tin tra cứu hóa đơn điện tử hoặc xem trực tiếp trên ứng dụng của chúng tôi để tải file XML và PDF gốc có chữ ký số hợp lệ của Tổng Cục Thuế.</p>
            
            <p style="margin-top: 30px;">Trân trọng,<br><strong>Đội ngũ Vinamilk E-commerce</strong></p>
        </div>
        <div style="background-color: #f1f5f9; padding: 15px; text-align: center; font-size: 12px; color: #64748b;">
            Đây là email tự động từ hệ thống, vui lòng không trả lời email này.
        </div>
    </div>
</body>
</html>
