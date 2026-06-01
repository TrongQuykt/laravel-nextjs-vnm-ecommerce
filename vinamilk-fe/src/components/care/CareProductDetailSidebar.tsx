"use client";

import React, { useEffect, useState, useMemo } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { X, Minus, Plus, ChevronDown, ChevronUp } from "lucide-react";
import { catalogApi } from "@/lib/api";
import { Product, ProductVariant, ProductImage } from "@/types";
import { ProductGallery } from "@/components/catalog/ProductGallery";
import VariantSelector from "@/components/catalog/VariantSelector";
import { NutritionFacts } from "@/components/catalog/NutritionFacts";
import { useCareCart, formatVnd, variantUnitPrice, variantBasePrice } from "@/context/CareCartContext";
import { CareProduct } from "@/types/care";

interface Props {
  careProduct: CareProduct | null;
  isOpen: boolean;
  onClose: () => void;
}

export function CareProductDetailSidebar({ careProduct, isOpen, onClose }: Props) {
  const { addToCareCart } = useCareCart();
  const [product, setProduct] = useState<Product | null>(null);
  const [selectedVariant, setSelectedVariant] = useState<ProductVariant | null>(null);
  const [quantity, setQuantity] = useState(1);
  const [nutritionOpen, setNutritionOpen] = useState(false);
  const [usageOpen, setUsageOpen] = useState(false);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (!isOpen || !careProduct?.slug) {
      setProduct(null);
      setSelectedVariant(null);
      return;
    }
    setLoading(true);
    setNutritionOpen(false);
    setUsageOpen(false);
    catalogApi
      .getProduct(careProduct.slug)
      .then((res) => {
        const p = (res.data ?? res.product) as Product;
        if (!p) return;
        setProduct(p);
        setSelectedVariant(p.variants?.[0] ?? null);
        setQuantity(1);
      })
      .catch(() => setProduct(null))
      .finally(() => setLoading(false));
  }, [isOpen, careProduct?.slug]);

  const galleryImages = useMemo((): ProductImage[] => {
    if (!product || !selectedVariant) return [];
    const variantImages = Array.isArray(selectedVariant.images) ? selectedVariant.images : [];
    const productImages = Array.isArray(product.images) ? product.images : [];
    const combined: ProductImage[] = [
      ...variantImages.map((path, idx) => ({ id: 2000 + idx, path, type: "detail" as const })),
      ...(product.main_image ? [{ id: -1, path: product.main_image, type: "main" as const }] : []),
      ...productImages,
    ];
    const seen = new Set<string>();
    return combined.filter((img) => {
      if (!img.path || seen.has(img.path)) return false;
      seen.add(img.path);
      return true;
    });
  }, [product, selectedVariant]);

  const mainImage = selectedVariant?.main_image || product?.main_image || careProduct?.image || null;
  const unit = selectedVariant ? variantUnitPrice(selectedVariant) : 0;
  const base = selectedVariant ? variantBasePrice(selectedVariant) : 0;
  const discount = selectedVariant?.discount_percentage || 0;

  const handleAdd = () => {
    if (!careProduct || !selectedVariant) return;
    addToCareCart(careProduct, selectedVariant, quantity);
    onClose();
  };

  return (
    <AnimatePresence>
      {isOpen && (
        <>
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 bg-black/50 z-[150]"
            onClick={onClose}
          />
          <motion.aside
            initial={{ x: "100%" }}
            animate={{ x: 0 }}
            exit={{ x: "100%" }}
            transition={{ type: "spring", damping: 28, stiffness: 260 }}
            className="fixed top-0 right-0 h-full w-full max-w-[520px] bg-[#fefef0] z-[160] shadow-2xl flex flex-col overflow-hidden"
          >
            <motion.div className="flex items-center justify-between px-6 py-4 border-b border-[#001c9a]/10 shrink-0">
              <div>
                <p className="text-[10px] font-bold text-[#001c9a]/40 uppercase tracking-widest">
                  Vinamilk Care / Chi tiết sản phẩm
                </p>
                <h2 className="text-lg font-black text-[#001c9a]">Chi tiết sản phẩm</h2>
              </div>
              <button type="button" onClick={onClose} className="p-2 rounded-full hover:bg-[#001c9a]/5 text-[#001c9a]">
                <X size={22} />
              </button>
            </motion.div>

            <div className="flex-1 overflow-y-auto px-6 py-4 navy-scrollbar">
              {loading ? (
                <div className="space-y-4 animate-pulse">
                  <div className="aspect-square bg-[#001c9a]/5 rounded-2xl" />
                  <div className="h-6 bg-[#001c9a]/5 rounded w-2/3" />
                </div>
              ) : !product ? (
                <p className="text-center text-[#001c9a]/60 py-12">Không tải được thông tin sản phẩm.</p>
              ) : (
                <>
                  <ProductGallery mainImage={mainImage} images={galleryImages} hideThumbnails={true} />
                  <div className="mt-6">
                    <p className="text-[11px] font-bold text-[#001c9a]/50 uppercase tracking-wide mb-1">
                      {product.category?.name || careProduct?.category_name}
                    </p>
                    <h3 className="text-2xl font-black text-[#001c9a] leading-tight mb-3">{product.name}</h3>
                    {product.short_description && (
                      <div
                        className="text-sm text-[#001c9a]/75 leading-relaxed mb-4 prose prose-sm max-w-none"
                        dangerouslySetInnerHTML={{ __html: product.short_description }}
                      />
                    )}
                    {product.description && (
                      <div
                        className="text-sm text-[#001c9a]/70 leading-relaxed mb-4 prose prose-sm max-w-none"
                        dangerouslySetInnerHTML={{ __html: product.description }}
                      />
                    )}
                  </div>
                  <VariantSelector product={product} onVariantChange={setSelectedVariant} />
                  {selectedVariant && (
                    <div className="mt-4 flex items-baseline gap-2">
                      {discount > 0 && <span className="text-sm font-bold text-[#001c9a]">-{discount}%</span>}
                      {base > unit && (
                        <span className="text-sm line-through text-[#001c9a]/40">{formatVnd(base)}</span>
                      )}
                      <span className="text-xl font-black text-[#001c9a]">{formatVnd(unit)}</span>
                    </div>
                  )}
                  {(product.ingredients || product.nutrition_facts?.length) && (
                    <div className="mt-4">
                      <button
                        type="button"
                        onClick={() => setNutritionOpen(true)}
                        className="group flex items-center justify-between w-full p-4 bg-[#001c9a]/5 rounded-2xl border border-[#001c9a]/10 hover:bg-[#001c9a]/10 transition-all text-left"
                      >
                        <div className="flex flex-col gap-1 overflow-hidden">
                          <span className="text-[10px] font-black uppercase tracking-widest text-[#001c9a]">
                            Thành phần & Dinh dưỡng
                          </span>
                          <span className="text-xs text-[#001c9a]/60 italic truncate max-w-[200px]">
                            {product.ingredients
                              ? product.ingredients.replace(/<[^>]*>/g, '').substring(0, 100) + '...'
                              : 'Xem chi tiết...'}
                          </span>
                        </div>
                        <div className="w-8 h-8 rounded-lg bg-white text-[#001c9a] shadow-sm flex items-center justify-center flex-shrink-0">
                          <Plus size={16} />
                        </div>
                      </button>
                    </div>
                  )}
                  {product.usage_instructions && (
                    <div className="mt-3">
                      <button
                        type="button"
                        onClick={() => setUsageOpen(true)}
                        className="group flex items-center justify-between w-full p-4 bg-[#001c9a]/5 rounded-2xl border border-[#001c9a]/10 hover:bg-[#001c9a]/10 transition-all text-left"
                      >
                        <div className="flex flex-col gap-1 overflow-hidden">
                          <span className="text-[10px] font-black uppercase tracking-widest text-[#001c9a]">
                            Hướng dẫn sử dụng
                          </span>
                          <span className="text-xs text-[#001c9a]/60 italic truncate max-w-[200px]">
                            {product.usage_instructions.replace(/<[^>]*>/g, '').substring(0, 80) + '...'}
                          </span>
                        </div>
                        <div className="w-8 h-8 rounded-lg bg-white text-[#001c9a] shadow-sm flex items-center justify-center flex-shrink-0">
                          <Plus size={16} />
                        </div>
                      </button>
                    </div>
                  )}
                </>
              )}
            </div>

            <div className="shrink-0 px-6 py-4 bg-[#fefef0] border-t border-[#001c9a]/10">
              <div className="flex items-center gap-3">
                <div className="flex items-center border border-[#001c9a]/25 rounded-lg bg-white">
                  <button type="button" onClick={() => setQuantity((q) => Math.max(1, q - 1))} className="px-3 py-2">
                    <Minus size={16} />
                  </button>
                  <span className="w-8 text-center font-bold text-[#001c9a]">{quantity}</span>
                  <button type="button" onClick={() => setQuantity((q) => Math.min(99, q + 1))} className="px-3 py-2">
                    <Plus size={16} />
                  </button>
                </div>
                <button
                  type="button"
                  disabled={!selectedVariant || !careProduct}
                  onClick={handleAdd}
                  className="flex-1 bg-[#001c9a] text-white py-3 rounded-full font-bold text-sm disabled:opacity-40"
                >
                  {formatVnd(unit * quantity)} | Thêm vào gói
                </button>
              </div>
            </div>
          </motion.aside>

          {/* Nutrition Drawer */}
          <AnimatePresence>
            {nutritionOpen && product && (
              <>
                <motion.div
                  initial={{ opacity: 0 }}
                  animate={{ opacity: 1 }}
                  exit={{ opacity: 0 }}
                  onClick={() => setNutritionOpen(false)}
                  className="fixed inset-0 bg-black/60 z-[170] backdrop-blur-md"
                />
                <motion.div
                  initial={{ x: "100%" }}
                  animate={{ x: 0 }}
                  exit={{ x: "100%" }}
                  transition={{ type: "spring", damping: 25, stiffness: 200 }}
                  className="fixed top-0 right-0 h-full w-full max-w-[500px] bg-[#fefef0] z-[180] shadow-2xl flex flex-col overflow-hidden"
                >
                  <div className="flex items-center justify-between px-8 py-6 border-b border-[#001c9a]/10 shrink-0">
                    <h2 className="text-xl font-black text-[#001c9a] tracking-tight">Thành phần & Dinh dưỡng</h2>
                    <button onClick={() => setNutritionOpen(false)} className="p-2 hover:bg-[#001c9a]/5 rounded-full transition-colors text-[#001c9a]">
                      <X size={20} />
                    </button>
                  </div>
                  <div className="flex-1 overflow-y-auto px-8 py-6 navy-scrollbar space-y-6">
                    <div>
                      <div className="text-[#001c9a]/80 leading-relaxed text-[13.5px] font-medium" dangerouslySetInnerHTML={{ __html: product.ingredients || 'Thông tin đang được cập nhật...' }} />
                    </div>
                    <div>
                      <NutritionFacts facts={product.nutrition_facts} />
                    </div>
                  </div>
                </motion.div>
              </>
            )}
          </AnimatePresence>

          {/* Usage Drawer */}
          <AnimatePresence>
            {usageOpen && product && (
              <>
                <motion.div
                  initial={{ opacity: 0 }}
                  animate={{ opacity: 1 }}
                  exit={{ opacity: 0 }}
                  onClick={() => setUsageOpen(false)}
                  className="fixed inset-0 bg-black/60 z-[170] backdrop-blur-md"
                />
                <motion.div
                  initial={{ x: "100%" }}
                  animate={{ x: 0 }}
                  exit={{ x: "100%" }}
                  transition={{ type: "spring", damping: 25, stiffness: 200 }}
                  className="fixed top-0 right-0 h-full w-full max-w-[500px] bg-[#fefef0] z-[180] shadow-2xl flex flex-col overflow-hidden"
                >
                  <div className="flex items-center justify-between px-8 py-6 border-b border-[#001c9a]/10 shrink-0">
                    <h2 className="text-xl font-black text-[#001c9a] tracking-tight">Hướng dẫn sử dụng</h2>
                    <button onClick={() => setUsageOpen(false)} className="p-2 hover:bg-[#001c9a]/5 rounded-full transition-colors text-[#001c9a]">
                      <X size={20} />
                    </button>
                  </div>
                  <div className="flex-1 overflow-y-auto px-8 py-6 navy-scrollbar space-y-6">
                    {product.usage_instructions && (
                      <div>
                        <h3 className="text-[10px] font-black uppercase tracking-widest text-[#001c9a] mb-2 py-1 border-b border-[#001c9a]/10">Cách dùng</h3>
                        <div className="text-[#001c9a]/80 leading-relaxed text-[13.5px] font-medium" dangerouslySetInnerHTML={{ __html: product.usage_instructions }} />
                      </div>
                    )}
                    {product.storage_instructions && (
                      <div>
                        <h3 className="text-[10px] font-black uppercase tracking-widest text-[#001c9a] mb-2 py-1 border-b border-[#001c9a]/10">Hướng dẫn bảo quản</h3>
                        <div className="text-[#001c9a]/80 leading-relaxed text-[13.5px] font-medium" dangerouslySetInnerHTML={{ __html: product.storage_instructions }} />
                      </div>
                    )}
                  </div>
                </motion.div>
              </>
            )}
          </AnimatePresence>
        </>
      )}
    </AnimatePresence>
  );
}
