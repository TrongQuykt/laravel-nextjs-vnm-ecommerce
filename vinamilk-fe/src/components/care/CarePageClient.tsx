"use client";

import React, { useEffect, useState } from "react";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import { careApi, catalogApi } from "@/lib/api";
import { CarePageSettings, CareProduct } from "@/types/care";
import { CareProductDetailSidebar } from "./CareProductDetailSidebar";
import { CarePackageStep } from "./CarePackageStep";
import { CareCheckout } from "./CareCheckout";
import { CareStepper } from "./CareStepper";
import { useCareCart, formatVnd, variantUnitPrice } from "@/context/CareCartContext";
import { Product } from "@/types";
import { Minus, Plus, Trash2, Star } from "lucide-react";

const STANDARD_BENEFITS = [
  "Sữa giao tận nhà, đều đặn mỗi tháng 1 lần.",
  "Gọi điện thăm hỏi và tư vấn sức khỏe mỗi 2 tuần",
  "1 tấm thiệp gửi gắm trọn lời yêu",
  "Là những người đầu tiên được thử sản phẩm mới miễn phí",
  "Vận chuyển miễn phí",
];

const PREMIUM_EXTRA = ["Bộ quà tặng cao cấp", "Kiểm tra sức khỏe định kỳ miễn phí"];

interface Props {
  initialSettings: CarePageSettings | null;
  initialProducts?: CareProduct[];
}

