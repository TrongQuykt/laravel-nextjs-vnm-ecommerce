"use client";

import React, { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { careApi, checkoutApi, catalogApi, getImageUrl } from "@/lib/api";
import { useCareCart, variantUnitPrice, variantBasePrice } from "@/context/CareCartContext";
import AddressSidebar from "@/components/checkout/AddressSidebar";
import InvoiceSidebar from "@/components/checkout/InvoiceSidebar";
import GiftSelectionPanel from "@/components/catalog/GiftSelectionPanel";
import { GreetingCardModal } from "@/components/care/GreetingCardModal";
import {
  Receipt,
  CheckCircle,
  CreditCard,
  QrCode,
  ArrowLeft,
} from "lucide-react";
import Link from "next/link";
import { AnimatePresence, motion } from "framer-motion";

// ─── Format VND ─────────────────────────────────────────
function formatVnd(n: number) {
  // Format with vi-VN locale (Vietnamese currency format)
  return n.toLocaleString("vi-VN") + "đ";
}

// ─── Skeleton helpers ────────────────────────────────────
const skBg = "bg-[#001c9a]/10 animate-pulse";

function SkeletonLine({ w = "w-full", h = "h-4" }: { w?: string; h?: string }) {
  return <div className={`${h} ${w} ${skBg} rounded`} />;
}

function SkeletonBlock({ h = "h-24" }: { h?: string }) {
  return <div className={`${h} w-full ${skBg} rounded-lg`} />;
}

function LeftColumnSkeleton() {
  return (
    <div className="space-y-6">
      <SkeletonLine w="w-48" h="h-10" />
      {[1, 2, 3, 4, 5].map(i => (
        <div key={i} className="p-6 bg-[#fffff1] border border-[#001c9a]/5 space-y-3">
          <SkeletonLine w="w-32" h="h-3" />
          <SkeletonLine w="w-64" />
          <SkeletonLine w="w-48" />
        </div>
      ))}
    </div>
  );
}

function RightColumnSkeleton() {
  return (
    <div className="bg-[#fffff1] border border-[#001c9a]/10 p-8 min-h-[600px] space-y-6">
      <SkeletonLine w="w-56" h="h-7" />
      {[1, 2, 3].map(i => (
        <div key={i} className="pb-6 border-b border-[#001c9a]/10 space-y-3">
          <div className="flex justify-between items-center">
            <SkeletonLine w="w-28" h="h-5" />
            <SkeletonLine w="w-24" h="h-5" />
          </div>
          <div className="flex gap-4">
            <div className="w-16 h-16 bg-[#001c9a]/10 rounded animate-pulse flex-shrink-0" />
            <div className="flex-1 space-y-2">
              <SkeletonLine />
              <SkeletonLine w="w-3/4" />
            </div>
            <div className="space-y-1 text-right">
              <SkeletonLine w="w-20" />
              <SkeletonLine w="w-20" />
            </div>
          </div>
        </div>
      ))}
    </div>
  );
}

// ─── Payment methods ─────────────────────────────────────
const PAYMENT_METHODS = [
  { id: "vnpay", label: "Thẻ thanh toán nội địa (VNPay)", icon: CreditCard },
  { id: "momo",  label: "Ví điện tử MoMo",                icon: QrCode },
  { id: "paypal",label: "Cổng thanh toán PayPal",          icon: CreditCard },
  { id: "stripe",label: "Thẻ thanh toán quốc tế (Stripe)", icon: CreditCard },
];

// ─── Main Component ───────────────────────────────────────
export default function CareCheckoutClient() {
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
    selectedGifts,
  } = useCareCart();

  const [loading, setLoading]           = useState(true);
  const [selectedAddress, setSelectedAddress] = useState<any>(null);
  const [isAddressOpen, setIsAddressOpen]     = useState(false);
  const [isInvoiceOpen, setIsInvoiceOpen]     = useState(false);
  const [isInvoiceEnabled, setIsInvoiceEnabled] = useState(false);
  const [invoiceData, setInvoiceData]         = useState<any>(null);
  const [paymentMethod, setPaymentMethod]     = useState("vnpay");
  const [firstDate, setFirstDate]             = useState(firstDeliveryDate || "");
  const [submitting, setSubmitting]           = useState(false);
  const [error, setError]                     = useState("");
  const [toast, setToast]                     = useState<{ message: string; visible: boolean }>({ message: "", visible: false });

  // Gift / Card data
  const [gifts, setGifts]               = useState<any[]>([]);
  const [loadingGifts, setLoadingGifts] = useState(true);
  const [cards, setCards]               = useState<any[]>([]);

  // Modals
  const [isGiftModalOpen, setIsGiftModalOpen] = useState(false);
  const [isCardModalOpen, setIsCardModalOpen] = useState(false);
  const [activeGiftReward, setActiveGiftReward] = useState<any>(null);

  const product   = cartLine?.product;
  const variantId = cartLine?.variant?.id;
  const quantity  = cartLine?.quantity ?? 1;

  const showNotification = (msg: string) => {
    setToast({ message: msg, visible: true });
    setTimeout(() => setToast(prev => ({ ...prev, visible: false })), 2500);
  };

  // ── Initial load ─────────────────────────────────────
  useEffect(() => {
    const token = typeof window !== "undefined" ? localStorage.getItem("auth_token") : null;
    if (!token) {
      router.push("/login?redirect=/care");
      return;
    }
    if (!product || !pricing) {
      router.push("/care");
      return;
    }
    Promise.all([
      checkoutApi.getAddresses(),
      careApi.getGreetingCards(),
    ]).then(([addrRes, cardRes]) => {
      const list = addrRes.data || addrRes || [];
      if (list.length) setSelectedAddress(list.find((a: any) => a.is_default) || list[0]);
      setCards(cardRes.cards || []);
    }).finally(() => setLoading(false));
  }, []);

  // ── Re-calculate pricing when date changes ────────────
  useEffect(() => {
    if (!product || !deliveryCount || !variantId || !firstDate) return;
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
        if (firstDate !== firstDeliveryDate) updateDraft({ firstDeliveryDate: firstDate });
      });
  }, [firstDate]);

  // ── Evaluate cart for gifts ───────────────────────────
  useEffect(() => {
    if (!product || !variantId || !quantity || !cartLine?.variant) return;
    setLoadingGifts(true);
    catalogApi
      .evaluateCart({
        items: [{
          product_id: product.product_id,
          variant_id: variantId,
          quantity,
          price: variantUnitPrice(cartLine.variant),
          category_id: 1,
        }],
      })
      .then((response) => {
        if (response.success && response.data.gifts?.length > 0) {
          const rewardList = response.data.gifts;
          setGifts(rewardList);
          const currentGifts = { ...selectedGifts };
          let changed = false;
          rewardList.forEach((r: any) => {
             const maybeId = r.id ?? r.reward_id ?? r.item_id ?? (r.options && r.options[0] ? (r.options[0].id ?? r.options[0].item_id) : undefined);
             const key = (typeof maybeId !== 'undefined' && maybeId !== null) ? String(maybeId) : `${product.product_id}:${variantId}`;
             if (!currentGifts[key] && r.options?.[0]) {
                 currentGifts[key] = r.options[0];
                 changed = true;
             }
          });
          if (changed) updateDraft({ selectedGifts: currentGifts });
        } else {
          setGifts([]);
        }
      })
      .catch(() => {
        setGifts([]);
      })
      .finally(() => setLoadingGifts(false));
  }, [product, variantId, quantity, selectedGifts, updateDraft, cartLine]);

  // ─────────────────────────────────────────────────────
  const schedulePreview = pricing?.delivery_schedule || [];
  const nextDates       = schedulePreview.slice(1, 3);

  const itemValue       = product && cartLine ? variantUnitPrice(cartLine.variant) * quantity : 0;
  const packageSubtotal = itemValue * deliveryCount;
  const discountAmount  = pricing?.discount_amount || 0;
  const calcDiscount    = discountAmount > 0 ? discountAmount : packageSubtotal * 0.1;
  const totalAmount     = packageSubtotal - calcDiscount;

  const selectedCard = cards.find(c => c.id === greetingCardId) || cards[0];

  // ─────────────────────────────────────────────────────
  const handlePay = async () => {
    if (!selectedAddress) { setError("Vui lòng chọn địa chỉ nhận hàng."); return; }
    if (!variantId)       { setError("Vui lòng chọn biến thể sản phẩm."); return; }
    setSubmitting(true);
    setError("");
    try {
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
        // Include frontend-calculated pricing to ensure backend uses the same totals
        pricing: pricing || null,
        package_subtotal: packageSubtotal,
        discount_amount: calcDiscount,
        total_amount: totalAmount,
        selected_gifts: selectedGifts || null,
      });
      reset();
      if (res.payment_url) window.location.href = res.payment_url;
      else router.push(`/account/orders/${res.order_number}`);
    } catch (e: any) {
      setError(e.message || "Thanh toán thất bại");
    } finally {
      setSubmitting(false);
    }
  };

  const horizontalLineStyle = {
    backgroundImage: `
      linear-gradient(to bottom, rgba(0, 28, 154, 0.05) 1px, transparent 1px)
    `,
    backgroundSize: "100% 40px",
  };

  // ─────────────────────────────────────────────────────
  if (loading) {
    return (
      <div className="min-h-screen bg-[#f7f9fc] font-sans">
        <header className="h-20 flex items-center px-10 border-b border-[#001c9a]/5 bg-[#f7f9fc]">
          <div className="flex-1">
            <div className={`h-4 w-16 ${skBg} rounded`} />
          </div>
          <div className="flex-shrink-0">
            <img src="/logo_vina.svg" alt="Vinamilk" className="h-10 w-auto opacity-50" />
          </div>
          <div className="flex-1" />
        </header>
        <main className="max-w-[1300px] mx-auto px-10 py-12">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <LeftColumnSkeleton />
            <RightColumnSkeleton />
          </div>
        </main>
      </div>
    );
  }

  if (!product || !pricing) return null;

  return (
    <div className="min-h-screen relative font-sans bg-[#f7f9fc]">
      <div className="absolute inset-0 pointer-events-none" style={horizontalLineStyle} />

      {/* Backdrop */}
      <AnimatePresence>
        {(isAddressOpen || isInvoiceOpen || isGiftModalOpen || isCardModalOpen) && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 z-[1000] bg-black/40 backdrop-blur-sm"
            onClick={() => {
              setIsAddressOpen(false);
              setIsInvoiceOpen(false);
              setIsGiftModalOpen(false);
              setIsCardModalOpen(false);
            }}
          />
        )}
      </AnimatePresence>

      {/* Header — no Navbar, no Footer */}
      <header className="relative z-10 h-20 flex items-center px-10 border-b border-[#001c9a]/5 bg-[#f7f9fc]/90 backdrop-blur-sm">
        <div className="flex-1">
          <button
            onClick={() => { setView("package"); router.back(); }}
            className="flex items-center gap-2 text-[#001c9a] font-bold text-[14px] hover:translate-x-[-4px] transition-transform"
          >
            <ArrowLeft size={18} />
            <span>Quay lại</span>
          </button>
        </div>
        <div className="flex-shrink-0">
          <Link href="/"><img src="/logo_vina.svg" alt="Vinamilk" className="h-10 w-auto" /></Link>
        </div>
        <div className="flex-1" />
      </header>

      <main className="relative z-10 max-w-[1300px] mx-auto px-6 md:px-10 py-12">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-10 items-start">

          {/* ═══════════════════════════════ LEFT COLUMN ═══════════════════════════════ */}
          <div className="space-y-5">
            <h1 className="text-[40px] font-black text-[#001c9a] leading-none tracking-tight">Thanh toán</h1>

            {/* Account */}
            <div className="space-y-1.5">
              <p className="text-[10px] font-bold text-[#001c9a]/40 uppercase tracking-widest italic">Tài khoản tích điểm</p>
              <div className="bg-[#fffff1] p-5 flex items-center gap-3 border border-[#001c9a]/5">
                <CheckCircle size={18} className="text-[#001c9a] shrink-0" />
                <p className="text-[14px] font-bold text-[#001c9a]">Quý Vy Trọng • 0945449758</p>
              </div>
            </div>

            {/* Address */}
            <div className="bg-[#fffff1] p-6 border border-[#001c9a]/5">
              <div className="flex items-center justify-between mb-3">
                <p className="text-[10px] font-black text-[#001c9a]/40 uppercase tracking-widest">Địa chỉ nhận hàng</p>
                <button onClick={() => setIsAddressOpen(true)} className="text-[12px] font-black text-[#001c9a] underline underline-offset-2">Đổi</button>
              </div>
              {selectedAddress ? (
                <div className="space-y-0.5">
                  <p className="font-bold text-[#001c9a] text-[14px]">{selectedAddress.last_name} {selectedAddress.first_name} • {selectedAddress.phone}</p>
                  <p className="text-[13px] text-[#001c9a]/60 leading-relaxed">{selectedAddress.detail}, {selectedAddress.ward}, {selectedAddress.district}, {selectedAddress.city}</p>
                </div>
              ) : (
                <button onClick={() => setIsAddressOpen(true)} className="text-[13px] font-bold text-[#001c9a] underline">+ Thêm địa chỉ</button>
              )}
            </div>

            {/* Shipping method */}
            <div className="space-y-1.5">
              <p className="text-[10px] font-bold text-[#001c9a]/40 uppercase tracking-widest italic">Phương thức giao nhận</p>
              <div className="bg-[#e9f0f8] border border-[#001c9a]/10 flex items-center justify-between p-5">
                <div className="flex items-center gap-3">
                  <div className="w-4 h-4 rounded-full border-2 border-[#001c9a] flex items-center justify-center">
                    <div className="w-2 h-2 rounded-full bg-[#001c9a]" />
                  </div>
                  <span className="text-[14px] font-bold text-[#001c9a]">Giao hàng tiêu chuẩn</span>
                </div>
                <span className="font-black text-[#001c9a] text-[14px]">0đ</span>
              </div>
            </div>

            {/* Delivery date */}
            <div className="bg-[#fffff1] p-6 border border-[#001c9a]/5">
              <p className="text-[10px] font-black text-[#001c9a]/40 uppercase tracking-widest mb-3">Ngày giao hàng</p>
              <div className="space-y-3">
                <div>
                  <label className="text-[12px] text-[#001c9a]/60 block mb-1">Ngày giao kiện hàng đầu tiên*</label>
                  <input
                    type="date"
                    value={firstDate}
                    min={new Date().toISOString().split("T")[0]}
                    onChange={(e) => setFirstDate(e.target.value)}
                    className="border-b border-[#001c9a]/20 bg-transparent py-2 w-full text-[14px] font-bold text-[#001c9a] outline-none focus:border-[#001c9a]"
                  />
                </div>
                {nextDates.length > 0 && (
                  <p className="text-[11px] text-[#001c9a]/50">
                    Ngày dự kiến các kỳ kế tiếp: {nextDates.map((d) => new Date(d).toLocaleDateString("vi-VN")).join(", ")}
                  </p>
                )}
              </div>
            </div>

            {/* Invoice toggle */}
            <div className="bg-[#fffff1] p-5 border border-[#001c9a]/5 flex items-center justify-between">
              <div className="flex items-center gap-3">
                <Receipt className="text-[#001c9a]" size={18} />
                <span className="text-[13px] font-black text-[#001c9a]">Yêu cầu xuất hoá đơn điện tử</span>
              </div>
              <button
                type="button"
                onClick={() => { if (!invoiceData) setIsInvoiceOpen(true); else setIsInvoiceEnabled(!isInvoiceEnabled); }}
                className={`w-11 h-6 rounded-full transition-all relative ${isInvoiceEnabled ? "bg-[#001c9a]" : "bg-gray-200"}`}
              >
                <div className={`absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-all ${isInvoiceEnabled ? "left-[26px]" : "left-1"}`} />
              </button>
            </div>

            {/* Payment methods */}
            <div className="space-y-1.5">
              <p className="text-[10px] font-bold text-[#001c9a]/40 uppercase tracking-widest italic">Phương thức thanh toán</p>
              <div className="border border-[#001c9a]/10 overflow-hidden">
                {PAYMENT_METHODS.map((pm) => (
                  <label
                    key={pm.id}
                    className={`flex items-center gap-3 p-4 cursor-pointer border-b last:border-b-0 transition-colors ${paymentMethod === pm.id ? "bg-[#e9f0f8]" : "bg-[#fffff1] hover:bg-[#f0f4fd]"}`}
                  >
                    <input
                      type="radio"
                      name="payment"
                      checked={paymentMethod === pm.id}
                      onChange={() => setPaymentMethod(pm.id)}
                      className="w-4 h-4 accent-[#001c9a]"
                    />
                    <pm.icon size={16} className="text-[#001c9a] shrink-0" />
                    <span className="text-[13px] font-bold text-[#001c9a]">{pm.label}</span>
                  </label>
                ))}
              </div>
            </div>
          </div>

          {/* ═══════════════════════════════ RIGHT COLUMN ═══════════════════════════════ */}
          <div className="space-y-0">
            <div className="bg-[#fffff1] border border-[#001c9a]/10 flex flex-col">
              <div className="p-8 flex-grow">
                <h2 className="text-[22px] font-black text-[#001c9a] tracking-tight mb-7">Gói Vinamilk Care</h2>

                <div className="space-y-6">
                  {schedulePreview.map((date, i) => (
                    <div key={i} className="pb-6 border-b border-[#001c9a]/10 last:border-b-0 last:pb-0">
                      {/* Kiện hàng header */}
                      <div className="flex items-center justify-between mb-3">
                        <h3 className="text-[15px] font-black text-[#001c9a] italic font-serif">Kiện hàng {i + 1}</h3>
                        <span className="px-2 py-0.5 bg-[#e9f0f8] text-[10px] font-black text-[#001c9a] uppercase tracking-wider rounded-sm">
                          Giao hàng tiêu chuẩn
                        </span>
                      </div>

                      {/* Product row */}
                      <div className="flex gap-3 items-start">
                        <div className="w-14 h-14 bg-white border border-[#001c9a]/8 flex-shrink-0 flex items-center justify-center p-1.5 rounded relative">
                          <img
                            src={getImageUrl(cartLine.variant.main_image || product.image) || ""}
                            className="w-full h-full object-contain"
                          />
                          <span className="absolute -top-2 -right-2 bg-[#d3e1ff] text-[#001c9a] text-[9px] font-black w-5 h-5 rounded-full flex items-center justify-center border border-[#001c9a]/10">
                            {quantity}
                          </span>
                        </div>
                        <div className="flex-grow min-w-0">
                          <p className="text-[13px] font-bold text-[#001c9a] line-clamp-2 leading-tight">{product.name}</p>
                          <p className="text-[11px] text-[#001c9a]/50 mt-0.5 uppercase">{cartLine.variant.volume} {cartLine.variant.packaging_type}</p>
                        </div>
                        <div className="text-right flex-shrink-0">
                          <p className="text-[11px] text-[#001c9a]/40 line-through">{formatVnd(variantBasePrice(cartLine.variant) * quantity)}</p>
                          <p className="text-[13px] font-black text-[#001c9a]">{formatVnd(variantUnitPrice(cartLine.variant) * quantity)}</p>
                        </div>
                      </div>

                      {/* Gifts — only kiện hàng 1 */}
                      {i === 0 && (
                        <>
                          {loadingGifts ? (
                            <div className="mt-3 space-y-2">
                              <div className={`h-12 ${skBg} rounded-sm`} />
                            </div>
                          ) : gifts.length > 0 && gifts.map((reward: any, idx: number) => {
                            const maybeId = reward.id ?? reward.reward_id ?? reward.item_id ?? (reward.options && reward.options[0] ? (reward.options[0].id ?? reward.options[0].item_id) : undefined);
                            const key = (typeof maybeId !== 'undefined' && maybeId !== null) ? String(maybeId) : `${product.product_id}:${variantId}`;
                            const selected = selectedGifts[key];
                            return (
                              <div key={idx} className="flex gap-3 bg-[#ccff33]/40 p-2.5 mt-3 items-center rounded-sm">
                                <div className="w-10 h-10 bg-white flex-shrink-0 flex items-center justify-center p-1 rounded-sm relative">
                                  <img src={getImageUrl(selected?.image || reward.image || "gift_default.webp") || ""} className="w-full h-full object-contain" />
                                  <span className="absolute -top-1.5 -right-1.5 bg-[#001c9a] text-white text-[9px] font-black w-4 h-4 rounded-full flex items-center justify-center">
                                    {selected?.quantity || reward.quantity || 1}
                                  </span>
                                </div>
                                <div className="flex-grow min-w-0">
                                  <p className="text-[11px] font-black text-[#001c9a] line-clamp-1">{selected?.name || reward.name}</p>
                                </div>
                                <button
                                  onClick={() => { setActiveGiftReward(reward); setIsGiftModalOpen(true); }}
                                  className="text-[11px] font-black text-[#001c9a] underline underline-offset-2 hover:text-[#0213b0] shrink-0"
                                >
                                  Đổi
                                </button>
                              </div>
                            );
                          })}

                          {/* Greeting Card toggle + display */}
                          <div className="mt-4">
                            <div className="flex items-center gap-3 mb-2">
                              <button
                                type="button"
                                onClick={() => updateDraft({ includeGreetingCard: !includeGreetingCard })}
                                className={`w-10 h-5 rounded-full transition-all relative shrink-0 ${includeGreetingCard ? "bg-[#001c9a]" : "bg-[#001c9a]/20"}`}
                              >
                                <div className={`absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-all ${includeGreetingCard ? "left-[22px]" : "left-0.5"}`} />
                              </button>
                              <span className="text-[11px] font-black text-[#001c9a] uppercase tracking-wider">Thiệp đính kèm</span>
                            </div>

                            {includeGreetingCard && (
                              <div className="flex gap-3 bg-[#ccff33]/30 p-2.5 items-center rounded-sm">
                                <div className="w-10 h-10 bg-white flex-shrink-0 flex items-center justify-center rounded-sm border border-[#001c9a]/10 overflow-hidden">
                                  <img src={getImageUrl(selectedCard?.preview_image_path || "gift_default.webp") || ""} className="w-full h-full object-cover" />
                                </div>
                                <div className="flex-grow min-w-0">
                                  <p className="text-[11px] font-black text-[#001c9a] line-clamp-1">{selectedCard?.title || "Thiệp Vinamilk Care mẫu 01"}</p>
                                </div>
                                <button
                                  onClick={() => setIsCardModalOpen(true)}
                                  className="text-[11px] font-black text-[#001c9a] underline underline-offset-2 shrink-0"
                                >
                                  Đổi
                                </button>
                              </div>
                            )}
                          </div>
                        </>
                      )}
                    </div>
                  ))}
                </div>
              </div>

              {/* ── Summary ── */}
              <div className="relative mt-auto">
                <svg viewBox="0 0 400 20" className="w-full h-5" preserveAspectRatio="none">
                  <path d="M0 20 L0 10 C 20 0, 40 20, 60 10 C 80 0, 100 20, 120 10 C 140 0, 160 20, 180 10 C 200 0, 220 20, 240 10 C 260 0, 280 20, 300 10 C 320 0, 340 20, 360 10 C 380 0, 400 20, 400 10 L 400 20 Z" fill="#e9f0f8" />
                </svg>
                <div className="px-8 pt-4 pb-8 space-y-2.5 bg-[#e9f0f8]">
                  <div className="flex justify-between text-[13px] font-medium text-[#001c9a]">
                    <span>Loại gói</span>
                    <span className="font-bold">Gói Tiêu Chuẩn</span>
                  </div>
                  <div className="flex justify-between text-[13px] font-medium text-[#001c9a]">
                    <span>Số lần giao hàng</span>
                    <span className="font-bold">{deliveryCount} lần</span>
                  </div>
                  <div className="flex justify-between text-[13px] font-medium text-[#001c9a]">
                    <span>Chu kỳ</span>
                    <span className="font-bold">1 lần/tháng</span>
                  </div>
                  <div className="flex justify-between text-[13px] font-medium text-[#001c9a] pb-2 border-b border-dashed border-[#001c9a]/15">
                    <span>Giá trị kiện hàng</span>
                    <span className="font-bold">{formatVnd(itemValue)}</span>
                  </div>
                  <div className="flex justify-between text-[13px] font-medium text-[#001c9a]">
                    <span>Tổng tiền gói</span>
                    <span className="font-bold">{formatVnd(packageSubtotal)}</span>
                  </div>
                  <div className="flex justify-between text-[13px] font-medium text-[#001c9a]">
                    <span>Ưu đãi gói (10%)</span>
                    <span className="font-bold text-[#e11d48]">-{formatVnd(calcDiscount)}</span>
                  </div>
                  <div className="flex justify-between items-center pt-3 border-t border-dashed border-[#001c9a]/15 mt-1">
                    <span className="text-[14px] font-black text-[#001c9a]">Tổng thành tiền</span>
                    <span className="text-[20px] font-black text-[#001c9a]">{formatVnd(totalAmount)}</span>
                  </div>

                  {error && <p className="text-[#e11d48] text-[13px] font-medium mt-2">{error}</p>}

                  <button
                    type="button"
                    onClick={handlePay}
                    disabled={submitting || !selectedAddress}
                    className={`w-full py-4 bg-[#001c9a] text-white font-black text-[15px] mt-3 rounded transition-colors ${submitting || !selectedAddress ? "opacity-50 cursor-not-allowed" : "hover:bg-[#0213b0]"}`}
                  >
                    {submitting ? "Đang xử lý..." : "Thanh toán"}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>

      {/* ── Sidebars / Modals ── */}
      <AddressSidebar
        isOpen={isAddressOpen}
        onClose={() => setIsAddressOpen(false)}
        selectedAddressId={selectedAddress?.id ?? null}
        onSelect={(addr) => { setSelectedAddress(addr); setIsAddressOpen(false); }}
      />
      <InvoiceSidebar
        isOpen={isInvoiceOpen}
        onClose={() => setIsInvoiceOpen(false)}
        initialData={invoiceData}
        onApply={(data) => {
          setInvoiceData(data);
          setIsInvoiceEnabled(true);
          setIsInvoiceOpen(false);
          showNotification("Lưu thông tin hóa đơn thành công");
        }}
      />

      {activeGiftReward && isGiftModalOpen && (
        <GiftSelectionPanel
          isOpen={isGiftModalOpen}
          reward={activeGiftReward}
          onClose={() => setIsGiftModalOpen(false)}
          onApply={(selectionIds: any) => {
            if (activeGiftReward) {
              const chosenOption = activeGiftReward.options?.find(
                (o: any) => o.id === selectionIds[0] || o.item_id === selectionIds[0]
              );
              if (chosenOption) {
                const maybeId = activeGiftReward.id ?? activeGiftReward.reward_id ?? activeGiftReward.item_id ?? (activeGiftReward.options && activeGiftReward.options[0] ? (activeGiftReward.options[0].id ?? activeGiftReward.options[0].item_id) : undefined);
                const key = (typeof maybeId !== 'undefined' && maybeId !== null) ? String(maybeId) : `${product.product_id}:${variantId}`;
                updateDraft({ selectedGifts: { ...selectedGifts, [key]: chosenOption } });
              }
            }
            setIsGiftModalOpen(false);
            showNotification("Cập nhật quà tặng thành công");
          }}
          isStandalone={true}
        />
      )}

      {isCardModalOpen && (
        <GreetingCardModal
          isOpen={isCardModalOpen}
          onClose={() => setIsCardModalOpen(false)}
          cards={cards}
          selectedId={greetingCardId}
          onConfirm={(cardId, include) => {
            updateDraft({ greetingCardId: cardId, includeGreetingCard: include });
            setIsCardModalOpen(false);
            if (include) showNotification("Cập nhật thiệp thành công");
            else showNotification("Đã bỏ thiệp đính kèm");
          }}
        />
      )}

      {/* Toast */}
      <AnimatePresence>
        {toast.visible && (
          <motion.div
            initial={{ y: 100, opacity: 0, x: "-50%" }}
            animate={{ y: -40, opacity: 1, x: "-50%" }}
            exit={{ y: 100, opacity: 0, x: "-50%" }}
            className="fixed bottom-0 left-1/2 z-[3000] px-6 py-2.5 bg-[#e7f9a1] rounded-full shadow-xl flex items-center gap-3 border border-[#001c9a]/10"
          >
            <CheckCircle size={16} className="text-[#001c9a]" />
            <span className="text-[13px] font-black text-[#001c9a] whitespace-nowrap">{toast.message}</span>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}
