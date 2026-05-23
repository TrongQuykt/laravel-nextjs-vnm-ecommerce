"use client";

import React, { useState, useEffect } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { X, ShoppingCart } from "lucide-react";
import { Product, PromotionBanner } from "@/types";
import { getImageUrl } from "@/lib/api";
import Link from "next/link";

interface PromotionModalProps {
  banner: PromotionBanner | null;
  products: Product[];
  isOpen: boolean;
  onClose: () => void;
}

function formatDate(dateStr: string | null) {
  if (!dateStr) return null;
  const d = new Date(dateStr);
  return `${String(d.getDate()).padStart(2, "0")}/${String(d.getMonth() + 1).padStart(2, "0")}/${d.getFullYear()}`;
}

export const PromotionModal = ({ banner, products, isOpen, onClose }: PromotionModalProps) => {
  const [displayCount, setDisplayCount] = useState(6); // Default 6 products for 3 columns

  // ESC key
  useEffect(() => {
    const fn = (e: KeyboardEvent) => { if (e.key === "Escape") onClose(); };
    if (isOpen) window.addEventListener("keydown", fn);
    return () => window.removeEventListener("keydown", fn);
  }, [isOpen, onClose]);

  // Body scroll lock
  useEffect(() => {
    document.body.style.overflow = isOpen ? "hidden" : "";
    return () => { document.body.style.overflow = ""; };
  }, [isOpen]);

  if (!banner) return null;

  const limit = banner.modal_products_limit || 12;
  const visibleProducts = products.slice(0, Math.min(displayCount, limit));

  const startFmt = formatDate(banner.start_date);
  const endFmt = formatDate(banner.end_date);
  const dateStr = startFmt && endFmt ? `${startFmt} - ${endFmt}` : startFmt || endFmt || null;

  return (
    <AnimatePresence>
      {isOpen && (
        <>
          {/* Backdrop */}
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            transition={{ duration: 0.2 }}
            onClick={onClose}
            className="fixed inset-0 bg-black/60 z-[100] backdrop-blur-sm"
          />

          {/* Modal panel */}
          <motion.div
            initial={{ opacity: 0, scale: 0.96, y: 20 }}
            animate={{ opacity: 1, scale: 1, y: 0 }}
            exit={{ opacity: 0, scale: 0.96, y: 20 }}
            transition={{ duration: 0.22, ease: [0.25, 0.1, 0.25, 1] }}
            className="fixed z-[101] inset-0 flex items-center justify-center p-3 md:p-6 pointer-events-none"
          >
            {/* Vùng modal cực rộng max-w-6xl ~ 1152px, toàn bộ nền cream */}
            <div className="relative bg-[#fdfcf0] w-full max-w-7xl max-h-[94vh] rounded-2xl shadow-3xl overflow-hidden pointer-events-auto flex flex-col">

              {/* ── Close button ──────────────────────────────── */}
              <button
                onClick={onClose}
                className="absolute top-4 right-4 z-20 w-10 h-10 rounded-full shadow-sm hover:bg-white flex items-center justify-center text-[#001c9a] transition-colors"
                aria-label="Đóng"
              >
                <X size={20} />
              </button>

              {/* ── Scrollable content ────────────────────────── */}
              <div className="flex-1 overflow-y-auto overscroll-contain [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:bg-[#001c9a]/20 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-transparent">

                {/* TOP SECTION: image left + content right */}
                <div className="flex flex-col md:flex-row min-h-[300px]">

                  {/* LEFT: Banner image - Sticky */}
                  {(banner.modal_image_path || banner.image_path) && (
                    <div className="w-full md:w-5/12 p-8 lg:p-12 relative">
                      {/* Thẻ này sẽ dính trên top khi cuộn nội dung bên phải */}
                      <div className="sticky top-8 w-full max-w-[420px] mx-auto rounded-xl overflow-hidden shadow-sm border border-[#001c9a]/10 bg-transparent">
                        <img
                          src={getImageUrl(banner.modal_image_path || banner.image_path) || ""}
                          alt={banner.title}
                          className="w-full h-auto object-contain"
                        />
                      </div>
                    </div>
                  )}

                  {/* RIGHT: Content */}
                  <div className="flex-1 px-6 py-8 md:pr-12 md:py-12">
                    {/* Date range */}
                    {dateStr && (
                      <p className="text-sm text-v-navy font-semibold tracking-wide mb-3">
                        {dateStr}
                      </p>
                    )}

                    {/* Title */}
                    <h2 className="text-2xl md:text-3xl lg:text-4xl font-sans font-black text-[#001c9a] leading-tight mb-8">
                      {banner.modal_title || banner.title}
                    </h2>

                    {/* Rich HTML content & Multiple Tables */}
                    {(() => {
                      const content = banner.modal_content || "";
                      const tables = banner.modal_table_data || [];
                      
                      const TableComponent = ({ rows }: { rows: any[] }) => {
                        if (!rows || rows.length === 0) return null;
                        
                        // Chỉ hiện cột nếu hàng tiêu đề (hàng 0) có nội dung
                        const showCol1 = !!rows[0]?.col1;
                        const showCol2 = !!rows[0]?.col2;
                        const showCol3 = !!rows[0]?.col3;
                        const showCol4 = !!rows[0]?.col4;
                        const showCol5 = !!rows[0]?.col5;

                        return (
                          <div className="my-8 overflow-x-auto">
                            <table className="w-full border-collapse text-sm md:text-base bg-transparent border border-[#001c9a]">
                              <thead>
                                <tr>
                                  {showCol1 && <th className="border border-[#001c9a] p-3 md:p-4 text-[#001c9a] font-bold text-left">{rows[0].col1}</th>}
                                  {showCol2 && <th className="border border-[#001c9a] p-3 md:p-4 text-[#001c9a] font-bold text-left">{rows[0].col2}</th>}
                                  {showCol3 && <th className="border border-[#001c9a] p-3 md:p-4 text-[#001c9a] font-bold text-left">{rows[0].col3}</th>}
                                  {showCol4 && <th className="border border-[#001c9a] p-3 md:p-4 text-[#001c9a] font-bold text-left">{rows[0].col4}</th>}
                                  {showCol5 && <th className="border border-[#001c9a] p-3 md:p-4 text-[#001c9a] font-bold text-left">{rows[0].col5}</th>}
                                </tr>
                              </thead>
                              <tbody>
                                {rows.slice(1).map((row: any, i: number) => (
                                  <tr key={i}>
                                    {showCol1 && <td className="border border-[#001c9a] p-3 md:p-4 align-top text-[#001c9a]">{row.col1}</td>}
                                    {showCol2 && <td className="border border-[#001c9a] p-3 md:p-4 align-top text-[#001c9a]">{row.col2}</td>}
                                    {showCol3 && <td className="border border-[#001c9a] p-3 md:p-4 align-top text-[#001c9a]">{row.col3}</td>}
                                    {showCol4 && <td className="border border-[#001c9a] p-3 md:p-4 align-top text-[#001c9a]">{row.col4}</td>}
                                    {showCol5 && <td className="border border-[#001c9a] p-3 md:p-4 align-top text-[#001c9a]">{row.col5}</td>}
                                  </tr>
                                ))}
                              </tbody>
                            </table>
                          </div>
                        );
                      };

                      const proseClasses = "prose prose-base md:prose-lg max-w-none text-[#001c9a]/80 leading-relaxed prose-headings:text-[#001c9a] prose-headings:font-black prose-headings:mt-8 prose-headings:mb-4 prose-a:text-blue-600 prose-a:underline prose-strong:text-[#001c9a] prose-strong:font-bold prose-ul:list-disc prose-ul:ml-5 prose-li:my-2 prose-ol:list-decimal prose-ol:ml-5";

                      // Tách nội dung theo các tag [TABLE:id] hoặc [TABLE]
                      const regex = /(?:<p>)?\[TABLE(?::(\w+))?\](?:<\/p>)?/g;
                      const parts = content.split(regex);
                      
                      const elements = [];
                      for (let i = 0; i < parts.length; i += 2) {
                        const textPart = parts[i];
                        const tableId = parts[i + 1];

                        if (textPart) {
                          elements.push(
                            <div key={`text-${i}`} className={proseClasses} dangerouslySetInnerHTML={{ __html: textPart }} />
                          );
                        }

                        if (tableId !== undefined || (i + 1 < parts.length)) {
                          const searchId = tableId || "1";
                          const tableData = tables.find(t => t.table_id === searchId) || (tableId === undefined ? tables[0] : null);
                          
                          if (tableData && tableData.rows) {
                            elements.push(<TableComponent key={`table-${i}`} rows={tableData.rows} />);
                          }
                        }
                      }

                      if (tables.length > 0 && !content.includes('[TABLE')) {
                        return (
                          <>
                            <div className={proseClasses} dangerouslySetInnerHTML={{ __html: content }} />
                            {tables.map((t, idx) => (
                              <TableComponent key={idx} rows={t.rows} />
                            ))}
                          </>
                        );
                      }

                      return <>{elements}</>;
                    })()}
                  </div>
                </div>

                {/* BOTTOM SECTION: Products list */}
                {visibleProducts.length > 0 && (
                  <div className="px-6 md:px-12 py-10">
                    {/* <h3 className="text-[#001c9a] font-black text-xl mb-8 uppercase tracking-widest">Sản phẩm khuyến mãi</h3> */}


                    {/* Products grid - 3 cột */}
                    <div className="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-6">
                      {visibleProducts.map((product) => (
                        <ModalProductCard key={product.id} product={product} onClose={onClose} />
                      ))}
                    </div>

                    {/* Load more */}
                    {products.length > displayCount && displayCount < limit && (
                      <div className="mt-8 flex justify-center">
                        <button
                          onClick={() => setDisplayCount((p) => p + 6)}
                          className="px-6 py-2.5 border border-[#001c9a] text-[#001c9a] text-xs font-bold uppercase tracking-wider rounded-full"
                        >
                          Xem thêm sản phẩm
                        </button>
                      </div>
                    )}
                  </div>
                )}
              </div>
            </div>
          </motion.div>
        </>
      )}
    </AnimatePresence>
  );
};