export function CarePageClient({ initialSettings, initialProducts = [] }: Props) {
  const {
    cartLine,
    sidebarSlug,
    view,
    openSidebar,
    closeSidebar,
    setView,
    clearCart,
    addToCareCart,
    updateCartQuantity,
    pricing,
  } = useCareCart();

  const [products, setProducts] = useState<CareProduct[]>(initialProducts);
  const [cardQty, setCardQty] = useState<Record<number, number>>({});

  const settings = initialSettings;
  const benefits = settings?.benefits?.length
    ? settings.benefits
    : [
      { title: "Sữa xịn, giá tốt nhất", description: "Mức giá tốt độc quyền cho gói Vinamilk Care." },
      { title: "Giao tận tay, đều đặn", description: "Giao đến người thân mỗi tháng 1 lần, miễn phí vận chuyển." },
      { title: "Chăm gọi điện, tư vấn", description: "Thăm hỏi và tư vấn sức khỏe mỗi 2 tuần." },
    ];

  const sidebarProduct = products.find((p) => p.slug === sidebarSlug) ?? null;

  useEffect(() => {
    if (initialProducts.length > 0) return;
    careApi
      .getProducts()
      .then((r) => setProducts(r.products || []))
      .catch(() => setProducts([]));
  }, [initialProducts.length]);

  const lineTotal = cartLine ? variantUnitPrice(cartLine.variant) * cartLine.quantity : 0;

  const quickAdd = async (cp: CareProduct, qty: number) => {
    const res = await catalogApi.getProduct(cp.slug);
    const p = (res.data ?? res.product) as Product;
    const variant = p.variants?.[0];
    if (!variant) return;
    addToCareCart(cp, variant, qty);
  };

  const handleQtyChange = (pId: number, q: number) => {
    setCardQty((prev) => ({ ...prev, [pId]: q }));
    if (cartLine?.careProductId === pId) {
      updateCartQuantity(q);
    }
  };

  const showStepper = view !== "main" || !!cartLine;

  return (
    <div className="bg-[#fefef0] min-h-screen">
      <Navbar />

      <section className="bg-[#001c9a] text-white pt-28 pb-16">
        <div className="container mx-auto px-6 max-w-6xl">
          <p className="text-2xl font-bold uppercase mb-8 opacity-90">Vinamilk Care</p>
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-10 items-start">
            <div />
            <div>
              <h1 className="text-2xl md:text-3xl font-serif italic leading-snug mb-6">
                &ldquo;{settings?.tagline || "Yêu thương là hiện diện mỗi ngày, bằng mọi cách."}&rdquo;
              </h1>
              <p className="text-sm md:text-base leading-relaxed opacity-95">
                {settings?.intro_text ||
                  "Với Vinamilk Care, bạn chỉ cần chọn gói định kỳ 3, 6 hoặc 9 tháng một lần. Vinamilk sẽ thay bạn mang sữa đến tận tay người nhận đều đặn mỗi tháng."}
              </p>
            </div>
          </div>
        </div>
      </section>

      <section className="bg-[#fefef0] py-14">
        <div className="container mx-auto px-6 max-w-6xl grid grid-cols-1 md:grid-cols-3 gap-10">
          {benefits.slice(0, 3).map((b, i) => (
            <div key={i} className="text-center md:text-left">
              <div className="aspect-[4/5] bg-[#001c9a]/5 rounded-lg mb-6 overflow-hidden">
                {settings?.hero_image_path && i === 0 ? (
                  <img src={settings.hero_image_path} alt="" className="w-full h-full object-cover opacity-80" />
                ) : (
                  <div className="w-full h-full bg-gradient-to-b from-[#d3e1ff]/40 to-[#001c9a]/10" />
                )}
              </div>
              <h3 className="font-black text-[#001c9a] text-lg mb-2">{b.title}</h3>
              <p className="text-sm text-[#001c9a]/70 leading-relaxed">{b.description}</p>
            </div>
          ))}
        </div>
      </section>

      <section
        className="py-16"
        style={{
          backgroundImage:
            "linear-gradient(#d3e1ff33 1px, transparent 1px), linear-gradient(90deg, #d3e1ff33 1px, transparent 1px)",
          backgroundSize: "24px 24px",
        }}
      >
        <div className="container mx-auto px-6 max-w-5xl">
          <h2 className="text-2xl md:text-3xl font-black text-[#001c9a] text-center mb-12">
            Tìm hiểu các gói của Vinamilk Care
          </h2>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            <PackageCard title="Gói Tiêu Chuẩn" pattern="blue" items={STANDARD_BENEFITS} />
            <PackageCard
              title="Gói Cao Cấp"
              pattern="green"
              items={[...STANDARD_BENEFITS, ...PREMIUM_EXTRA]}
              comingSoon
            />
          </div>
        </div>
      </section>

      <section className="pb-32 pt-8" id="chon-san-pham">
        <div className="container mx-auto px-6 max-w-6xl">
          {view === "main" && (
            <>
              <h2 className="text-3xl md:text-4xl font-black text-[#001c9a] text-center mb-2">Chọn sản phẩm</h2>
              <p className="text-center text-[#001c9a]/70 mb-10">
                Để tạo thành gói phù hợp với nhu cầu của người bạn thương
              </p>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                {products.map((p) => {
                  const qty = cartLine?.careProductId === p.id ? cartLine.quantity : (cardQty[p.id] ?? 1);
                  return (
                    <CareProductCard
                      key={p.id}
                      product={p}
                      qty={qty}
                      onQtyChange={(q) => handleQtyChange(p.id, q)}
                      onOpen={() => openSidebar(p.slug)}
                      onQuickAdd={() => quickAdd(p, qty)}
                    />
                  );
                })}
              </div>
              {products.length === 0 && (
                <p className="text-center text-[#001c9a]/50 py-12">
                  Chưa có sản phẩm Care. Vui lòng cấu hình trong admin.
                </p>
              )}
            </>
          )}
        </div>
      </section>

      {view !== "main" && (
        <div className="fixed inset-0 z-[9999] flex items-center justify-center bg-[#000000cc] p-4 md:p-6">
          <div className="bg-[#fffff1] w-full max-w-6xl max-h-full md:max-h-[90vh] rounded flex flex-col overflow-hidden relative shadow-2xl border border-[#001c9a]/10">
            {/* Close button */}
            <button 
              type="button" 
              onClick={() => setView("main")} 
              className="absolute top-4 right-4 text-[#001c9a] hover:bg-[#001c9a]/10 p-2 rounded-full z-10 transition-colors"
            >
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
            
            <div className="px-8 py-8 border-b border-[#001c9a]/10 shrink-0">
              <div className="max-w-2xl mx-auto">
                 <CareStepper current={view === "checkout" ? 3 : view === "package" ? 2 : 1} />
              </div>
            </div>

            <div className="flex-1 overflow-y-auto p-8 navy-scrollbar">
              {view === "package" && <CarePackageStep onContinue={() => setView("checkout")} />}
              {view === "checkout" && <CareCheckout onBack={() => setView("package")} />}
            </div>
          </div>
        </div>
      )}

      <Footer />

      {cartLine && view === "main" && (
        <div className="fixed bottom-6 left-1/2 -translate-x-1/2 z-[100] bg-[#d3e1ff] border border-[#001c9a]/10 rounded w-[95%] max-w-3xl">
          <div className="px-6 py-4 flex items-center justify-between gap-4">
            <div className="text-[#001c9a] text-sm md:text-base flex items-center">
              <div className="flex items-center gap-2 mr-6 text-[#001c9a]/80">
                <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19,15V11H5V15H19M19,7A2,2 0 0,1 21,9V15A2,2 0 0,1 19,17H5A2,2 0 0,1 3,15V9A2,2 0 0,1 5,7H19M12,9A2,2 0 0,0 10,11A2,2 0 0,0 12,13A2,2 0 0,0 14,11A2,2 0 0,0 12,9Z" /></svg>
                <span>Số sản phẩm: <span className="font-bold">{cartLine.quantity}</span></span>
              </div>
              <div className="flex items-center gap-2">
                <span>Giá tạm tính:</span>
                <span className="font-black text-lg">{formatVnd(pricing?.total_amount ?? lineTotal)}</span>
              </div>
            </div>
            <div className="flex items-center gap-3">
              <button
                type="button"
                onClick={clearCart}
                className="w-10 h-10 flex items-center justify-center border border-[#001c9a]/20 rounded text-[#001c9a] hover:bg-[#001c9a]/5 transition-colors"
              >
                <Trash2 size={18} />
              </button>
              <button
                type="button"
                onClick={() => setView("package")}
                className="bg-[#001c9a] text-white px-6 py-2.5 rounded font-bold text-sm whitespace-nowrap hover:bg-[#0213b0] transition-colors"
              >
                Đăng ký ngay
              </button>
            </div>
          </div>
        </div>
      )}

      <CareProductDetailSidebar careProduct={sidebarProduct} isOpen={!!sidebarSlug} onClose={closeSidebar} />
    </div>
  );
}

