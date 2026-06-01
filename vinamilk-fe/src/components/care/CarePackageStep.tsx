"use client";

import React, { useEffect, useState, useCallback, useRef } from "react";
import { careApi, catalogApi } from "@/lib/api";
import { CareDeliveryOption, CareGreetingCard } from "@/types/care";
import { Product, ProductVariant } from "@/types";
import { useCareCart, formatVnd, variantUnitPrice, variantBasePrice } from "@/context/CareCartContext";
import { GreetingCardModal } from "./GreetingCardModal";
import VariantSelector from "@/components/catalog/VariantSelector";
import { Minus, Plus, ArrowLeft, Trash2, Check, Gift, X } from "lucide-react";

/* ─── Constants ───────────────────────────────── */
const BENEFITS_STANDARD = [
  "Sữa giao tận nhà, đều đặn mỗi tháng 1 lần.",
  "Gọi điện thăm hỏi và tư vấn sức khỏe mỗi 2 tuần",
  "1 tấm thiệp gửi gắm trọn lời yêu",
  "Là những người đầu tiên được thử sản phẩm mới miễn phí",
  "Vận chuyển miễn phí",
];

const BENEFITS_PREMIUM = [
  ...BENEFITS_STANDARD,
  "Bộ quà tặng cao cấp",
  "Kiểm tra sức khỏe định kỳ miễn phí",
];

const PACKAGE_DISCOUNT = 0.1; // 10%

// Mock gift list (until API is ready)
import { getImageUrl } from "@/lib/api";

/* ─── Skeleton Components ─────────────────────── */
const skBg = "bg-[#001c9a]/10";

function PricingSkeleton() {
  return (
    <div className="animate-pulse space-y-3 pt-4">
      <div className="flex justify-between">
        <div className={`h-4 ${skBg} rounded w-24`} />
        <div className={`h-4 ${skBg} rounded w-20`} />
      </div>
      <div className="flex justify-between">
        <div className={`h-4 ${skBg} rounded w-28`} />
        <div className={`h-4 ${skBg} rounded w-24`} />
      </div>
      <div className="flex justify-between">
        <div className={`h-4 ${skBg} rounded w-20`} />
        <div className={`h-4 ${skBg} rounded w-20`} />
      </div>
      <div className="border-t border-dashed border-[#001c9a]/15 pt-3 flex justify-between">
        <div className={`h-5 ${skBg} rounded w-24`} />
        <div className={`h-5 ${skBg} rounded w-28`} />
      </div>
    </div>
  );
}

function GiftRowSkeleton() {
  return (
    <div className="animate-pulse bg-[#ccff33]/50 rounded-lg px-4 py-3 flex items-center justify-between">
      <div className="flex items-center gap-3">
        <div className="w-7 h-7 rounded-full bg-white/40" />
        <div className={`h-4 ${skBg} rounded w-40`} />
      </div>
      <div className={`h-3 ${skBg} rounded w-14`} />
    </div>
  );
}

function DeliveryButtonsSkeleton() {
  return (
    <div className="animate-pulse flex gap-2 mb-5">
      {[1, 2, 3].map((i) => (
        <div key={i} className={`flex-1 py-2 h-10 rounded-full ${skBg}`} />
      ))}
    </div>
  );
}

function RightColumnSkeleton() {
  return (
    <div className="animate-pulse space-y-4">
      {/* Tab skeleton */}
      <div className="flex rounded-lg overflow-hidden border border-[#001c9a]/10">
        <div className={`flex-1 h-12 ${skBg}`} />
        <div className="flex-1 h-12 bg-[#001c9a]/5" />
      </div>
      {/* Title */}
      <div className={`h-4 ${skBg} rounded w-40`} />
      {/* Benefit lines */}
      {[1, 2, 3, 4, 5].map((i) => (
        <div key={i} className="flex items-center gap-3">
          <div className="w-5 h-5 rounded-full bg-[#001c9a]/15" />
          <div className={`h-3.5 ${skBg} rounded`} style={{ width: `${50 + i * 8}%` }} />
        </div>
      ))}
      {/* Delivery buttons */}
      <div className={`h-4 ${skBg} rounded w-28 mt-2`} />
      <div className="flex gap-2">
        {[1, 2, 3].map((i) => (
          <div key={i} className={`flex-1 h-10 rounded-full ${skBg}`} />
        ))}
      </div>
      {/* Pricing lines */}
      <div className="border-t border-dashed border-[#001c9a]/15 pt-4 space-y-3">
        {[1, 2, 3, 4].map((i) => (
          <div key={i} className="flex justify-between">
            <div className={`h-4 ${skBg} rounded w-24`} />
            <div className={`h-4 ${skBg} rounded w-20`} />
          </div>
        ))}
      </div>
      {/* CTA button */}
      <div className={`h-12 rounded-lg ${skBg} mt-4`} />
    </div>
  );
}

