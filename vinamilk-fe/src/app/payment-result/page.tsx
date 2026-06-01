"use client";

import React, { useEffect, useState, Suspense } from "react";
import { useSearchParams, useRouter } from "next/navigation";
import Link from "next/link";
import { CheckCircle, XCircle, Clock, ArrowRight, Package, RotateCcw } from "lucide-react";

function PaymentResultContent() {
  const searchParams = useSearchParams();
  const router = useRouter();

  // Extract order number with bulletproof fallback for gateway-specific parameters
  let orderNumber = searchParams.get("order");
  if (!orderNumber) {
    const vnpTxnRef = searchParams.get("vnp_TxnRef");
    if (vnpTxnRef) {
      orderNumber = vnpTxnRef;
    } else {
      const momoOrderId = searchParams.get("orderId");
      if (momoOrderId) {
        orderNumber = momoOrderId.split("_")[0];
      }
    }
  }

  const status = searchParams.get("status");

  // VNPay specific params
  const vnpResponseCode = searchParams.get("vnp_ResponseCode");
  const vnpTransactionStatus = searchParams.get("vnp_TransactionStatus");

  // MoMo specific params
  const momoResultCode = searchParams.get("resultCode");

  // PayPal specific params
  const paypalToken = searchParams.get("token");
  const paypalPayerId = searchParams.get("PayerID");
  const stripeSessionId = searchParams.get("session_id");

  const [countdown, setCountdown] = useState(8);

  // Determine final status
  let finalStatus: "success" | "failed" | "pending" = "pending";

  if (status === "success") {
    finalStatus = "success";
  }
  if (status === "cancel" || status === "failed") {
    finalStatus = "failed";
  }

  // VNPay: 00 = success
  if (vnpResponseCode !== null) {
    finalStatus = vnpResponseCode === "00" && vnpTransactionStatus === "00" ? "success" : "failed";
  }

  // MoMo: 0 = success
  if (momoResultCode !== null) {
    finalStatus = momoResultCode === "0" ? "success" : "failed";
  }

  // Stripe: if session_id present, payment was approved
  if (stripeSessionId) {
    finalStatus = "success";
  }

  // PayPal: if PayerID present, payment was approved
  if (paypalPayerId) {
    finalStatus = "success";
  }

  // Some gateways redirect with only the order number on success.
  if ((status !== "cancel" && status !== "failed") && finalStatus === "pending" && orderNumber && !status && !vnpResponseCode && !momoResultCode && !paypalPayerId && !stripeSessionId) {
    finalStatus = "success";
  }

  const isSuccess = finalStatus === "success";
  const isFailed = finalStatus === "failed";

  useEffect(() => {
    if (finalStatus === "success" && orderNumber) {
      const apiBase = process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000/api/v1';
      fetch(`${apiBase}/orders/${orderNumber}/payment-success`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          gateway: vnpResponseCode !== null ? 'vnpay' : (momoResultCode !== null ? 'momo' : (stripeSessionId ? 'stripe' : (paypalPayerId ? 'paypal' : 'unknown'))),
          status: finalStatus,
          params: Object.fromEntries(searchParams.entries()),
        }),
      })
      .then(res => res.json())
      .then(data => console.log('Payment synchronized successfully with backend:', data))
      .catch(err => console.error('Error synchronizing payment status:', err))
      .finally(() => {
        router.push(`/account/orders/${orderNumber}`);
      });
    }
  }, [finalStatus, orderNumber, router]);

  useEffect(() => {
    if (!isSuccess || countdown <= 0) return;
    const timer = window.setTimeout(() => setCountdown((prev) => prev - 1), 1000);
    return () => clearTimeout(timer);
  }, [countdown, isSuccess]);

  return (
    <div className="min-h-screen bg-gradient-to-br from-[#f0f4ff] via-white to-[#e8f5e9] flex items-center justify-center px-4">
      <div className="w-full max-w-lg">
        {/* Card */}
        <div className="bg-white rounded-3xl shadow-2xl overflow-hidden">
          {/* Header Banner */}
          <div
            className={`px-8 py-10 text-center ${
              isSuccess
                ? "bg-gradient-to-br from-[#0213b0] to-[#1a56db]"
                : isFailed
                ? "bg-gradient-to-br from-red-500 to-red-700"
                : "bg-gradient-to-br from-amber-400 to-amber-600"
            }`}
          >
            <div className="flex justify-center mb-4">
              {isSuccess ? (
                <div className="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center animate-bounce">
                  <CheckCircle size={48} className="text-white" />
                </div>
              ) : isFailed ? (
                <div className="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center">
                  <XCircle size={48} className="text-white" />
                </div>
              ) : (
                <div className="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center animate-spin">
                  <Clock size={48} className="text-white" />
                </div>
              )}
            </div>

            <h1 className="text-2xl font-black text-white mb-2">
              {isSuccess ? "Thanh toán thành công!" : isFailed ? "Thanh toán thất bại" : "Đang xử lý..."}
            </h1>
            <p className="text-white/80 text-sm">
              {isSuccess
                ? "Cảm ơn bạn đã tin tưởng Vinamilk. Đơn hàng của bạn đang được xử lý."
                : isFailed
                ? "Giao dịch không thành công. Vui lòng thử lại hoặc chọn phương thức thanh toán khác."
                : "Hệ thống đang xác nhận giao dịch của bạn..."}
            </p>
          </div>

          {/* Body */}
          <div className="px-8 py-6 space-y-4">
            {/* Order number */}
            {orderNumber && (
              <div className="bg-gray-50 rounded-2xl px-6 py-4 flex items-center justify-between">
                <div>
                  <p className="text-xs text-gray-500 font-medium uppercase tracking-wider">Mã đơn hàng</p>
                  <p className="text-lg font-black text-[#0213b0] mt-0.5">{orderNumber}</p>
                </div>
                <Package size={28} className="text-[#0213b0]/30" />
              </div>
            )}

            {/* Auto redirect countdown */}
            {isSuccess && (
              <div className="text-center py-2">
                <p className="text-sm text-gray-500">
                  Tự động chuyển đến trang đơn hàng sau{" "}
                  <span className="font-black text-[#0213b0]">{countdown}</span> giây...
                </p>
              </div>
            )}

            {/* Actions */}
            <div className="space-y-3 pt-2">
              {isSuccess && orderNumber && (
                <Link
                  href={`/account/orders/${orderNumber}`}
                  className="flex items-center justify-center gap-2 w-full py-4 bg-[#0213b0] text-white font-black rounded-2xl hover:bg-[#002060] transition-colors text-[15px]"
                >
                  <Package size={18} />
                  Xem chi tiết đơn hàng
                  <ArrowRight size={18} />
                </Link>
              )}

              {isFailed && (
                <Link
                  href="/checkout"
                  className="flex items-center justify-center gap-2 w-full py-4 bg-[#0213b0] text-white font-black rounded-2xl hover:bg-[#002060] transition-colors text-[15px]"
                >
                  <RotateCcw size={18} />
                  Thử lại thanh toán
                </Link>
              )}

              <Link
                href="/"
                className="flex items-center justify-center gap-2 w-full py-4 bg-gray-100 text-gray-700 font-bold rounded-2xl hover:bg-gray-200 transition-colors text-[15px]"
              >
                Về trang chủ
              </Link>
            </div>
          </div>

          {/* Footer note */}
          <div className="px-8 pb-6">
            <p className="text-center text-xs text-gray-400">
              Mọi thắc mắc về đơn hàng vui lòng liên hệ{" "}
              <span className="text-[#0213b0] font-bold">1800 9033</span>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}

export default function PaymentResultPage() {
  return (
    <Suspense fallback={
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin w-10 h-10 border-4 border-[#0213b0] border-t-transparent rounded-full" />
      </div>
    }>
      <PaymentResultContent />
    </Suspense>
  );
}
