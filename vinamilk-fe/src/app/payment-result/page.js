"use client";
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.default = PaymentResultPage;
var react_1 = require("react");
var navigation_1 = require("next/navigation");
var link_1 = require("next/link");
var lucide_react_1 = require("lucide-react");
function PaymentResultContent() {
    var searchParams = (0, navigation_1.useSearchParams)();
    var router = (0, navigation_1.useRouter)();
    // Extract order number with bulletproof fallback for gateway-specific parameters
    var orderNumber = searchParams.get("order");
    if (!orderNumber) {
        var vnpTxnRef = searchParams.get("vnp_TxnRef");
        if (vnpTxnRef) {
            orderNumber = vnpTxnRef;
        }
        else {
            var momoOrderId = searchParams.get("orderId");
            if (momoOrderId) {
                orderNumber = momoOrderId.split("_")[0];
            }
        }
    }
    var status = searchParams.get("status");
    // VNPay specific params
    var vnpResponseCode = searchParams.get("vnp_ResponseCode");
    var vnpTransactionStatus = searchParams.get("vnp_TransactionStatus");
    // MoMo specific params
    var momoResultCode = searchParams.get("resultCode");
    // PayPal specific params
    var paypalToken = searchParams.get("token");
    var paypalPayerId = searchParams.get("PayerID");
    var stripeSessionId = searchParams.get("session_id");
    var _a = (0, react_1.useState)(8), countdown = _a[0], setCountdown = _a[1];
    // Determine final status
    var finalStatus = "pending";
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
    (0, react_1.useEffect)(function () {
        if (finalStatus === "success" && orderNumber) {
            var apiBase = process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000/api/v1';
            fetch("".concat(apiBase, "/orders/").concat(orderNumber, "/payment-success"), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    gateway: vnpResponseCode !== null ? 'vnpay' : (momoResultCode !== null ? 'momo' : (paypalPayerId ? 'paypal' : 'other')),
                    status: finalStatus,
                    params: Object.fromEntries(searchParams.entries()),
                }),
            })
                .then(function (res) { return res.json(); })
                .then(function (data) { return console.log('Payment synchronized successfully with backend:', data); })
                .catch(function (err) { return console.error('Error synchronizing payment status:', err); })
                .finally(function () {
                router.push("/account/orders/".concat(orderNumber));
            });
        }
    }, [finalStatus, orderNumber, router]);
    var isSuccess = finalStatus === "success";
    var isFailed = finalStatus === "failed";
    return (<div className="min-h-screen bg-gradient-to-br from-[#f0f4ff] via-white to-[#e8f5e9] flex items-center justify-center px-4">
      <div className="w-full max-w-lg">
        {/* Card */}
        <div className="bg-white rounded-3xl shadow-2xl overflow-hidden">
          {/* Header Banner */}
          <div className={"px-8 py-10 text-center ".concat(isSuccess
            ? "bg-gradient-to-br from-[#0213b0] to-[#1a56db]"
            : isFailed
                ? "bg-gradient-to-br from-red-500 to-red-700"
                : "bg-gradient-to-br from-amber-400 to-amber-600")}>
            <div className="flex justify-center mb-4">
              {isSuccess ? (<div className="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center animate-bounce">
                  <lucide_react_1.CheckCircle size={48} className="text-white"/>
                </div>) : isFailed ? (<div className="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center">
                  <lucide_react_1.XCircle size={48} className="text-white"/>
                </div>) : (<div className="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center animate-spin">
                  <lucide_react_1.Clock size={48} className="text-white"/>
                </div>)}
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
            {orderNumber && (<div className="bg-gray-50 rounded-2xl px-6 py-4 flex items-center justify-between">
                <div>
                  <p className="text-xs text-gray-500 font-medium uppercase tracking-wider">Mã đơn hàng</p>
                  <p className="text-lg font-black text-[#0213b0] mt-0.5">{orderNumber}</p>
                </div>
                <lucide_react_1.Package size={28} className="text-[#0213b0]/30"/>
              </div>)}

            {/* Auto redirect countdown */}
            {isSuccess && (<div className="text-center py-2">
                <p className="text-sm text-gray-500">
                  Tự động chuyển đến trang đơn hàng sau{" "}
                  <span className="font-black text-[#0213b0]">{countdown}</span> giây...
                </p>
              </div>)}

            {/* Actions */}
            <div className="space-y-3 pt-2">
              {isSuccess && orderNumber && (<link_1.default href={"/account/orders/".concat(orderNumber)} className="flex items-center justify-center gap-2 w-full py-4 bg-[#0213b0] text-white font-black rounded-2xl hover:bg-[#002060] transition-colors text-[15px]">
                  <lucide_react_1.Package size={18}/>
                  Xem chi tiết đơn hàng
                  <lucide_react_1.ArrowRight size={18}/>
                </link_1.default>)}

              {isFailed && (<link_1.default href="/checkout" className="flex items-center justify-center gap-2 w-full py-4 bg-[#0213b0] text-white font-black rounded-2xl hover:bg-[#002060] transition-colors text-[15px]">
                  <lucide_react_1.RotateCcw size={18}/>
                  Thử lại thanh toán
                </link_1.default>)}

              <link_1.default href="/" className="flex items-center justify-center gap-2 w-full py-4 bg-gray-100 text-gray-700 font-bold rounded-2xl hover:bg-gray-200 transition-colors text-[15px]">
                Về trang chủ
              </link_1.default>
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
    </div>);
}
function PaymentResultPage() {
    return (<react_1.Suspense fallback={<div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin w-10 h-10 border-4 border-[#0213b0] border-t-transparent rounded-full"/>
      </div>}>
      <PaymentResultContent />
    </react_1.Suspense>);
}
