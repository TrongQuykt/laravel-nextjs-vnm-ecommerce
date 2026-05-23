"use client";

import React, { useEffect, useState, useRef } from "react";
import { Product, ProductVariant } from "@/types";
import { ChevronUp } from "lucide-react";
import { motion, AnimatePresence } from "framer-motion";

interface QuickAddToCartBarProps {
  product: Product;
  selectedVariant: ProductVariant;
  quantity: number;
  setQuantity: (q: number) => void;
  mainButtonRef: React.RefObject<HTMLDivElement | null>;
  certifiedRef: React.RefObject<HTMLElement | null>;
  bottomRef: React.RefObject<HTMLDivElement | null>;
}

import { useCart } from "@/context/CartContext";

export function QuickAddToCartBar({
  product,
  selectedVariant,
  quantity,
  setQuantity,
  mainButtonRef,
  certifiedRef,
  bottomRef
}: QuickAddToCartBarProps) {
  const { addToCart, isLoading } = useCart();
  const [isVisible, setIsVisible] = useState(false);
  const [isPastMainBtn, setIsPastMainBtn] = useState(false);
  const [isReachedBottom, setIsReachedBottom] = useState(false);

  const handleAddToCart = (e: React.MouseEvent<HTMLButtonElement>) => {
    addToCart(product, selectedVariant, quantity, e.currentTarget);
  };

  useEffect(() => {
    // 1. Theo dõi nút mua hàng chính
    const mainBtnObserver = new IntersectionObserver(
      ([entry]) => {
        setIsPastMainBtn(!entry.isIntersecting && entry.boundingClientRect.top < 0);
      },
      { threshold: 0 }
    );

    // 2. Theo dõi điểm dừng (Certified hoặc Cột mốc đáy trang)
    const bottomObserver = new IntersectionObserver(
      ([entry]) => {
        // Đã chạm HOẶC đã cuộn qua hẳn (top < 0) thì đều coi là đã đến đáy
        setIsReachedBottom(entry.isIntersecting || entry.boundingClientRect.top < 0);
      },
      { threshold: 0 }
    );

    if (mainButtonRef.current) mainBtnObserver.observe(mainButtonRef.current);
    
    // Ưu tiên dừng ở Certified, nếu không có thì dừng ở Cột mốc đáy trang
    if (certifiedRef.current) {
      bottomObserver.observe(certifiedRef.current);
    } 
    
    if (bottomRef.current) {
      bottomObserver.observe(bottomRef.current);
    }

    return () => {
      mainBtnObserver.disconnect();
      bottomObserver.disconnect();
    };
  }, [mainButtonRef, certifiedRef, bottomRef]);

  useEffect(() => {
    setIsVisible(isPastMainBtn && !isReachedBottom);
  }, [isPastMainBtn, isReachedBottom]);

  const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  const variantInfo = [
    selectedVariant.volume,
    selectedVariant.packaging_type
  ].filter(Boolean).join(" / ");

  return (
    <AnimatePresence>
      {isVisible && (
        <motion.div
          initial={{ y: 100, opacity: 0 }}
          animate={{ y: 0, opacity: 1 }}
          exit={{ y: 100, opacity: 0 }}
          className="fixed bottom-6 left-0 right-0 z-[100] px-4 md:px-6"
        >
          <div className="max-w-6xl mx-auto bg-[#fffff1] border border-v-navy/10 shadow-[0_8px_30px_rgb(0,0,0,0.12)] rounded-xl p-3 md:p-4 flex items-center justify-between gap-4">
            {/* Product Info */}
            <div className="flex items-center gap-4 overflow-hidden">
              <div className="w-12 h-12 bg-transparent rounded-lg flex-shrink-0 p-1">
                <img
                  src={selectedVariant.main_image || product.main_image || ""}
                  alt={product.name}
                  className="w-full h-full object-contain"
                />
              </div>
              <div className="hidden sm:block overflow-hidden">
                <h3 className="text-[13px] font-bold text-v-navy truncate flex items-center">
                  <span className="truncate">{product.name}</span>

                  {selectedVariant.volume && (
                    <>
                      <span className="mx-1 opacity-40 shrink-0">/</span>
                      <span className="shrink-0">{selectedVariant.volume}</span>
                    </>
                  )}

                  {selectedVariant.packaging_type && (
                    <>
                      <span className="mx-1 opacity-40 shrink-0">/</span>
                      <span className="shrink-0">{selectedVariant.packaging_type}</span>
                    </>
                  )}
                </h3>
              </div>
            </div>

            {/* Actions */}
            <div className="flex items-center gap-3 md:gap-6">
              {/* Quantity Selector */}
              <div className="flex items-center border border-v-navy/20 rounded-lg h-10 px-2 gap-3 bg-white">
                <button
                  onClick={() => setQuantity(Math.max(1, quantity - 1))}
                  className="text-v-navy/40 hover:text-v-navy text-lg font-bold transition-colors w-6"
                >
                  −
                </button>
                <span className="w-4 text-center text-[13px] font-bold text-v-navy">
                  {quantity}
                </span>
                <button
                  onClick={() => setQuantity(quantity + 1)}
                  className="text-v-navy/40 hover:text-v-navy text-lg font-bold transition-colors w-6"
                >
                  +
                </button>
              </div>

              {/* Price | Add to Cart */}
              <button 
                onClick={handleAddToCart}
                disabled={isLoading}
                className="h-10 px-6 bg-[#0213b0] text-white rounded-lg text-[13px] font-bold tracking-wide hover:bg-[#172d6e] transition-all flex items-center gap-2 shadow-lg shadow-blue-900/20 whitespace-nowrap disabled:opacity-70"
              >
                {isLoading ? (
                  <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
                ) : (
                  <>
                    {(selectedVariant.price * quantity).toLocaleString("vi-VN", { minimumFractionDigits: 3, maximumFractionDigits: 3 })}đ
                    <span className="opacity-40">|</span>
                    Thêm vào giỏ
                  </>
                )}
              </button>

              {/* Scroll to top */}
              <button
                onClick={scrollToTop}
                className="w-10 h-10 flex items-center justify-center border border-v-navy/20 text-v-navy rounded-lg hover:bg-white transition-colors"
              >
                <ChevronUp size={20} />
              </button>
            </div>
          </div>
        </motion.div>
      )}
    </AnimatePresence>
  );
}
