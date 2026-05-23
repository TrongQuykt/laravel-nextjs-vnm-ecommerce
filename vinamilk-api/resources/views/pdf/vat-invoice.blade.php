<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Hóa đơn VAT - {{ $order->order_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 13px; color: #333; }
        .header { border-bottom: 2px solid #002094; padding-bottom: 20px; margin-bottom: 20px; }
        .logo { font-size: 24px; font-weight: bold; color: #002094; }
        .invoice-title { text-align: center; font-size: 20px; font-weight: bold; text-transform: uppercase; color: #002094; margin: 20px 0; }
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table td { padding: 5px; vertical-align: top; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th { background-color: #f1f5f9; padding: 10px; border: 1px solid #ddd; text-align: center; font-weight: bold; }
        .items-table td { padding: 10px; border: 1px solid #ddd; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals-table { width: 40%; float: right; border-collapse: collapse; }
        .totals-table td { padding: 8px; border: 1px solid #ddd; }
        .totals-table .bold { font-weight: bold; }
        .footer { clear: both; margin-top: 50px; text-align: center; font-size: 11px; color: #777; border-top: 1px solid #ddd; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <table width="100%">
            <tr>
                <td width="50%">
                    <div class="logo">VINAMILK E-COMMERCE</div>
                    <div>Số 10, Tân Trào, Tân Phú, Quận 7, TP.HCM</div>
                    <div>MST: 0300588569</div>
                </td>
                <td width="50%" class="text-right">
                    <div><strong>Ký hiệu mẫu số:</strong> 1C23TML</div>
                    <div><strong>Ký hiệu hóa đơn:</strong> AA/23E</div>
                    <div><strong>Số:</strong> {{ str_pad(rand(1, 99999), 7, '0', STR_PAD_LEFT) }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="invoice-title">HÓA ĐƠN GIÁ TRỊ GIA TĂNG (BẢN THỂ HIỆN)</div>
    <div class="text-center" style="margin-bottom: 30px;">
        Ngày {{ date('d') }} tháng {{ date('m') }} năm {{ date('Y') }}
    </div>

    <table class="info-table">
        <tr>
            <td width="25%"><strong>Họ tên người mua hàng:</strong></td>
            <td>{{ $order->invoice_info['name'] ?? 'Khách lẻ' }}</td>
        </tr>
        <tr>
            <td><strong>Tên đơn vị:</strong></td>
            <td>{{ $order->invoice_info['company'] ?? ($order->invoice_info['name'] ?? 'Khách lẻ') }}</td>
        </tr>
        <tr>
            <td><strong>Mã số thuế:</strong></td>
            <td>{{ $order->invoice_info['tax_code'] ?? 'Không khai báo' }}</td>
        </tr>
        <tr>
            <td><strong>Địa chỉ:</strong></td>
            <td>{{ $order->invoice_info['address'] ?? 'Không khai báo' }}</td>
        </tr>
        <tr>
            <td><strong>Hình thức thanh toán:</strong></td>
            <td>{{ strtoupper($order->payment_method) }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">STT</th>
                <th width="40%">Tên hàng hóa, dịch vụ</th>
                <th width="10%">ĐVT</th>
                <th width="10%">SL</th>
                <th width="15%">Đơn giá (Net)</th>
                <th width="10%">Thuế suất</th>
                <th width="20%">Thành tiền (Gross)</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalNet = 0; 
                $totalTax = 0; 
                $totalGross = 0;
            @endphp
            @foreach($order->items as $index => $item)
                @php
                    $isDairy = preg_match('/sữa tươi|sữa đặc|sữa chua|tiệt trùng|thanh trùng/i', strtolower($item->product_name));
                    $taxRate = $isDairy ? 0.08 : 0.10;
                    $grossTotalLine = $item->total;
                    $netTotalLine = $grossTotalLine / (1 + $taxRate);
                    $taxAmountLine = $grossTotalLine - $netTotalLine;
                    $netUnitPrice = $netTotalLine / max($item->quantity, 1);
                    
                    $totalNet += $netTotalLine;
                    $totalTax += $taxAmountLine;
                    $totalGross += $grossTotalLine;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->product_name }}</td>
                    <td class="text-center">Hộp</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($netUnitPrice, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $taxRate * 100 }}%</td>
                    <td class="text-right">{{ number_format($grossTotalLine, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td>Cộng tiền hàng hóa, dịch vụ:</td>
            <td class="text-right">{{ number_format($totalNet, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Tiền thuế GTGT:</td>
            <td class="text-right">{{ number_format($totalTax, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="bold">Tổng cộng tiền thanh toán:</td>
            <td class="text-right bold">{{ number_format($totalGross, 0, ',', '.') }} VNĐ</td>
        </tr>
    </table>

    <div class="footer">
        Đơn vị cung cấp giải pháp Hóa đơn điện tử: VNPT E-Invoice<br>
        Bản thể hiện của Hóa đơn điện tử có giá trị tra cứu thông tin và đối soát. (Mã tra cứu: {{ strtoupper(uniqid()) }})
    </div>
</body>
</html>
