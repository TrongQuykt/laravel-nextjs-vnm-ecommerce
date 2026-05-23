<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class PaymentController extends Controller
{
    public function createPaymentUrl(Order $order)
    {
        switch ($order->payment_method) {
            case 'momo':
                return $this->createMomoUrl($order);
            case 'vnpay':
                return $this->createVnpayUrl($order);
            case 'intl':
                // Default to Stripe for international
                return $this->createStripeUrl($order);
            default:
                return null;
        }
    }

    protected function createVnpayUrl($order)
    {
        $vnp_Url = env('VNP_URL');
        $vnp_HashSecret = trim(env('VNP_HASH_SECRET'));
        $vnp_TmnCode = trim(env('VNP_TMN_CODE'));

        $vnp_TxnRef = $order->order_number;
        $vnp_OrderInfo = "Thanh toan don hang " . $order->order_number;
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = (int)($order->total_amount * 100);
        $vnp_Locale = 'vn';
        
        $vnp_IpAddr = request()->ip() ?? '127.0.0.1';
        if ($vnp_IpAddr === '::1' || $vnp_IpAddr === '::ffff:127.0.0.1') {
            $vnp_IpAddr = '127.0.0.1';
        }

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => env('APP_URL') . "/payment-result",
            "vnp_TxnRef" => $vnp_TxnRef,
        );

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
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return $vnp_Url;
    }

    protected function createMomoUrl($order)
    {
        $endpoint = env('MOMO_ENDPOINT');
        $partnerCode = env('MOMO_PARTNER_CODE');
        $accessKey = env('MOMO_ACCESS_KEY');
        $secretKey = env('MOMO_SECRET_KEY');

        $orderInfo = "Thanh toán đơn hàng " . $order->order_number;
        $amount = (string)$order->total_amount;
        $orderId = $order->order_number . "_" . time();
        $redirectUrl = env('APP_URL') . "/payment-result";
        $ipnUrl = env('APP_URL') . "/api/v1/payment/momo-callback";
        $requestId = (string)time();
        $requestType = "captureWallet";
        $extraData = "";

        $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;
        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        $data = array(
            'partnerCode' => $partnerCode,
            'partnerName' => "Vinamilk Test",
            'storeId' => "MomoStore",
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature
        );

        try {
            $response = Http::post($endpoint, $data);
            if ($response->successful()) {
                return $response->json()['payUrl'] ?? null;
            }
        } catch (\Exception $e) {
            \Log::error("Momo Payment Error: " . $e->getMessage());
        }

        return null;
    }

    protected function createStripeUrl($order)
    {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'vnd',
                        'product_data' => [
                            'name' => 'Đơn hàng ' . $order->order_number,
                        ],
                        'unit_amount' => (int)$order->total_amount,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => env('APP_URL') . '/payment-result?order=' . $order->order_number,
                'cancel_url' => env('APP_URL') . '/payment-result?status=cancel',
            ]);

            return $session->url;
        } catch (\Exception $e) {
            \Log::error("Stripe Error: " . $e->getMessage());
            return null;
        }
    }
}