/* ─── Gift Selection Modal ────────────────────── */
function GiftSelectionModal({
  isOpen,
  onClose,
  onSelect,
  currentGiftId,
  gifts,
}: {
  isOpen: boolean;
  onClose: () => void;
  onSelect: (gift: any) => void;
  currentGiftId: number | null;
  gifts: any[];
}) {
  const [localGiftId, setLocalGiftId] = useState<number | null>(currentGiftId);

  // Sync local state when opened
  useEffect(() => {
    if (isOpen) {
      setLocalGiftId(currentGiftId);
    }
  }, [isOpen, currentGiftId]);

  if (!isOpen) return null;

  const handleApply = () => {
    const gift = gifts.find(g => g.id === localGiftId);
    if (gift) onSelect(gift);
    onClose();
  };

  return (
    <div className="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/80">
      <div className="bg-[#fffff1] rounded-xl w-full max-w-lg flex flex-col shadow-2xl max-h-[80vh]">
        {/* Header */}
        <div className="flex items-center justify-between px-6 py-4 border-b border-[#001c9a]/10 shrink-0">
          <div className="flex items-center gap-3">
            <Gift size={20} className="text-[#001c9a]" />
            <h3 className="text-lg font-bold text-[#001c9a] tracking-tight">Đổi quà tặng</h3>
          </div>
          <button onClick={onClose} className="p-1.5 rounded-full hover:bg-[#001c9a]/5">
            <X size={18} className="text-[#001c9a]/50" strokeWidth={1.5} />
          </button>
        </div>

        {/* Gift list */}
        <div className="flex-1 overflow-y-auto p-6 navy-scrollbar space-y-3">
          <p className="text-xs text-[#001c9a]/60 mb-4">
            Chọn một món quà tặng kèm theo gói Vinamilk Care của bạn. Quà sẽ được giao cùng lần giao hàng đầu tiên.
          </p>
          {gifts.map((gift) => (
            <button
              key={gift.id}
              type="button"
              onClick={() => setLocalGiftId(gift.id)}
              className={`w-full flex items-center gap-4 p-4 rounded-lg border-2 transition-all text-left ${localGiftId === gift.id
                ? "border-[#001c9a] bg-[#001c9a]/5"
                : "border-[#001c9a]/10 hover:border-[#001c9a]/30"
                }`}
            >
              {/* Radio */}
              <div className={`w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 ${localGiftId === gift.id ? "border-[#001c9a]" : "border-[#001c9a]/30"
                }`}>
                {localGiftId === gift.id && <div className="w-2.5 h-2.5 rounded-full bg-[#001c9a]" />}
              </div>

              {/* Gift Image */}
              <div className="w-14 h-14 bg-gray-100 rounded-md border border-gray-200 shrink-0 flex items-center justify-center p-1">
                <img src={getImageUrl(gift.image || "gift_default.webp") || ""} alt="" className="w-full h-full object-contain" />
              </div>

              {/* Gift info */}
              <div className="flex-1 min-w-0">
                <p className="text-sm font-bold text-[#001c9a] truncate">{gift.name}</p>
                <p className="text-xs text-[#001c9a]/50 mt-0.5">Số lượng: x{gift.quantity || 1}</p>
              </div>
            </button>
          ))}
        </div>

        {/* Footer */}
        <div className="p-4 border-t border-[#001c9a]/10 shrink-0">
          <button
            type="button"
            onClick={handleApply}
            className="w-full bg-[#001c9a] text-white py-3 rounded-lg font-bold hover:bg-[#0213b0] transition-colors"
          >
            Áp dụng
          </button>
        </div>
      </div>
    </div>
  );
}

