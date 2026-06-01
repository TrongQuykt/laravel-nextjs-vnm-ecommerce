<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class PaymentService
{
    /**
     * Frontend URL cho redirect sau thanh toán (khác với APP_URL của Laravel)
     */
    private function frontendUrl(): string
    {
        return rtrim(env('FRONTEND_URL', 'http://localhost:3000'), '/');
    }

    public function createPaymentUrl(Order $order)
    {
        try {
            switch ($order->payment_method) {
                case 'momo':
                    return $this->createMomoUrl($order);
                case 'vnpay':
                    return $this->createVnpayUrl($order);
                case 'stripe':
                    return $this->createStripeUrl($order);
                case 'paypal':
                    return $this->createPaypalUrl($order);
                default:
                    Log::warning("Unknown payment method: " . $order->payment_method);
                    return null;
            }
        } catch (\Exception $e) {
            Log::error("Payment Error for Order {$order->order_number}: " . $e->getMessage());
            return null;
        }
    }

    protected function createVnpayUrl($order)
    {
        $vnp_Url = env('VNP_URL');
        $vnp_HashSecret = trim(env('VNP_HASH_SECRET'));
        $vnp_TmnCode = trim(env('VNP_TMN_CODE'));

        if (!$vnp_Url || !$vnp_HashSecret || !$vnp_TmnCode) {
            Log::error("VNPAY Config Missing");
            return null;
        }

        // VNPAY yêu cầu amount là số nguyên và nhân 100
        $amount = (int)(round($order->total_amount) * 100);

        // Giải quyết vấn đề địa chỉ IP localhost ::1 hoặc ::ffff:127.0.0.1 (chuẩn hóa về IPv4 127.0.0.1)
        $ipAddr = request()->ip() ?? '127.0.0.1';
        if ($ipAddr === '::1' || $ipAddr === '::ffff:127.0.0.1') {
            $ipAddr = '127.0.0.1';
        }

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $ipAddr,
            "vnp_Locale" => 'vn',
            "vnp_OrderInfo" => "Thanh toan don hang " . $order->order_number,
            "vnp_OrderType" => 'billpayment',
                // After payment, redirect user to payment-result so frontend can synchronize status before showing the order
                "vnp_ReturnUrl" => $this->frontendUrl() . "/payment-result?order=" . $order->order_number,
            "vnp_TxnRef" => $order->order_number,
        ];

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode((string)$key) . "=" . urlencode((string)$value);
            } else {
                $hashdata .= urlencode((string)$key) . "=" . urlencode((string)$value);
                $i = 1;
            }
            $query .= urlencode((string)$key) . "=" . urlencode((string)$value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;

        Log::info("VNPAY URL generated for {$order->order_number}: amount={$amount}");
        return $vnp_Url;
    }

    protected function createMomoUrl($order)
    {
        $endpoint = env('MOMO_ENDPOINT');
        $partnerCode = env('MOMO_PARTNER_CODE');
        $accessKey = env('MOMO_ACCESS_KEY');
        $secretKey = env('MOMO_SECRET_KEY');

        if (!$endpoint || !$partnerCode) {
            Log::error("Momo Config Missing");
            return null;
        }

        // MoMo yêu cầu amount là chuỗi số nguyên VND, không có dấu thập phân
        $amount = (string)round($order->total_amount);
        $orderId = $order->order_number . "_" . time();
        $requestId = time() . "_" . $order->order_number;
        // After MoMo payment, redirect to the payment result page so frontend can synchronize status
        $redirectUrl = $this->frontendUrl() . "/payment-result?order=" . $order->order_number;
        $ipnUrl = env('APP_URL') . "/api/v1/payment/momo-callback"; // IPN vẫn gửi về backend
        $orderInfo = "Thanh toan don hang " . $order->order_number; // Không dùng ký tự đặc biệt

        // payWithMethod: Hiển thị màn hình chọn QR / ATM / Visa / MoMo Wallet
        $requestType = 'payWithMethod';
        $rawHash = "accessKey=$accessKey&amount=$amount&extraData=&ipnUrl=$ipnUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$redirectUrl&requestId=$requestId&requestType=$requestType";
        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        $data = [
            'partnerCode' => $partnerCode,
            'partnerName' => "Vinamilk Store",
            'storeId' => "MomoTestStore",
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => '',
            'requestType' => $requestType,
            'signature' => $signature
        ];

        Log::info("MoMo Request for {$order->order_number}", [
            'amount' => $amount,
            'orderId' => $orderId,
            'rawHash' => $rawHash,
        ]);

        $response = Http::timeout(30)->post($endpoint, $data);

        if ($response->successful()) {
            $json = $response->json();
            Log::info("MoMo Response for {$order->order_number}: " . json_encode($json));
            return $json['payUrl'] ?? null;
        }

        Log::error("MoMo API Error for Order {$order->order_number}: " . $response->body());
        return null;
    }

    protected function createStripeUrl($order)
    {
        $secret = env('STRIPE_SECRET_KEY');
        if (!$secret) {
            Log::error("Stripe Secret Missing");
            return null;
        }

        try {
            Stripe::setApiKey($secret);

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'vnd',
                        'product_data' => ['name' => "Don hang " . $order->order_number],
                        'unit_amount' => (int)round($order->total_amount),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                // On success go to payment-result to synchronize before redirecting to order details
                'success_url' => $this->frontendUrl() . "/payment-result?order=" . $order->order_number,
                'cancel_url' => $this->frontendUrl() . "/payment-result?order=" . $order->order_number . "&status=cancel",
            ]);

            Log::info("Stripe OK for {$order->order_number}: session={$session->id}");
            return $session->url;
        } catch (\Stripe\Exception\AuthenticationException $e) {
            Log::error("Stripe Auth Error: " . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            Log::error("Stripe Error for {$order->order_number}: " . $e->getMessage());
            return null;
        }
    }

    protected function createPaypalUrl($order)
    {
        $clientId = env('PAYPAL_CLIENT_ID');
        $secret = env('PAYPAL_SECRET');
        $baseUrl = "https://api-m.sandbox.paypal.com";

        if (!$clientId || !$secret) {
            Log::error("PayPal Config Missing");
            return null;
        }

        try {
            // 1. Lấy Access Token
            $authResponse = Http::timeout(15)
                ->asForm()
                ->withBasicAuth($clientId, $secret)
                ->post("$baseUrl/v1/oauth2/token", ['grant_type' => 'client_credentials']);

            if (!$authResponse->successful()) {
                Log::error("PayPal Auth Error: " . $authResponse->body());
                return null;
            }

            $accessToken = $authResponse->json()['access_token'];
            Log::info("PayPal Auth OK for {$order->order_number}");

            // 2. Tạo Order - Đổi VND → USD (1 USD ≈ 25,000 VND)
            $amountVnd = round($order->total_amount);
            $amountUsd = max(1.00, round($amountVnd / 25000, 2));

            $orderResponse = Http::timeout(15)
                ->withToken($accessToken)
                ->post("$baseUrl/v2/checkout/orders", [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [[
                        'reference_id' => $order->order_number,
                        'description' => 'Don hang Vinamilk #' . $order->order_number,
                        'amount' => [
                            'currency_code' => 'USD',
                            'value' => number_format($amountUsd, 2, '.', ''),
                        ]
                    ]],
                    'application_context' => [
                        'brand_name' => 'Vinamilk Store',
                        'landing_page' => 'BILLING',
                        'user_action' => 'PAY_NOW',
                        // On PayPal success go directly to order detail page
                        'return_url' => $this->frontendUrl() . "/payment-result?order=" . $order->order_number,
                        'cancel_url' => $this->frontendUrl() . "/payment-result?order=" . $order->order_number . "&status=cancel",
                    ]
                ]);

            if ($orderResponse->successful()) {
                $links = $orderResponse->json()['links'] ?? [];
                foreach ($links as $link) {
                    if ($link['rel'] === 'approve') {
                        Log::info("PayPal Order created for {$order->order_number}: USD {$amountUsd}");
                        return $link['href'];
                    }
                }
            }

            Log::error("PayPal Order Error for {$order->order_number}: " . $orderResponse->body());
            return null;
        } catch (\Exception $e) {
            Log::error("PayPal Exception for {$order->order_number}: " . $e->getMessage());
            return null;
        }
    }
}