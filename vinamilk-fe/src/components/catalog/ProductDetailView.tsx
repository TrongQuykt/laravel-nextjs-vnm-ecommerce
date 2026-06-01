"use client";

import { Product, ProductVariant, ProductImage } from "@/types";

import { useState, useEffect, useRef } from "react";
import { ProductGallery } from "./ProductGallery";
import { ProductLineNavigator } from "./ProductLineNavigator";
import VariantSelector from "./VariantSelector";
import { NutritionFacts } from "./NutritionFacts";
import { Plus, X, ShieldCheck, Truck, RotateCcw } from "lucide-react";
import { motion, AnimatePresence } from "framer-motion";
import { getImageUrl } from "@/lib/api";
import { QuickAddToCartBar } from "./QuickAddToCartBar";
import { useCart } from "@/context/CartContext";

export default function ProductDetailView({
  product,
  lineProducts,
}: {
  product: Product;
  lineProducts: Product[];
}) {
  const { addToCart, isLoading } = useCart();
  const [selectedVariant, setSelectedVariant] = useState<ProductVariant>(
    product.variants?.[0] || { id: 0, price: 0, base_price: 0, discount_percentage: 0, sku: '', name: '', stock_quantity: 0, reserved_quantity: 0, available_quantity: 0, stock_status: 'in_stock', is_in_stock: true, is_low_stock: false, is_out_of_stock: false, units_per_pack: 1, is_active: false, flavor: null, flavor_slug: null, volume: null, volume_slug: null, packaging_type: null, packaging_type_slug: null }
  );
  const [quantity, setQuantity] = useState(1);
  const [isNutritionOpen, setIsNutritionOpen] = useState(false);
  const [isUsageOpen, setIsUsageOpen] = useState(false);

  const mainButtonRef = useRef<HTMLDivElement>(null);
  const certifiedRef = useRef<HTMLElement>(null);
  const bottomRef = useRef<HTMLDivElement>(null);

  const handleAddToCart = (e: React.MouseEvent<HTMLButtonElement>) => {
    addToCart(product, selectedVariant, quantity, e.currentTarget);
  };

  useEffect(() => {
    if (product.variants?.length > 0) {
      setSelectedVariant(product.variants[0]);
    }
  }, [product.variants]);

  const comparisonHeaders = Array.isArray(product.comparison_table_headers)
    ? product.comparison_table_headers
    : [];

  // Calculate gallery based on selected variant + product images
  const variantImages = Array.isArray(selectedVariant.images) ? selectedVariant.images : [];
  const productImages = Array.isArray(product.images) ? product.images : [];

  const effectiveMainImage = variantImages[0] || selectedVariant.main_image || product.main_image;

  // Combine variant images and product images, filtering out duplicates by path
  const combinedImages: ProductImage[] = [
    ...variantImages.map((path, idx) => ({ id: 2000 + idx, path, type: 'detail' as const })),
    ...(product.main_image ? [{ id: -1, path: product.main_image, type: 'main' as const }] : []),
    ...productImages
  ];

  // Remove duplicates while preserving order
  const seenPaths = new Set<string>();
  const effectiveGallery = combinedImages.filter(img => {
    if (!img.path || seenPaths.has(img.path)) return false;
    seenPaths.add(img.path);
    return true;
  });

  return (
    <div className="bg-cream min-h-screen">
      {/* Nutrition Drawer */}
      <AnimatePresence>
        {isNutritionOpen && (
          <>
            <motion.div
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              onClick={() => setIsNutritionOpen(false)}
              className="fixed inset-0 bg-black/60 z-[110]"
            />
            <motion.div
              initial={{ x: "100%" }}
              animate={{ x: 0 }}
              exit={{ x: "100%" }}
              transition={{ type: "spring", damping: 25, stiffness: 200 }}
              className="fixed top-0 right-0 h-full w-full max-w-[500px] bg-cream z-[120] shadow-2xl p-10 overflow-y-auto custom-scrollbar-navy"
            >
              <div className="flex items-center justify-between mb-0 pb-3 border-b-2 border-v-navy/30">
                <h2 className="text-2xl font-sans font-black text-v-navy tracking-tight">Thành phần & Dinh dưỡng</h2>
                <button onClick={() => setIsNutritionOpen(false)} className="p-2 hover:bg-gray-100 rounded-full transition-colors text-v-navy">
                  <X size={20} />
                </button>
              </div>

              <div className="pt-4 space-y-6">
                <div>
                  <div className="text-v-navy leading-relaxed text-[13.5px] font-medium font-sans" dangerouslySetInnerHTML={{ __html: product.ingredients || 'Thông tin đang được cập nhật...' }} />
                </div>

                <div>
                  <NutritionFacts facts={product.nutrition_facts} />
                </div>

                <div className="p-4 bg-white rounded-lg border border-v-navy/20 text-[11px] text-v-navy leading-relaxed font-medium">
                  Các vitamin và khoáng chất có sẵn trong sữa tươi. <br />
                  <span className="opacity-80 font-normal italic">*Hàm lượng các chất không thấp hơn 80% giá trị trên nhãn.</span>
                </div>
              </div>
            </motion.div>
          </>
        )}
      </AnimatePresence>

      {/* Usage Drawer */}
      <AnimatePresence>
        {isUsageOpen && (
          <>
            <motion.div
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              onClick={() => setIsUsageOpen(false)}
              className="fixed inset-0 bg-black/60 z-[110]"
            />
            <motion.div
              initial={{ x: "100%" }}
              animate={{ x: 0 }}
              exit={{ x: "100%" }}
              transition={{ type: "spring", damping: 25, stiffness: 200 }}
              className="fixed top-0 right-0 h-full w-full max-w-[500px] bg-cream z-[120] shadow-2xl p-10 overflow-y-auto custom-scrollbar-navy"
            >
              <div className="flex items-center justify-between mb-0 pb-3 border-b-2 border-v-navy/30">
                <h2 className="text-2xl font-sans font-black text-v-navy tracking-tight">Hướng dẫn sử dụng</h2>
                <button onClick={() => setIsUsageOpen(false)} className="p-2 hover:bg-gray-100 rounded-full transition-colors text-v-navy">
                  <X size={20} />
                </button>
              </div>

              <div className="pt-4 space-y-6">
                {product.usage_instructions && (
                  <div>
                    <h3 className="text-[10px] font-black uppercase tracking-widest text-[#0213b0] mb-2 py-1">Cách dùng</h3>
                    <div className="text-v-navy/80 leading-relaxed text-[13.5px] font-medium font-sans" dangerouslySetInnerHTML={{ __html: product.usage_instructions }} />
                  </div>
                )}

                {product.storage_instructions && (
                  <div>
                    <h3 className="text-[10px] font-black uppercase tracking-widest text-[#0213b0] mb-2 py-1">Hướng dẫn bảo quản</h3>
                    <div className="text-v-navy/80 leading-relaxed text-[13.5px] font-medium font-sans" dangerouslySetInnerHTML={{ __html: product.storage_instructions }} />
                  </div>
                )}
              </div>
            </motion.div>
          </>
        )}
      </AnimatePresence>

      <div className="container mx-auto px-6 max-w-6xl pt-30 pb-8">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-start">
          {/* Main Display: Gallery (Sticky on Desktop) */}
          <div className="flex justify-center w-full lg:sticky lg:top-26 h-fit lg:max-h-[calc(100vh-140px)]">
            <ProductGallery mainImage={effectiveMainImage} images={effectiveGallery} />
          </div>

          {/* Product Actions Column */}
          <div className="flex flex-col gap-10">
            <div>
              <div className="flex items-center gap-2 mb-8">
                <span className="text-[10px] font-black uppercase tracking-[0.3em] text-v-navy">
                  {product.brand?.name || 'VINAMILK'}
                </span>
                <span className="h-px w-4 bg-v-navy/20" />
                <span className="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                  {product.category?.name}
                </span>
                <span className="h-px w-4 bg-v-navy/20" />
                <span className="text-[10px] font-bold uppercase tracking-widest text-v-navy/60">
                  {product.product_line?.name}
                </span>
              </div>
              <h1 className="text-4xl md:text-3xl font-sans font-semibold text-blue-700 mb-6 leading-[1.05] tracking-[-0.5px]">
                {product.name}
              </h1>
              <div
                className="text-v-navy text-sm md:text-base prose prose-sm prose-v-navy max-w-none 
                prose-ul:list-disc prose-ul:pl-5 prose-ul:my-4 prose-li:my-1 prose-p:my-2
                [&_ul]:list-disc [&_ul]:pl-5 [&_p]:mb-2"
                dangerouslySetInnerHTML={{ __html: product.short_description || '' }}
              />
            </div>

            <ProductLineNavigator currentProduct={product} lineProducts={lineProducts} />

            <VariantSelector product={product} onVariantChange={setSelectedVariant} />

            {/* Stock Status Display */}
            {/* <div className="flex items-center gap-2">
              {selectedVariant.available_quantity <= 0 ? (
                <div className="flex items-center gap-2 px-4 py-2 bg-red-50 border border-red-200 rounded-lg">
                  <div className="w-2 h-2 rounded-full bg-red-500" />
                  <span className="text-sm font-semibold text-red-700">Hết hàng</span>
                </div>
              ) : selectedVariant.available_quantity <= 10 ? (
                <div className="flex items-center gap-2 px-4 py-2 bg-orange-50 border border-orange-200 rounded-lg">
                  <div className="w-2 h-2 rounded-full bg-orange-500" />
                  <span className="text-sm font-semibold text-orange-700">
                    Sắp hết hàng - Còn {selectedVariant.available_quantity}
                  </span>
                </div>
              ) : (
                <div className="flex items-center gap-2 px-4 py-2 bg-green-50 border border-green-200 rounded-lg">
                  <div className="w-2 h-2 rounded-full bg-green-500" />
                  <span className="text-sm font-semibold text-green-700">Còn hàng</span>
                </div>
              )}
            </div> */}

            <div className="flex flex-col gap-6">
              <div ref={mainButtonRef} className="flex gap-3 items-stretch">
                <div className="flex items-center bg-transparent border border-[#0213b0] rounded-lg px-3 py-2 gap-3">
                  <button
                    onClick={() => setQuantity(Math.max(1, quantity - 1))}
                    className="text-[#0213b0]/60 hover:text-[#1e3a8a] text-xl leading-none font-bold transition-colors"
                  >
                    −
                  </button>

                  <span className="w-6 text-center text-sm font-bold text-v-navy">
                    {quantity}
                  </span>

                  <button
                    onClick={() => setQuantity(quantity + 1)}
                    className="text-[#0213b0]/60 hover:text-[#1e3a8a] text-xl leading-none font-bold transition-colors"
                  >
                    +
                  </button>
                </div>
                <button
                  onClick={handleAddToCart}
                  disabled={isLoading || selectedVariant.available_quantity <= 0}
                  className="flex-1 py-3 bg-[#0213b0] text-white rounded-lg text-sm font-semibold tracking-wide hover:bg-[#172d6e] transition-colors flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed disabled:bg-gray-400"
                >
                  {isLoading ? (
                    <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin" />
                  ) : selectedVariant.available_quantity <= 0 ? (
                    <span>Hết hàng</span>
                  ) : (
                    <>
                      {(selectedVariant.price * quantity).toLocaleString("vi-VN", { minimumFractionDigits: 3, maximumFractionDigits: 3 })}đ
                      <span className="text-white/60">|</span>
                      Thêm vào giỏ
                    </>
                  )}
                </button>
              </div>

              <div className="flex flex-col gap-3">
                {/* Thành phần & Dinh dưỡng Bar */}
                <button
                  onClick={() => setIsNutritionOpen(true)}
                  className="group flex items-center justify-between p-5 bg-v-navy/5 rounded-2xl border border-v-navy/10 hover:bg-v-navy/[0.08] transition-all text-left"
                >
                  <div className="flex flex-col gap-1 overflow-hidden">
                    <span className="text-[12px] font-black uppercase tracking-widest text-v-navy">
                      Thành phần & Dinh dưỡng
                    </span>
                    <span className="text-xs text-v-navy/60 italic truncate max-w-xs md:max-w-sm">
                      {product.ingredients
                        ? product.ingredients.replace(/<[^>]*>/g, '').substring(0, 100) + '...'
                        : 'Xem chi tiết...'}
                    </span>
                  </div>

                  {/* Nút + vuông bo góc nhẹ, bỏ hover riêng */}
                  <div className="w-9 h-9 rounded-lg bg-white text-v-navy shadow-sm flex items-center justify-center flex-shrink-0">
                    <Plus size={16} />
                  </div>
                </button>

                {/* Hướng dẫn sử dụng Bar */}
                {product.usage_instructions && (
                  <button
                    onClick={() => setIsUsageOpen(true)}
                    className="group flex items-center justify-between p-5 bg-v-navy/5 rounded-2xl border border-v-navy/10 hover:bg-v-navy/[0.08] transition-all text-left"
                  >
                    <div className="flex flex-col gap-1 overflow-hidden">
                      <span className="text-[12px] font-black uppercase tracking-widest text-v-navy">
                        Hướng dẫn sử dụng
                      </span>
                      <span className="text-xs text-v-navy/60 italic truncate max-w-xs md:max-w-sm">
                        {product.usage_instructions.replace(/<[^>]*>/g, '').substring(0, 80) + '...'}
                      </span>
                    </div>

                    {/* Nút + vuông bo góc nhẹ */}
                    <div className="w-9 h-9 rounded-lg bg-white text-v-navy shadow-sm flex items-center justify-center flex-shrink-0">
                      <Plus size={16} />
                    </div>
                  </button>
                )}
              </div>
            </div>
          </div>
        </div>

        {/* --- SECTION 0: CERTIFICATES (CERTIFIED) --- */}
        {product.certificates && product.certificates.length > 0 && (
          <section ref={certifiedRef} className="mt-20 bg-[#d3e1ff5c] rounded-3xl py-14 px-12">
            <div className={`grid justify-items-center items-start gap-y-6 gap-x-8
      ${product.certificates.length <= 3
                ? `grid-cols-${product.certificates.length}`
                : 'grid-cols-2 md:grid-cols-3 lg:grid-cols-5'
              }`}
            >
              {product.certificates.map((certified) => (
                <div key={certified.id} className="flex flex-col items-center text-center gap-5">
                  <img
                    src={getImageUrl(certified.icon) || ""}
                    alt={certified.name}
                    className="w-38 h-28 object-contain"
                  />
                  <span className="text-[13px] font-bold text-v-navy/100 leading-snug max-w-[300px]">
                    {certified.name}
                  </span>
                </div>
              ))}
            </div>
          </section>
        )}

        {/* --- SECTION 1: DYNAMIC FEATURES (TOP) --- */}
        {product.features && product.features.length > 0 && (
          <section className="mt-24">
            {product.features_title && (
              <h2 className="text-center text-4xl md:text-5xl font-serif font-normal text-v-navy mb-8 pt-8">{product.features_title}</h2>
            )}

            <div className="max-w-5xl mx-auto">
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">
                {/* Left Column: Sticky Image - smaller & centered in its col */}
                <div className="lg:sticky lg:top-32 h-fit flex justify-center lg:justify-end">
                  {product.features_main_image && (
                    <div className="relative group max-w-[320px] w-full">
                      <img
                        src={getImageUrl(product.features_main_image) || ''}
                        alt="Features"
                        className="relative w-full object-contain rounded-2xl"
                      />
                    </div>
                  )}
                </div>

                {/* Right Column: Scrolling Content - tighter & aligned to its col start */}
                <div className="space-y-3 py-2 lg:max-w-md">
                  {product.features.map((feature, index) => (
                    <div key={index} className="group">
                      <h3 className="text-md font-sans font-semibold text-v-navy mb-1">{feature.title}</h3>
                      <div className="text-[14px] text-v-navy/70 leading-relaxed prose max-w-none" dangerouslySetInnerHTML={{ __html: feature.content }} />
                      <div className="h-px w-full bg-v-navy/20 mt-3" />
                    </div>
                  ))}
                </div>
              </div>
            </div>

            {/* SPECIAL HIGHLIGHTS GRID - centered */}
            {product.special_highlights && product.special_highlights.length > 0 && (
              <div className="mt-10 py-8 border-t border-v-navy/20">
                <div className="flex flex-wrap justify-center gap-x-18 gap-y-8 items-start text-center">
                  {product.special_highlights.map((highlight) => (
                    <div key={highlight.id} className="flex flex-col items-center justify-center w-40">
                      <div className="w-16 h-16 rounded-full bg-transparent flex items-center justify-center mb-3">
                        <img
                          src={getImageUrl(highlight.icon) || ""}
                          alt={highlight.name}
                          className="w-16 h-16 object-contain"
                        />
                      </div>
                      <span className="text-xs font-semibold text-v-navy/80 tracking-tight leading-snug">
                        {highlight.name}
                      </span>
                    </div>
                  ))}
                </div>
              </div>
            )}
          </section>
        )}

      </div>

      {/* --- SECTION 2: DESCRIPTION - FULL BLEED --- */}
      {(product.description || product.description_image || (product.description_images as string[] | undefined)?.length) && (
        <section className="mt-16">
          {product.description_title && (
            <div className="container mx-auto px-6 max-w-6xl mb-6">
              <h2 className="text-center text-2xl font-serif font-normal text-v-navy">{product.description_title}</h2>
            </div>
          )}

          {/* Multiple images stacked - full bleed */}
          {Array.isArray(product.description_images) && (product.description_images as string[]).length > 0 ? (
            <div className="w-full flex flex-col">
              {(product.description_images as string[]).map((img: string, i: number) => (
                <img key={i} src={getImageUrl(img) || ''} alt={`desc-${i}`} className="w-full h-auto block" />
              ))}
            </div>
          ) : product.description_image ? (
            <div className="w-full overflow-hidden">
              <img
                src={getImageUrl(product.description_image) || ''}
                alt={product.description_title || 'Mô tả sản phẩm'}
                className="w-full h-auto"
              />
            </div>
          ) : null}

          {product.description && (
            <div className="container mx-auto px-6 max-w-4xl mt-8">
              <div className="prose prose-sm max-w-none text-v-navy/80 leading-relaxed text-center" dangerouslySetInnerHTML={{ __html: product.description }} />
            </div>
          )}
        </section>
      )}

      <div className="container mx-auto px-6 max-w-6xl pb-16">

        {/* --- SECTION 3: DYNAMIC COMPARISON TABLE (BOTTOM) --- */}
        {comparisonHeaders.length > 0 && (
          <div className="mt-16 flex flex-col items-center">
            {product.comparison_title && (
              <h2 className="text-center text-4xl md:text-5xl font-serif font-normal text-v-navy mb-8">{product.comparison_title}</h2>
            )}

            <div className="w-full overflow-x-auto pb-6">
              <table className="w-full min-w-[600px] border-separate border-spacing-y-1">
                <thead>
                  <tr className="text-[9px] font-black uppercase tracking-widest text-v-navy/40">
                    <th className="px-4 py-2 text-left font-light"></th>
                    {comparisonHeaders.map((header, headIdx) => (
                      <th
                        key={headIdx}
                        className={`px-4 py-3 text-center rounded-t-xl italic text-xs ${headIdx === 0 ? 'bg-v-navy/5 text-v-navy' : ''}`}
                      >
                        {header.name}
                      </th>
                    ))}
                  </tr>
                </thead>
                <tbody className="text-v-navy/80 text-xs">
                  {Array.isArray(product.comparison_table_rows) && product.comparison_table_rows.map((row, idx) => (
                    <tr key={idx} className="group">
                      <td className="px-4 py-3 border-b border-v-navy/20 font-medium text-xs">{row.attribute}</td>
                      {comparisonHeaders.map((header, hIdx) => {
                        const val = (row as any)[header.key];
                        return (
                          <td
                            key={hIdx}
                            className={`px-4 py-3 border-b border-v-navy/20 text-center text-[13.5px] ${hIdx === 0 ? 'bg-v-navy/5 font-bold text-v-navy' : 'text-v-navy/100'}`}
                          >
                            {val === 'dot' ? '●' : val === 'dash' ? '—' : val === 'none' ? '' : (val || '')}
                          </td>
                        );
                      })}
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}

        <div className="mt-16 flex flex-wrap justify-center gap-16 py-12">
          <TrustItem icon={<ShieldCheck size={28} />} title="An toàn" desc="Thanh toán 100% bảo mật" />
          <TrustItem icon={<Truck size={28} />} title="Giao nhanh" desc="Toàn quốc 2-4 ngày" />
          <TrustItem icon={<RotateCcw size={28} />} title="Đổi trả" desc="Trong vòng 7 ngày" />
        </div>

        {/* Cột mốc để ẩn thanh Quick Add to Cart */}
        <div ref={bottomRef} className="h-px w-full" />
      </div>

      <QuickAddToCartBar
        product={product}
        selectedVariant={selectedVariant}
        quantity={quantity}
        setQuantity={setQuantity}
        mainButtonRef={mainButtonRef}
        certifiedRef={certifiedRef}
        bottomRef={bottomRef}
      />
    </div>
  );
}

function TrustItem({ icon, title, desc }: { icon: React.ReactNode; title: string, desc: string }) {
  return (
    <div className="flex flex-col items-center text-center gap-4 group max-w-[200px]">
      <div className="w-16 h-16 rounded-3xl bg-white flex items-center justify-center text-v-navy/30 group-hover:bg-v-navy group-hover:text-white transition-all shadow-sm group-hover:shadow-xl">
        {icon}
      </div>
      <div>
        <h4 className="text-xs font-black uppercase tracking-widest text-v-navy mb-2">{title}</h4>
        <p className="text-[11px] text-v-navy/50 leading-relaxed">{desc}</p>
      </div>
    </div>
  );
}
