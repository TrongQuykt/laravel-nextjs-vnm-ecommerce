"use client";

import React, { useEffect, useState, useCallback, useRef } from "react";
import { careApi, catalogApi } from "@/lib/api";
import { CareDeliveryOption, CareGreetingCard } from "@/types/care";
import { Product, ProductVariant } from "@/types";
import { useCareCart, formatVnd, variantUnitPrice, variantBasePrice } from "@/context/CareCartContext";
import { GreetingCardModal } from "./GreetingCardModal";
import VariantSelector from "@/components/catalog/VariantSelector";
import { Minus, Plus, ArrowLeft, Trash2, Check } from "lucide-react";

const BENEFITS_STANDARD = [
  "Sữa giao tận nhà, đều đặn mỗi tháng 1 lần.",
  "Gọi điện thăm hỏi và tư vấn sức khỏe mỗi 2 tuần",
  "1 tấm thiệp gửi gắm trọn lời yêu",
  "Là những người đầu tiên được thử sản phẩm mới miễn phí",
  "Vận chuyển miễn phí",
];

const BENEFITS_PREMIUM = [
  "Sữa giao tận nhà, đều đặn mỗi tháng 1 lần.",
  "Gọi điện thăm hỏi và tư vấn sức khỏe mỗi 2 tuần",
  "1 tấm thiệp gửi gắm trọn lời yêu",
  "Là những người đầu tiên được thử sản phẩm mới miễn phí",
  "Vận chuyển miễn phí",
  "Bộ quà tặng cao cấp",
  "Kiểm tra sức khỏe định kỳ miễn phí",
];

