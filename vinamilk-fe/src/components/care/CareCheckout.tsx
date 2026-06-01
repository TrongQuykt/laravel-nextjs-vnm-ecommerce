"use client";

import React, { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { careApi, checkoutApi } from "@/lib/api";
import { useCareCart, formatVnd } from "@/context/CareCartContext";
import AddressSidebar from "@/components/checkout/AddressSidebar";
import InvoiceSidebar from "@/components/checkout/InvoiceSidebar";
import { ArrowLeft } from "lucide-react";

interface Props {
  onBack: () => void;
}

export function CareCheckout({ onBack }: Props) {
  const router = useRouter();
  const {
    cartLine,
    deliveryCount,
    includeGreetingCard,
    greetingCardId,
    firstDeliveryDate,
    pricing,
    updateDraft,
    reset,
    setView,
  } = useCareCart();

  const [selectedAddress, setSelectedAddress] = useState<any>(null);
  const [isAddressOpen, setIsAddressOpen] = useState(false);
  const [isInvoiceOpen, setIsInvoiceOpen] = useState(false);
  const [isInvoiceEnabled, setIsInvoiceEnabled] = useState(false);
  const [invoiceData, setInvoiceData] = useState<any>(null);
  const [paymentMethod, setPaymentMethod] = useState("vnpay");
  const [firstDate, setFirstDate] = useState(firstDeliveryDate);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState("");

  const product = cartLine?.product;
  const variantId = cartLine?.variant?.id;
  const quantity = cartLine?.quantity ?? 1;

  useEffect(() => {
    const token = typeof window !== "undefined" ? localStorage.getItem("auth_token") : null;
    if (!token) {
      router.push("/login?redirect=/care");
      return;
    }
    if (!product || !pricing) {
      setView("main");
      return;
    }
    checkoutApi.getAddresses().then((res) => {
      const list = res.data || res || [];
      if (list.length) {
        setSelectedAddress(list.find((a: any) => a.is_default) || list[0]);
      }
    });
  }, []);

  useEffect(() => {
    if (!product || !deliveryCount || !variantId || !firstDeliveryDate) return;
    careApi
      .calculate({
        care_product_id: product.id,
        variant_id: variantId,
        quantity,
        delivery_count: deliveryCount,
        first_delivery_date: firstDate,
      })
      .then((p) => {
        updateDraft({ pricing: p });
        if (firstDate !== firstDeliveryDate) {
          updateDraft({ firstDeliveryDate: firstDate });
        }
      });
  }, [firstDate]);

  const schedulePreview = pricing?.delivery_schedule || [];
  const nextDates = schedulePreview.slice(1, 3);

  const handlePay = async () => {
    if (!selectedAddress) {
      setError("Vui lòng chọn địa chỉ nhận hàng.");
      return;
    }
    setSubmitting(true);
    setError("");
    try {
      if (!variantId) {
        setError("Vui lòng chọn biến thể sản phẩm.");
        setSubmitting(false);
        return;
      }
      const res = await careApi.checkout({
        care_product_id: product!.id,
        variant_id: variantId,
        quantity,
        delivery_count: deliveryCount,
        include_greeting_card: includeGreetingCard,
        greeting_card_id: includeGreetingCard ? greetingCardId : null,
        first_delivery_date: firstDate,
        shipping_address: selectedAddress,
        payment_method: paymentMethod,
        invoice_info: isInvoiceEnabled ? invoiceData : null,
      });
      reset();
      if (res.payment_url) {
        window.location.href = res.payment_url;
      } else {
        router.push(`/payment-result?order=${res.order_number}`);
      }
    } catch (e: any) {
      setError(e.message || "Thanh toán thất bại");
    } finally {
      setSubmitting(false);
    }
  };

  if (!product || !pricing) return null;

  return (
    <div className="pb-20">
      <button
        type="button"
        onClick={onBack}
        className="inline-flex items-center gap-2 text-[#001c9a] mb-6 text-sm font-semibold"
      >
        <ArrowLeft size={16} /> Quay lại điều chỉnh gói
      </button>

      <div className="grid grid-cols-1 lg:grid-cols-5 gap-10">
        <div className="lg:col-span-3 space-y-6">
          <section className="bg-white rounded-2xl border border-[#001c9a]/10 p-6">
            <h2 className="font-bold text-[#001c9a] mb-4">Địa chỉ nhận hàng</h2>
            {selectedAddress ? (
              <div className="flex justify-between items-start">
                <div className="text-sm text-[#001c9a]">
                  <p className="font-bold">
                    {selectedAddress.last_name} {selectedAddress.first_name} · {selectedAddress.phone}
                  </p>
                  <p className="opacity-70 mt-1">
                    {selectedAddress.detail}, {selectedAddress.ward}, {selectedAddress.district},{" "}
                    {selectedAddress.city}
                  </p>
                </div>
                <button
                  type="button"
                  onClick={() => setIsAddressOpen(true)}
                  className="text-sm font-bold text-[#001c9a] underline"
                >
                  Đổi
                </button>
              </div>
            ) : (
              <button
                type="button"
                onClick={() => setIsAddressOpen(true)}
                className="text-[#001c9a] font-bold underline"
              >
                Thêm địa chỉ
              </button>
            )}
          </section>

          <section className="bg-white rounded-2xl border border-[#001c9a]/10 p-6">
            <h2 className="font-bold text-[#001c9a] mb-4">Phương thức giao nhận</h2>
            <p className="text-sm text-[#001c9a] font-semibold">Giao hàng tiêu chuẩn — 0đ</p>
          </section>

          <section className="bg-white rounded-2xl border border-[#001c9a]/10 p-6">
            <h2 className="font-bold text-[#001c9a] mb-4">Ngày giao hàng</h2>
            <label className="text-sm text-[#001c9a]/70 block mb-2">Ngày giao kiện hàng đầu tiên*</label>
            <input
              type="date"
              value={firstDate}
              min={new Date().toISOString().split("T")[0]}
              onChange={(e) => setFirstDate(e.target.value)}
              className="border border-[#001c9a]/20 rounded-lg px-4 py-2 w-full max-w-xs text-[#001c9a]"
            />
            {nextDates.length > 0 && (
              <p className="text-xs text-[#001c9a]/60 mt-3">
                Ngày giao hàng dự kiến các kỳ kế tiếp:{" "}
                {nextDates.map((d) => new Date(d).toLocaleDateString("vi-VN")).join(", ")}
              </p>
            )}
          </section>

          <section className="bg-white rounded-2xl border border-[#001c9a]/10 p-6">
            <div className="flex items-center justify-between">
              <span className="font-bold text-[#001c9a]">Yêu cầu xuất hóa đơn điện tử</span>
              <button
                type="button"
                onClick={() => setIsInvoiceEnabled(!isInvoiceEnabled)}
                className={`w-12 h-7 rounded-full ${isInvoiceEnabled ? "bg-[#001c9a]" : "bg-gray-300"}`}
              >
                <span
                  className={`block w-5 h-5 bg-white rounded-full shadow mx-1 transition-transform ${
                    isInvoiceEnabled ? "translate-x-5" : ""
                  }`}
                />
              </button>
            </div>
            {isInvoiceEnabled && (
              <button
                type="button"
                onClick={() => setIsInvoiceOpen(true)}
                className="mt-3 text-sm text-[#001c9a] underline"
              >
                Nhập thông tin hóa đơn
              </button>
            )}
          </section>

          <section className="bg-white rounded-2xl border border-[#001c9a]/10 p-6">
            <h2 className="font-bold text-[#001c9a] mb-4">Phương thức thanh toán</h2>
            <div className="space-y-2">
              {[
                { id: "stripe", label: "Thẻ thanh toán quốc tế" },
                { id: "vnpay", label: "Thẻ thanh toán nội địa (VNPay)" },
                { id: "momo", label: "Ví MoMo" },
                { id: "paypal", label: "PayPal" },
              ].map((m) => (
                <label
                  key={m.id}
                  className={`flex items-center gap-3 p-3 rounded-xl border cursor-pointer ${
                    paymentMethod === m.id ? "border-[#001c9a] bg-[#d3e1ff]/30" : "border-[#001c9a]/15"
                  }`}
                >
                  <input
                    type="radio"
                    name="pay"
                    checked={paymentMethod === m.id}
                    onChange={() => setPaymentMethod(m.id)}
                  />
                  <span className="text-sm font-semibold text-[#001c9a]">{m.label}</span>
                </label>
              ))}
            </div>
          </section>
        </div>

        <div className="lg:col-span-2">
          <div className="bg-[#fff9e6] rounded-2xl p-6 sticky top-28">
            <h2 className="font-black text-[#001c9a] text-xl mb-4">Gói Vinamilk Care</h2>
            {schedulePreview.map((date, i) => (
              <div key={i} className="mb-4 pb-4 border-b border-[#001c9a]/10 last:border-0">
                <p className="text-xs font-bold text-[#001c9a]/50 uppercase">Kiện hàng {i + 1}</p>
                <p className="text-sm font-bold text-[#001c9a] mt-1">{product.name}</p>
                <p className="text-xs text-[#001c9a]/60">Giao: {new Date(date).toLocaleDateString("vi-VN")}</p>
                {i === 0 && includeGreetingCard && (
                  <p className="text-xs text-[#001c9a] mt-1">+ Thiệp đính kèm</p>
                )}
              </div>
            ))}
            <div className="bg-[#001c9a] text-white rounded-xl p-4 mt-4 space-y-2 text-sm">
              <div className="flex justify-between opacity-80">
                <span>Loại gói</span>
                <span>Gói Tiêu Chuẩn</span>
              </div>
              <div className="flex justify-between opacity-80">
                <span>Số lần giao</span>
                <span>{deliveryCount} lần</span>
              </div>
              {pricing.discount_amount > 0 && (
                <div className="flex justify-between text-red-300">
                  <span>Ưu đãi gói</span>
                  <span>-{formatVnd(pricing.discount_amount)}</span>
                </div>
              )}
              <div className="flex justify-between text-lg font-black pt-2 border-t border-white/20">
                <span>Tổng thành tiền</span>
                <span>{formatVnd(pricing.total_amount)}</span>
              </div>
            </div>
            {error && <p className="text-red-600 text-sm mt-3">{error}</p>}
            <button
              type="button"
              disabled={submitting}
              onClick={handlePay}
              className="mt-4 w-full bg-white text-[#001c9a] py-3 rounded-full font-bold disabled:opacity-50"
            >
              {submitting ? "Đang xử lý..." : "Thanh toán"}
            </button>
          </div>
        </div>
      </div>

      <AddressSidebar
        isOpen={isAddressOpen}
        onClose={() => setIsAddressOpen(false)}
        selectedAddressId={selectedAddress?.id ?? null}
        onSelect={(addr) => {
          setSelectedAddress(addr);
          setIsAddressOpen(false);
        }}
      />
      <InvoiceSidebar
        isOpen={isInvoiceOpen}
        onClose={() => setIsInvoiceOpen(false)}
        initialData={invoiceData}
        onApply={(data) => {
          setInvoiceData(data);
          setIsInvoiceOpen(false);
        }}
      />
    </div>
  );
}