function PackageCard({
  title,
  items,
  pattern,
  comingSoon,
}: {
  title: string;
  items: string[];
  pattern: "blue" | "green";
  comingSoon?: boolean;
}) {
  const headerBg =
    pattern === "blue"
      ? "bg-[repeating-linear-gradient(90deg,#001c9a_0px,#001c9a_8px,transparent_8px,transparent_16px)] bg-[#d3e1ff]"
      : "bg-[repeating-linear-gradient(90deg,#4ade80_0px,#4ade80_6px,transparent_6px,transparent_14px)] bg-[#ecfdf5]";

  return (
    <div className="bg-white rounded-b-3xl overflow-hidden shadow-sm border border-[#001c9a]/10 relative pb-6">
      <div className={`h-16 ${headerBg}`} />
      <div className="px-8 pt-6">
        <div className="flex items-center gap-3 mb-2">
          <h3 className="text-xl font-black text-[#001c9a]">{title}</h3>
          {comingSoon && (
            <span className="text-[10px] font-bold bg-[#d3e1ff] text-[#001c9a] px-2 py-1 rounded-full flex items-center gap-1">
              <Star size={10} fill="currentColor" /> Sắp ra mắt
            </span>
          )}
        </div>
        <p className="text-sm font-bold text-[#001c9a] mb-4">Người bạn thương sẽ được:</p>
        <ul className="space-y-3">
          {items.map((item) => (
            <li key={item} className="flex gap-3 text-sm text-[#001c9a]/85">
              <span className="w-5 h-5 rounded-full border-2 border-[#001c9a] flex items-center justify-center shrink-0 mt-0.5">
                <span className="w-2 h-2 rounded-full bg-[#001c9a]" />
              </span>
              {item}
            </li>
          ))}
        </ul>
      </div>
      <div
        className="absolute bottom-0 left-0 right-0 h-4"
        style={{
          background: "radial-gradient(circle at 10px 0, transparent 10px, white 10px)",
          backgroundSize: "20px 20px",
          backgroundPosition: "0 -10px",
        }}
      />
    </div>
  );
}