const PACKAGE_DISCOUNT = 0.1; // 10%

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
  } = useCareCart();

  const [options, setOptions] = useState<CareDeliveryOption[]>([]);
  const [cards, setCards] = useState<CareGreetingCard[]>([]);
  const [cardModalOpen, setCardModalOpen] = useState(false);
  const [loading, setLoading] = useState(false);
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
    catalogApi.getProduct(product.slug).then((res) => {
      setCatalogProduct((res.data ?? res.product) as Product);
    });
    careApi.getPage().then((r) => setOptions(r.delivery_options || []));
    careApi.getGreetingCards().then((r) => {
      setCards(r.cards || []);
      if (!greetingCardId && r.cards?.[0]) {
        updateDraft({ greetingCardId: r.cards[0].id });
      }
    });
    if (!firstDeliveryDate) {
      const d = new Date();
      d.setDate(d.getDate() + 7);
      updateDraft({ firstDeliveryDate: d.toISOString().split("T")[0] });
    }
  }, [product?.slug]);

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
      <p className="text-center py-20" style={{ color: "#001c9a" }}>
        <button type="button" onClick={() => setView("main")} style={{ fontWeight: 700, textDecoration: "underline" }}>
          Chọn sản phẩm trước
        </button>
      </p>
    );
  }

  const selectedCard = cards.find((c) => c.id === greetingCardId);
  const unitPrice = selectedVariant ? variantUnitPrice(selectedVariant) : product.care_price;

  // Pricing calculation
  const packageSubtotal = pricing?.package_subtotal ?? (unitPrice * quantity * deliveryCount);
  const discountAmount = Math.round(packageSubtotal * PACKAGE_DISCOUNT);
  const totalAmount = packageSubtotal - discountAmount;
  const itemValue = pricing ? Math.round(pricing.package_subtotal / (pricing.delivery_count || deliveryCount)) : unitPrice * quantity;

  const benefits = activeTab === "standard" ? BENEFITS_STANDARD : BENEFITS_PREMIUM;

  return (
    <div style={{ paddingBottom: 48, fontFamily: "'Be Vietnam Pro', sans-serif" }}>
      {/* Back button */}
      <button
        type="button"
        onClick={() => setView("main")}
        style={{
          display: "inline-flex",
          alignItems: "center",
          gap: 8,
          color: "#001c9a",
          marginBottom: 24,
          fontSize: 14,
          fontWeight: 600,
          background: "none",
          border: "none",
          cursor: "pointer",
          padding: 0,
        }}
      >
        <ArrowLeft size={16} /> Quay lại chọn sản phẩm
      </button>

      <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 32 }}>
        {/* LEFT COLUMN */}
        <div>
          <h2 style={{
            fontSize: 13,
            fontWeight: 700,
            color: "#001c9a",
            textTransform: "uppercase",
            letterSpacing: "0.08em",
            marginBottom: 4,
          }}>
            SẢN PHẨM TRONG GÓI
          </h2>
          <p style={{ fontSize: 12, color: "rgba(0,28,154,0.6)", marginBottom: 16 }}>
            *Số lượng sản phẩm sẽ cố định theo gói và được giao theo chu kỳ mỗi tháng 1 lần
          </p>

          {/* Product row */}
          <div style={{ marginBottom: 8 }}>
            <div style={{ display: "flex", gap: 16, paddingBottom: 12, alignItems: "flex-start" }}>
              {product.image && (
                <img
                  src={product.image}
                  alt=""
                  style={{ width: 64, height: 64, objectFit: "contain", flexShrink: 0 }}
                />
              )}
              <div style={{ flex: 1 }}>
                <p style={{ fontWeight: 700, color: "#001c9a", fontSize: 14, marginBottom: 4 }}>{product.name}</p>
                {selectedVariant && (
                  <p style={{ fontSize: 12, color: "rgba(0,28,154,0.6)", marginBottom: 8 }}>
                    {selectedVariant.volume}{selectedVariant.packaging_type ? ` ${selectedVariant.packaging_type}` : ""}
                  </p>
                )}
                {/* Qty stepper */}
                <div style={{ display: "inline-flex", alignItems: "center", gap: 12, border: "1.5px solid rgba(0,28,154,0.2)", borderRadius: 8, padding: "4px 10px", background: "#fff" }}>
                  <button
                    type="button"
                    onClick={() => changeQty(-1)}
                    style={{ background: "none", border: "none", cursor: "pointer", color: "#001c9a", padding: "2px 4px", display: "flex", alignItems: "center" }}
                  >
                    <Minus size={14} />
                  </button>
                  <span style={{ fontWeight: 700, color: "#001c9a", width: 20, textAlign: "center", fontSize: 14 }}>{quantity}</span>
                  <button
                    type="button"
                    onClick={() => changeQty(1)}
                    style={{ background: "none", border: "none", cursor: "pointer", color: "#001c9a", padding: "2px 4px", display: "flex", alignItems: "center" }}
                  >
                    <Plus size={14} />
                  </button>
                </div>
              </div>
              {/* Price + delete */}
              <div style={{ display: "flex", flexDirection: "column", alignItems: "flex-end", gap: 4, minWidth: 100 }}>
                <button
                  type="button"
                  style={{ background: "none", border: "none", cursor: "pointer", color: "rgba(0,28,154,0.4)", padding: 0, marginBottom: 4 }}
                >
                  <Trash2 size={17} />
                </button>
                {selectedVariant && (
                  <>
                    {selectedVariant.discount_percentage > 0 && (
                      <span style={{ fontSize: 12, color: "rgba(0,28,154,0.4)", textDecoration: "line-through" }}>
                        {formatVnd(variantBasePrice(selectedVariant) * quantity)}
                      </span>
                    )}
                    <span style={{ fontWeight: 700, color: "#001c9a", fontSize: 14 }}>
                      {formatVnd(variantUnitPrice(selectedVariant) * quantity)}
                    </span>
                  </>
                )}
              </div>
            </div>

            {/* Gift row */}
            <div style={{
              background: "#ccff33",
              borderRadius: 8,
              padding: "10px 14px",
              display: "flex",
              alignItems: "center",
              justifyContent: "space-between",
              marginBottom: 0,
            }}>
              <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
                <div style={{
                  width: 28, height: 28, borderRadius: "50%",
                  background: "rgba(255,255,255,0.6)",
                  display: "flex", alignItems: "center", justifyContent: "center",
                  fontSize: 13, fontWeight: 900,
                  border: "1px solid rgba(0,28,154,0.1)",
                }}>
                  <span style={{ color: "#001c9a", fontSize: 11 }}>8</span>
                </div>
                <span style={{ fontSize: 13, fontWeight: 700, color: "#001c9a" }}>
                  Sữa hạt 9 loại hạt Vinamilk 180ml (24H/T)
                </span>
              </div>
              <button
                type="button"
                style={{ fontSize: 12, fontWeight: 700, color: "#001c9a", background: "none", border: "none", cursor: "pointer", textTransform: "uppercase" }}
              >
                Đổi quà
              </button>
            </div>
          </div>

          {/* Greeting card toggle */}
          <div style={{
            display: "flex",
            alignItems: "center",
            justifyContent: "space-between",
            marginTop: 24,
            paddingTop: 20,
            borderTop: "1px solid rgba(0,28,154,0.1)",
            marginBottom: 6,
          }}>
            <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
              {/* Toggle */}
              <button
                type="button"
                onClick={() => updateDraft({ includeGreetingCard: !includeGreetingCard })}
                style={{
                  width: 44,
                  height: 26,
                  borderRadius: 13,
                  background: includeGreetingCard ? "#001c9a" : "#d1d5db",
                  border: "none",
                  cursor: "pointer",
                  position: "relative",
                  transition: "background 0.2s",
                  flexShrink: 0,
                }}
              >
                <span
                  style={{
                    position: "absolute",
                    top: 3,
                    left: includeGreetingCard ? 21 : 3,
                    width: 20,
                    height: 20,
                    background: "#fff",
                    borderRadius: "50%",
                    transition: "left 0.2s",
                    boxShadow: "0 1px 3px rgba(0,0,0,0.15)",
                  }}
                />
              </button>
              <p style={{ fontWeight: 700, color: "#001c9a", fontSize: 14 }}>Thiệp đính kèm</p>
            </div>
          </div>
          <p style={{ fontSize: 12, color: "rgba(0,28,154,0.6)", marginBottom: 10 }}>
            *Thiệp sẽ gửi đến người yêu thương của bạn vào lần đầu tiên giao hàng
          </p>

          {includeGreetingCard && (
            <div style={{
              background: "#ccff33",
              borderRadius: 8,
              padding: "10px 14px",
              display: "flex",
              alignItems: "center",
              justifyContent: "space-between",
            }}>
              <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
                <div style={{
                  width: 28, height: 28, borderRadius: "50%",
                  background: "rgba(255,255,255,0.6)",
                  display: "flex", alignItems: "center", justifyContent: "center",
                  border: "1px solid rgba(0,28,154,0.1)",
                  fontSize: 13,
                }}>
                  <span style={{ fontSize: 12 }}>1</span>
                </div>
                <span style={{ fontSize: 13, fontWeight: 700, color: "#001c9a" }}>
                  {selectedCard?.title || "Thiệp Vinamilk Care mẫu 01"}
                </span>
              </div>
              <button
                type="button"
                onClick={() => setCardModalOpen(true)}
                style={{ fontSize: 12, fontWeight: 700, color: "#001c9a", background: "none", border: "none", cursor: "pointer", textTransform: "uppercase" }}
              >
                {greetingCardId ? "Đổi lời nhắn" : "Chọn lời nhắn"}
              </button>
            </div>
          )}
        </div>

        {/* RIGHT COLUMN */}
        <div style={{

          borderRadius: 16,
          border: "1px solid rgba(0,28,154,0.1)",
          padding: 0,
          overflow: "hidden",
        }}>
          {/* Tab header */}
          <div style={{ display: "flex", borderBottom: "1px solid rgba(0,28,154,0.1)" }}>
            <button
              type="button"
              onClick={() => setActiveTab("standard")}
              style={{
                flex: 1,
                padding: "14px 16px",
                fontSize: 14,
                fontWeight: 700,
                color: activeTab === "standard" ? "#001c9a" : "rgba(0,28,154,0.4)",
                background: activeTab === "standard" ? "#d3e1ff" : "rgba(0,28,154,0.02)",
                border: "none",
                borderBottom: activeTab === "standard" ? "2.5px solid #001c9a" : "2.5px solid transparent",
                cursor: "pointer",
                transition: "all 0.15s",
              }}
            >
              Gói Tiêu Chuẩn
            </button>
            <button
              type="button"
              onClick={() => setActiveTab("premium")}
              style={{
                flex: 1,
                padding: "14px 16px",
                fontSize: 14,
                fontWeight: 700,
                color: activeTab === "premium" ? "#001c9a" : "rgba(0,28,154,0.4)",
                background: activeTab === "premium" ? "#d3e1ff" : "rgba(0,28,154,0.02)",
                border: "none",
                borderBottom: activeTab === "premium" ? "2.5px solid #001c9a" : "2.5px solid transparent",
                cursor: "pointer",
                transition: "all 0.15s",
                position: "relative",
              }}
            >
              {activeTab !== "premium" && (
                <span style={{
                  display: "inline-block",
                  fontSize: 10,
                  fontWeight: 700,
                  color: "#001c9a",
                  background: "#ccff33",
                  borderRadius: 20,
                  padding: "2px 8px",
                  marginRight: 6,
                }}>
                  ✦ Sắp ra mắt
                </span>
              )}
              Gói Cao Cấp
            </button>
          </div>

          <div style={{ padding: "20px 24px" }}>
            {activeTab === "premium" && (
              <div style={{ marginBottom: 12 }}>
                <span style={{
                  display: "inline-block",
                  fontSize: 11,
                  fontWeight: 700,
                  color: "#001c9a",
                  background: "#ccff33",
                  borderRadius: 20,
                  padding: "3px 10px",
                }}>
                  ✦ Sắp ra mắt
                </span>
              </div>
            )}

            <h3 style={{ marginBottom: 12 }}>ƯU ĐÃI ĐỘC QUYỀN</h3>

            {/* Benefits list */}
            <ul style={{ listStyle: "none", padding: 0, margin: "0 0 20px 0", display: "flex", flexDirection: "column", gap: 10 }}>
              {benefits.map((b) => (
                <li key={b} style={{ display: "flex", alignItems: "flex-start", gap: 10, fontSize: 13, color: "#001c9a" }}>
                  <span style={{
                    width: 20, height: 20, borderRadius: "50%",
                    background: "#001c9a",
                    display: "flex", alignItems: "center", justifyContent: "center",
                    flexShrink: 0, marginTop: 1,
                  }}>
                    <Check size={11} color="#fff" strokeWidth={3} />
                  </span>
                  {b}
                </li>
              ))}
            </ul>

            {/* Delivery count selector */}
            <p style={{ fontSize: 13, fontWeight: 700, color: "#001c9a", marginBottom: 10 }}>Số lần giao hàng</p>
            <div style={{ display: "flex", gap: 8, marginBottom: 16 }}>
              {options.map((o) => (
                <button
                  key={o.id}
                  type="button"
                  onClick={() => {
                    calcKeyRef.current = "";
                    recalc({ deliveryCount: o.delivery_count });
                  }}
                  style={{
                    flex: 1,
                    padding: "8px 4px",
                    borderRadius: 20,
                    border: deliveryCount === o.delivery_count ? "2px solid #001c9a" : "2px solid rgba(0,28,154,0.2)",
                    background: deliveryCount === o.delivery_count ? "rgba(0,28,154,0.07)" : "#fff",
                    color: deliveryCount === o.delivery_count ? "#001c9a" : "rgba(0,28,154,0.5)",
                    fontSize: 13,
                    fontWeight: 700,
                    cursor: "pointer",
                    transition: "all 0.15s",
                  }}
                >
                  {o.delivery_count} lần
                </button>
              ))}
            </div>

            {/* Pricing breakdown */}
            <div style={{ borderTop: "1px dashed rgba(0,28,154,0.15)", paddingTop: 14, display: "flex", flexDirection: "column", gap: 10 }}>
              <div style={{ display: "flex", justifyContent: "space-between", fontSize: 13, color: "#001c9a" }}>
                <span>Chu kỳ</span>
                <span style={{ fontWeight: 600 }}>1 lần/tháng</span>
              </div>
              <div style={{ display: "flex", justifyContent: "space-between", fontSize: 13, color: "#001c9a", paddingBottom: 10, borderBottom: "1px dashed rgba(0,28,154,0.15)" }}>
                <span>Giá trị kiện hàng</span>
                <span style={{ fontWeight: 600 }}>{formatVnd(itemValue)}</span>
              </div>
              {pricing && (
                <>
                  <div style={{ display: "flex", justifyContent: "space-between", fontSize: 13, color: "#001c9a" }}>
                    <span>Tổng tiền gói</span>
                    <span style={{ fontWeight: 600 }}>{formatVnd(packageSubtotal)}</span>
                  </div>
                  <div style={{ display: "flex", justifyContent: "space-between", fontSize: 13, color: "#001c9a" }}>
                    <span>Ưu đãi gói (10%)</span>
                    <span style={{ fontWeight: 600, color: "#e53e3e" }}>-{formatVnd(discountAmount)}</span>
                  </div>
                  <h3 style={{
                    display: "flex",
                    justifyContent: "space-between",
                    fontSize: 16,
                    fontWeight: 900,
                    color: "#001c9a",
                    paddingTop: 8,
                    borderTop: "1px dashed rgba(0,28,154,0.15)",
                    marginTop: 2,
                  }}>
                    <span>Giá tạm tính</span>
                    <span>{formatVnd(totalAmount)}</span>
                  </h3>
                </>
              )}
            </div>

            <button
              type="button"
              disabled={loading || !pricing || !selectedVariant}
              onClick={onContinue}
              style={{
                marginTop: 20,
                width: "100%",
                background: "#001c9a",
                color: "#fff",
                padding: "14px",
                borderRadius: 40,
                fontWeight: 700,
                fontSize: 15,
                border: "none",
                cursor: loading || !pricing || !selectedVariant ? "not-allowed" : "pointer",
                opacity: loading || !pricing || !selectedVariant ? 0.5 : 1,
                letterSpacing: "0.01em",
              }}
            >
              Thanh toán gói →
            </button>
          </div>
        </div>
      </div>

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
    </div>
  );
}