/* ─── Main Component ──────────────────────────── */
interface Props {
  onContinue: () => void;
}

export function CarePackageStep({ onContinue }: Props) {
  const {
    cartLine,
    deliveryCount,
    includeGreetingCard,
    greetingCardId,
    firstDeliveryDate,
    pricing,
    updateDraft,
    setPricing,
    setCartLine,
    updateCartQuantity,
    setView,
    selectedGifts,
  } = useCareCart();

  const [options, setOptions] = useState<CareDeliveryOption[]>([]);
  const [cards, setCards] = useState<CareGreetingCard[]>([]);
  const [cardModalOpen, setCardModalOpen] = useState(false);
  const [giftModalOpen, setGiftModalOpen] = useState(false);
  const [rewardOptions, setRewardOptions] = useState<any[]>([]);
  const [loading, setLoading] = useState(false);
  const [loadingGifts, setLoadingGifts] = useState(true);
  const [initialLoading, setInitialLoading] = useState(true);
  const [catalogProduct, setCatalogProduct] = useState<Product | null>(null);
  const [activeTab, setActiveTab] = useState<"standard" | "premium">("standard");
  const initRef = useRef(false);
  const calcKeyRef = useRef("");

  const product = cartLine?.product;
  const selectedVariant = cartLine?.variant ?? null;
  const quantity = cartLine?.quantity ?? 1;

  const recalc = useCallback(
    async (overrides?: { deliveryCount?: number; quantity?: number; variantId?: number; date?: string }) => {
      if (!product || !cartLine) return;
      const variantId = overrides?.variantId ?? selectedVariant?.id;
      const qty = overrides?.quantity ?? quantity;
      const count = overrides?.deliveryCount ?? deliveryCount;
      const date = overrides?.date ?? firstDeliveryDate;
      if (!variantId) return;
      setLoading(true);
      try {
        const result = await careApi.calculate({
          care_product_id: product.id,
          variant_id: variantId,
          quantity: qty,
          delivery_count: count,
          first_delivery_date: date,
        });
        setPricing(result);
        if (count !== deliveryCount || date !== firstDeliveryDate) {
          updateDraft({ deliveryCount: count, firstDeliveryDate: date });
        }
      } finally {
        setLoading(false);
      }
    },
    [product, cartLine, selectedVariant, quantity, deliveryCount, firstDeliveryDate, setPricing, updateDraft]
  );

  useEffect(() => {
    if (!product?.slug || initRef.current) return;
    initRef.current = true;
    Promise.all([
      catalogApi.getProduct(product.slug).then((res) => {
        setCatalogProduct((res.data ?? res.product) as Product);
      }),
      careApi.getPage().then((r) => setOptions(r.delivery_options || [])),
      careApi.getGreetingCards().then((r) => {
        setCards(r.cards || []);
        if (!greetingCardId && r.cards?.[0]) {
          updateDraft({ greetingCardId: r.cards[0].id });
        }
      }),
    ]).finally(() => setInitialLoading(false));
    if (!firstDeliveryDate) {
      const d = new Date();
      d.setDate(d.getDate() + 7);
      updateDraft({ firstDeliveryDate: d.toISOString().split("T")[0] });
    }
  }, [product?.slug]);

  // Evaluate cart for gifts
  useEffect(() => {
    if (!product || !selectedVariant || !catalogProduct) return;
    setLoadingGifts(true);
    const evaluate = async () => {
      try {
        const payload = {
          items: [{
            product_id: product.product_id,
            variant_id: selectedVariant.id,
            quantity: quantity,
            price: variantUnitPrice(selectedVariant),
            category_id: catalogProduct.category_id || 1
          }]
        };
        const response = await catalogApi.evaluateCart(payload);
        if (response.success && response.data.gifts?.length > 0) {
          const reward = response.data.gifts[0];
          const opts = reward.options || [];
          setRewardOptions(opts);
          if (opts.length > 0) {
            // determine a stable reward key (support different response shapes)
            const maybeId = reward.id ?? reward.reward_id ?? reward.item_id ?? (opts[0]?.id ?? opts[0]?.item_id);
            const rewardKey = (typeof maybeId !== 'undefined' && maybeId !== null) ? String(maybeId) : `${product.product_id}:${selectedVariant.id}`;
            if (!selectedGifts[rewardKey]) {
              updateDraft({ selectedGifts: { ...selectedGifts, [rewardKey]: opts[0] } });
            }
          }
        } else {
          setRewardOptions([]);
        }
      } catch (err) { }
      finally { setLoadingGifts(false); }
    };
    evaluate();
  }, [product, selectedVariant, quantity, catalogProduct, selectedGifts, updateDraft]);

  useEffect(() => {
    if (!selectedVariant?.id || !quantity || !product?.id || !firstDeliveryDate) return;
    const key = `${product.id}:${selectedVariant.id}:${quantity}:${deliveryCount}:${firstDeliveryDate}`;
    if (calcKeyRef.current === key) return;
    calcKeyRef.current = key;
    recalc();
  }, [selectedVariant?.id, quantity, deliveryCount, firstDeliveryDate, product?.id, recalc]);

  const handleVariantChange = (variant: ProductVariant) => {
    if (!cartLine || cartLine.variant?.id === variant.id) return;
    calcKeyRef.current = "";
    setCartLine({ ...cartLine, variant });
  };

  const changeQty = (delta: number) => {
    const next = Math.max(1, Math.min(99, quantity + delta));
    if (next === quantity) return;
    calcKeyRef.current = "";
    updateCartQuantity(next);
  };

  if (!product || !cartLine) {
    return (
      <p className="text-center py-20 text-[#001c9a]">
        <button type="button" onClick={() => setView("main")} className="font-bold underline">
          Chọn sản phẩm trước
        </button>
      </p>
    );
  }

  const selectedCard = cards.find((c) => c.id === greetingCardId);
  const unitPrice = selectedVariant ? variantUnitPrice(selectedVariant) : product.care_price;
  
  // Try to find the reward that matches rewardOptions using same key generation logic
  let selectedGift = rewardOptions[0];
  if (rewardOptions.length > 0 && selectedVariant) {
    const firstReward = { id: rewardOptions[0].id, reward_id: rewardOptions[0].reward_id, item_id: rewardOptions[0].item_id, options: rewardOptions };
    const maybeId = firstReward.id ?? firstReward.reward_id ?? firstReward.item_id ?? (firstReward.options && firstReward.options[0] ? (firstReward.options[0].id ?? firstReward.options[0].item_id) : undefined);
    const rewardKey = (typeof maybeId !== 'undefined' && maybeId !== null) ? String(maybeId) : `${product.product_id}:${selectedVariant.id}`;
    selectedGift = selectedGifts[rewardKey] || rewardOptions[0];
  }

  // Pricing calculation
  const packageSubtotal = pricing?.package_subtotal ?? (unitPrice * quantity * deliveryCount);
  const discountAmount = Math.round(packageSubtotal * PACKAGE_DISCOUNT);
  const totalAmount = packageSubtotal - discountAmount;
  const itemValue = pricing ? Math.round(pricing.package_subtotal / (pricing.delivery_count || deliveryCount)) : unitPrice * quantity;

  const benefits = activeTab === "standard" ? BENEFITS_STANDARD : BENEFITS_PREMIUM;

  

  return (
    <div className="flex flex-col md:flex-row min-h-0" style={{ fontFamily: "'Be Vietnam Pro', sans-serif" }}>

      {/* ────────────── LEFT COLUMN — STICKY ────────────── */}
      <div className="w-full md:w-[45%] shrink-0 md:border-r border-[#001c9a]/10 p-6 md:sticky md:top-0 h-fit">
        {/* Back */}
        <button
          type="button"
          onClick={() => setView("main")}
          className="inline-flex items-center gap-2 text-[#001c9a] mb-5 text-sm font-semibold hover:underline"
        >
          <ArrowLeft size={16} /> Quay lại chọn sản phẩm
        </button>

        <h2 className="text-xs font-bold text-[#001c9a] uppercase tracking-widest mb-1">
          SẢN PHẨM TRONG GÓI
        </h2>
        <p className="text-xs text-[#001c9a]/50 mb-4">
          *Số lượng sản phẩm sẽ cố định theo gói và được giao theo chu kỳ mỗi tháng 1 lần
        </p>

        {/* Product row */}
        <div className="mb-2">
          <div className="flex gap-4 pb-3 items-start">
            {product.image && (
              <img src={product.image} alt="" className="w-16 h-16 object-contain shrink-0" />
            )}
            <div className="flex-1 min-w-0">
              <p className="font-bold text-[#001c9a] text-sm mb-1 truncate">{product.name}</p>
              {selectedVariant && (
                <p className="text-xs text-[#001c9a]/50 mb-2">
                  {selectedVariant.volume}{selectedVariant.packaging_type ? ` ${selectedVariant.packaging_type}` : ""}
                </p>
              )}
              {/* Qty stepper */}
              <div className="inline-flex items-center gap-3 border border-[#001c9a]/20 rounded-lg px-3 py-1 bg-white">
                <button type="button" onClick={() => changeQty(-1)} className="text-[#001c9a] p-0.5">
                  <Minus size={14} />
                </button>
                <span className="font-bold text-[#001c9a] text-sm w-5 text-center">{quantity}</span>
                <button type="button" onClick={() => changeQty(1)} className="text-[#001c9a] p-0.5">
                  <Plus size={14} />
                </button>
              </div>
            </div>
            {/* Price + delete */}
            <div className="flex flex-col items-end gap-1 shrink-0">
              <button type="button" className="text-[#001c9a]/30 hover:text-[#001c9a]/60 transition-colors">
                <Trash2 size={16} />
              </button>
              {selectedVariant && (
                <>
                  {selectedVariant.discount_percentage > 0 && (
                    <span className="text-xs text-[#001c9a]/40 line-through">
                      {formatVnd(variantBasePrice(selectedVariant) * quantity)}
                    </span>
                  )}
                  <span className="font-bold text-[#001c9a] text-sm">
                    {formatVnd(variantUnitPrice(selectedVariant) * quantity)}
                  </span>
                </>
              )}
            </div>
          </div>
        </div>

        {/* Gift row */}
        {loadingGifts ? (
          <GiftRowSkeleton />
        ) : selectedGift ? (
          <div className="bg-[#ccff33] rounded-lg px-4 py-3 flex items-center justify-between mb-0">
            <div className="flex items-center gap-3 min-w-0">
              <div className="w-12 h-12 flex-shrink-0 flex items-center justify-center p-1.5 relative rounded-md">
                <img src={getImageUrl(selectedGift.image || "gift_default.webp") || ""} className="w-full h-full object-contain" />
                <span className="absolute -top-2 -right-2 bg-[#001c9a] text-white text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center border-2 border-[#fffff1]">
                  {selectedGift.quantity || 1}
                </span>
              </div>
              <span className="text-sm font-bold text-[#001c9a] truncate">{selectedGift.name}</span>
            </div>
            <button
              type="button"
              onClick={() => setGiftModalOpen(true)}
              className="text-xs font-bold text-[#001c9a] uppercase hover:underline shrink-0 ml-2"
            >
              Đổi quà
            </button>
          </div>
        ) : null}

        {/* Greeting card toggle */}
        <div className="flex items-center justify-between mt-5 pt-4 border-t border-[#001c9a]/10 mb-1.5">
          <div className="flex items-center gap-3">
            <button
              type="button"
              onClick={() => updateDraft({ includeGreetingCard: !includeGreetingCard })}
              className={`w-11 h-[26px] rounded-full relative transition-colors shrink-0 ${includeGreetingCard ? "bg-[#001c9a]" : "bg-gray-300"}`}
            >
              <span
                className="absolute top-[3px] w-5 h-5 bg-white rounded-full transition-[left] shadow-sm"
                style={{ left: includeGreetingCard ? 21 : 3 }}
              />
            </button>
            <p className="font-bold text-[#001c9a] text-sm">Thiệp đính kèm</p>
          </div>
        </div>
        <p className="text-xs text-[#001c9a]/50 mb-3">
          *Thiệp sẽ gửi đến người yêu thương của bạn vào lần đầu tiên giao hàng
        </p>

        {includeGreetingCard && (
          <div className="bg-[#ccff33] rounded-lg px-4 py-3 flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div className="w-12 h-12 bg-white flex-shrink-0 flex items-center justify-center rounded-md border border-[#001c9a]/10 overflow-hidden">
                <img src={getImageUrl(selectedCard?.preview_image_path || "gift_default.webp") || ""} className="w-full h-full object-cover" />
              </div>
              <span className="text-sm font-bold text-[#001c9a]">
                {selectedCard?.title || "Thiệp Vinamilk Care mẫu 01"}
              </span>
            </div>
            <button
              type="button"
              onClick={() => setCardModalOpen(true)}
              className="text-xs font-bold text-[#001c9a] uppercase hover:underline shrink-0 ml-2"
            >
              {greetingCardId ? "Đổi lời nhắn" : "Chọn lời nhắn"}
            </button>
          </div>
        )}
      </div>

      {/* ────────────── RIGHT COLUMN — SCROLLABLE ────────────── */}
      <div className="flex-1 p-6">
        {initialLoading ? <RightColumnSkeleton /> : <>
          {/* Tab header */}
          <div className="flex rounded-lg overflow-hidden border border-[#001c9a]/10 mb-6">
            <button
              type="button"
              onClick={() => setActiveTab("standard")}
              className={`flex-1 py-3 text-sm font-bold transition-all ${activeTab === "standard"
                ? "bg-[#d3e1ff] text-[#001c9a] border-b-[2.5px] border-[#001c9a]"
                : "bg-[#001c9a]/[0.02] text-[#001c9a]/40 border-b-[2.5px] border-transparent"
                }`}
            >
              Gói Tiêu Chuẩn
            </button>
            <button
              type="button"
              onClick={() => setActiveTab("premium")}
              className={`flex-1 py-3 text-sm font-bold transition-all ${activeTab === "premium"
                ? "bg-[#d3e1ff] text-[#001c9a] border-b-[2.5px] border-[#001c9a]"
                : "bg-[#001c9a]/[0.02] text-[#001c9a]/40 border-b-[2.5px] border-transparent"
                }`}
            >
              Gói Cao Cấp
            </button>
          </div>


          <h3 className="text-sm font-black text-[#001c9a] uppercase tracking-wider mb-4">ƯU ĐÃI ĐỘC QUYỀN</h3>

          {/* Benefits list */}
          <ul className="space-y-3 mb-6">
            {benefits.map((b) => (
              <li key={b} className="flex items-start gap-3 text-sm text-[#001c9a]">
                <span className="w-5 h-5 rounded-full bg-[#001c9a] flex items-center justify-center shrink-0 mt-0.5">
                  <Check size={11} color="#fff" strokeWidth={3} />
                </span>
                {b}
              </li>
            ))}
          </ul>

          {/* ─── Pricing / Delivery Section (only for Standard) ─── */}
          {activeTab === "standard" ? (
            <>
              
              {/* Delivery count selector */}
              <p className="text-sm font-bold text-[#001c9a] mb-3">Số lần giao hàng</p>
              {options.length === 0 ? <DeliveryButtonsSkeleton /> : (
                <div className="flex gap-2 mb-5">
                  {options.map((o) => (
                    <button
                      key={o.id}
                      type="button"
                      onClick={() => {
                        // Optimistic update: change deliveryCount instantly
                        updateDraft({ deliveryCount: o.delivery_count });
                        calcKeyRef.current = "";
                        recalc({ deliveryCount: o.delivery_count });
                      }}
                      className={`flex-1 py-2 rounded-full text-sm font-bold transition-all border-2 ${deliveryCount === o.delivery_count
                        ? "border-[#001c9a] bg-[#001c9a]/[0.07] text-[#001c9a]"
                        : "border-[#001c9a]/20 text-[#001c9a]/50 hover:border-[#001c9a]/40"
                        }`}
                    >
                      {o.delivery_count} lần
                    </button>
                  ))}
                </div>
              )}

              {/* Pricing breakdown */}
              {loading ? (
                <PricingSkeleton />
              ) : (
                <div className="border-t border-dashed border-[#001c9a]/15 pt-4 space-y-2.5">
                  <div className="flex justify-between text-sm text-[#001c9a]">
                    <span>Chu kỳ</span>
                    <span className="font-semibold">1 lần/tháng</span>
                  </div>
                  <div className="flex justify-between text-sm text-[#001c9a] pb-2.5 border-b border-dashed border-[#001c9a]/15">
                    <span>Giá trị kiện hàng</span>
                    <span className="font-semibold">{formatVnd(itemValue)}</span>
                  </div>
                  {pricing && (
                    <>
                      <div className="flex justify-between text-sm text-[#001c9a]">
                        <span>Tổng tiền gói</span>
                        <span className="font-semibold">{formatVnd(packageSubtotal)}</span>
                      </div>
                      <div className="flex justify-between text-sm text-[#001c9a]">
                        <span>Ưu đãi gói (10%)</span>
                        <span className="font-semibold text-red-500">-{formatVnd(discountAmount)}</span>
                      </div>
                      <div className="flex justify-between text-base font-black text-[#001c9a] pt-2.5 border-t border-dashed border-[#001c9a]/15">
                        <h3>Giá tạm tính</h3>
                        <span>{formatVnd(totalAmount)}</span>
                      </div>
                    </>
                  )}
                </div>
              )}

              {/* CTA button */}
              <button
                type="button"
                disabled={loading || !pricing || !selectedVariant}
                onClick={onContinue}
                className={`mt-6 w-full bg-[#001c9a] text-white py-3.5 rounded-lg font-bold text-[15px] transition-opacity ${loading || !pricing || !selectedVariant ? "opacity-50 cursor-not-allowed" : "hover:bg-[#0213b0] cursor-pointer"
                  }`}
              >
                {loading ? "Đang tính toán..." : "Thanh toán gói →"}
              </button>
            </>
          ) : (
            /* ─── Premium tab — Coming Soon ─── */
            <div className="text-center py-12 px-6">
              <div className="w-20 h-20 rounded-full bg-[#ccff33]/30 flex items-center justify-center mx-auto mb-5">
                <span className="text-3xl">✦</span>
              </div>
              <h4 className="text-lg font-black text-[#001c9a] mb-2">Gói Cao Cấp sắp ra mắt!</h4>
              <p className="text-sm text-[#001c9a]/60 max-w-sm mx-auto leading-relaxed">
                Vinamilk đang hoàn thiện các quyền lợi đặc biệt dành riêng cho gói Cao Cấp.
                Hãy đăng ký Gói Tiêu Chuẩn và trải nghiệm dịch vụ tuyệt vời ngay hôm nay!
              </p>
              <button
                type="button"
                onClick={() => setActiveTab("standard")}
                className="mt-6 bg-[#001c9a] text-white px-8 py-3 rounded-lg font-bold text-sm hover:bg-[#0213b0] transition-colors"
              >
                Xem Gói Tiêu Chuẩn
              </button>
            </div>
          )}
        </>}
      </div>

      {/* ────────────── MODALS ────────────── */}
      <GreetingCardModal
        cards={cards}
        selectedId={greetingCardId}
        isOpen={cardModalOpen}
        onClose={() => setCardModalOpen(false)}
        onConfirm={(id, include) => {
          updateDraft({ greetingCardId: id, includeGreetingCard: include });
          setCardModalOpen(false);
        }}
      />

      <GiftSelectionModal
        isOpen={giftModalOpen}
        onClose={() => setGiftModalOpen(false)}
        gifts={rewardOptions}
        currentGiftId={selectedGift?.id ?? null}
        onSelect={(gift) => {
            // Use same key generation logic as when saving
            if (rewardOptions.length > 0 && selectedVariant) {
              const firstReward = { id: rewardOptions[0].id, reward_id: rewardOptions[0].reward_id, item_id: rewardOptions[0].item_id, options: rewardOptions };
              const maybeId = firstReward.id ?? firstReward.reward_id ?? firstReward.item_id ?? (firstReward.options && firstReward.options[0] ? (firstReward.options[0].id ?? firstReward.options[0].item_id) : undefined);
              const activeKey = (typeof maybeId !== 'undefined' && maybeId !== null) ? String(maybeId) : `${product.product_id}:${selectedVariant.id}`;
              updateDraft({ selectedGifts: { ...selectedGifts, [activeKey]: gift } });
            }
          setGiftModalOpen(false);
        }}
      />
    </div>
  );
}