function CareProductCard({
  product,
  qty,
  onQtyChange,
  onOpen,
  onQuickAdd,
}: {
  product: CareProduct;
  qty: number;
  onQtyChange: (q: number) => void;
  onOpen: () => void;
  onQuickAdd: () => Promise<void>;
}) {
  const [fullProduct, setFullProduct] = useState<Product | null>(null);
  const [isAdding, setIsAdding] = useState(false);

  useEffect(() => {
    catalogApi.getProduct(product.slug).then(res => {
      setFullProduct((res.data ?? res.product) as Product);
    }).catch(() => { });
  }, [product.slug]);

  const variant = fullProduct?.variants?.[0];
  const discount = variant?.discount_percentage || product.discount_percent;
  const variantName = variant ? [variant.volume, variant.packaging_type].filter(Boolean).join(', ') : 'Gói định kỳ';

  const handleAdd = async () => {
    setIsAdding(true);
    await onQuickAdd();
    setIsAdding(false);
  };

  return (
    <div className="bg-transparent flex flex-col h-full">
      <button type="button" onClick={onOpen} className="w-full text-left group">
        <div className="aspect-square flex items-center justify-center mb-4 bg-transparent p-2">
          {product.image && <img src={product.image} alt={product.name} className="max-h-full object-contain" />}
        </div>
      </button>
      <p className="text-[13px] text-[#001c9a] mb-1">{product.category_name}</p>
      <button type="button" onClick={onOpen} className="text-left w-full">
        <h3 className="text-[#001c9a] text-[22px] mb-2 hover:underline leading-tight">{product.name}</h3>
      </button>
      {product.short_description && (
        <div
          className="text-[13px] text-[#001c9a] line-clamp-2 mb-3 leading-relaxed"
          dangerouslySetInnerHTML={{ __html: product.short_description }}
        />
      )}

      <div className="mt-auto pt-3">
        <div className="flex items-center justify-between gap-2 mb-4 bg-[#f4f7ff] px-3 py-2.5 rounded">
          <div className="text-[12px] font-medium text-[#001c9a] line-clamp-1 flex-1">
            {variantName}
          </div>
          <div className="flex items-center gap-2">
            {discount > 0 && <span className="text-[12px] font-bold text-[#001c9a]">-{discount}%</span>}
            <span className="text-sm font-black text-[#001c9a]">{formatVnd(variant ? variantUnitPrice(variant) : product.care_price)}</span>
          </div>
        </div>

        <div className="flex flex-col gap-2">
          <div className="flex items-center justify-between border border-[#001c9a] rounded bg-transparent text-[#001c9a] px-3 py-1.5">
            <button type="button" onClick={() => onQtyChange(Math.max(1, qty - 1))} className="p-1">
              <Minus size={16} strokeWidth={1.5} />
            </button>
            <span className="text-sm font-bold">{qty}</span>
            <button type="button" onClick={() => onQtyChange(Math.min(99, qty + 1))} className="p-1">
              <Plus size={16} strokeWidth={1.5} />
            </button>
          </div>
          <button
            type="button"
            onClick={handleAdd}
            disabled={isAdding}
            className="w-full bg-[#eef2ff] text-[#001c9a] py-2.5 rounded text-sm font-bold hover:bg-[#d3e1ff] transition-colors flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
          >
            {isAdding ? <div className="w-4 h-4 border-2 border-[#001c9a] border-t-transparent rounded-full animate-spin" /> : 'Thêm vào gói'}
          </button>
        </div>
      </div>
    </div>
  );
}