/* ── Modal Product Card — Nền trong suốt ── */
const ModalProductCard = ({ product, onClose }: { product: any; onClose: () => void }) => {
  const firstVariant = product.variants?.[0];
  const featuredVariant = product.home_featured_variant || firstVariant;
  const representativeVariant = featuredVariant || firstVariant;

  if (!representativeVariant) return null;

  const restingImage =
    (representativeVariant.main_image ? getImageUrl(representativeVariant.main_image) : null) ||
    (product.main_image ? getImageUrl(product.main_image) : null) ||
    "/placeholder.png";

  const price = representativeVariant.price ?? 0;
  const basePrice = representativeVariant.base_price ?? 0;
  const discount = representativeVariant.discount_percentage ?? 0;

  const displayTitle = product.sugar_level?.name
    ? `${product.brand?.name} • ${product.sugar_level.name}`
    : representativeVariant.flavor
      ? `${product.brand?.name} • ${representativeVariant.flavor}`
      : product.name;

  const formattedPrice = price > 0 ? price.toLocaleString("vi-VN", { minimumFractionDigits: 3, maximumFractionDigits: 3 }) + "đ" : null;
  const formattedBase = basePrice > 0 ? basePrice.toLocaleString("vi-VN", { minimumFractionDigits: 3, maximumFractionDigits: 3 }) + "đ" : null;

  return (
    <Link
      href={`/products/${product.slug}`}
      onClick={onClose}
      className="group flex flex-col bg-transparent rounded-xl overflow-hidden"
    >
      {/* Image */}
      <div className="relative aspect-square flex items-center justify-center p-2 overflow-hidden bg-transparent">
        <img
          src={restingImage}
          alt={product.name}
          className="w-full h-full object-contain"
        />
        {/* Card tag */}
        {(product.card_tag || product.certificates?.[0]) && (
          <div className="absolute top-0 left-0">
            <span className="inline-flex items-center px-2 py-1 bg-blue-50 text-[#0213b0] text-[9px] font-bold rounded-sm border border-blue-100">
              {(product.card_tag || product.certificates[0]).name}
            </span>
          </div>
        )}
        {/* Discount badge */}
        {discount > 0 && (
          <div className="absolute top-0 right-0 bg-[#e3001b] text-white text-[10px] font-black px-1.5 py-0.5 rounded-sm">
            -{discount}%
          </div>
        )}
      </div>

      {/* Info */}
      <div className="pt-3 flex flex-col gap-1.5">
        <p className="text-[10px] font-bold tracking-widest text-[#001c9a]/40 uppercase">
          {product.product_line?.name || "Vinamilk"}
        </p>
        <h4 className="text-[#001c9a] font-bold text-xl leading-snug line-clamp-2 group-hover:text-[#001c9a]/70">
          {displayTitle}
        </h4>

        <div className="mt-auto pt-1 flex items-center justify-between gap-1">
          {/* Volume + Packaging */}
          {(representativeVariant.volume || representativeVariant.packaging_type) && (
            <div className="bg-[#001c9a]/5 px-2 py-1 rounded text-[10px] text-[#001c9a] font-semibold leading-none">
              {representativeVariant.volume}
              {representativeVariant.packaging_type ? ` · ${representativeVariant.packaging_type}` : ""}
            </div>
          )}

          {/* Price */}
          <div className="flex flex-col items-end">
            {discount > 0 && formattedBase && (
              <span className="text-[10px] text-[#001c9a]/40 line-through leading-none mb-0.5">{formattedBase}</span>
            )}
            {formattedPrice && (
              <span className="text-[#001c9a] font-black text-[15px] leading-none">{formattedPrice}</span>
            )}
          </div>
        </div>
      </div>
    </Link>
  );
};
