"use client";

import React, { useState, useEffect } from "react";
import { FlashSale, Product } from "@/types";
import Link from "next/link";
import { getImageUrl } from "@/lib/api";
import { motion, AnimatePresence } from "framer-motion";
import { Zap, ShoppingCart } from "lucide-react";

interface FlashSaleSectionProps {
  data: FlashSale | null;
  products: Product[];
}

export const FlashSaleSection = ({ data, products }: FlashSaleSectionProps) => {
  const [timeLeft, setTimeLeft] = useState({ days: 0, hours: 0, minutes: 0, seconds: 0 });
  const [status, setStatus] = useState<"pending" | "ongoing" | "ended">("pending");

  useEffect(() => {
    if (!data?.start_time || !data?.end_time) return;

    const timer = setInterval(() => {
      const now = new Date().getTime();
      const start = new Date(data.start_time).getTime();
      const end = new Date(data.end_time).getTime();

      let targetTime = 0;
      if (now < start) {
        setStatus("pending");
        targetTime = start;
      } else if (now < end) {
        setStatus("ongoing");
        targetTime = end;
      } else {
        setStatus("ended");
        clearInterval(timer);
        return;
      }

      const diff = targetTime - now;
      setTimeLeft({
        days: Math.floor(diff / (1000 * 60 * 60 * 24)),
        hours: Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
        minutes: Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60)),
        seconds: Math.floor((diff % (1000 * 60)) / 1000),
      });
    }, 1000);

    return () => clearInterval(timer);
  }, [data?.start_time, data?.end_time]);

  return (
    <div id="flash-sale" className="scroll-mt-24 mb-32">
      {/* Flash Sale Banner Text & Countdown */}
      {data && (
        <div className="flex flex-col items-center justify-center text-center mb-16 pt-8">
          <h2 className="text-2xl md:text-3xl lg:text-[58px] font-sans font-black text-[#001c9a] tracking-tight leading-none mb-4">
            {data.title || "Flash sales, tuần lễ Vinamilk"}
          </h2>

          {data.content && (
            <p className="text-[#001c9a] text-base md:text-xl font-medium max-w-4xl mx-auto leading-relaxed px-4">
              {data.content}
            </p>
          )}

          {/* Countdown */}
          {status !== "ended" ? (
            <div className="flex items-center justify-center gap-3 md:gap-6 mt-12 md:mt-16 text-[#f74d1e]">
              <CountdownBlock value={timeLeft.days} label="Ngày" />
              <CountdownSeparator />
              <CountdownBlock value={timeLeft.hours} label="Giờ" />
              <CountdownSeparator />
              <CountdownBlock value={timeLeft.minutes} label="Phút" />
              <CountdownSeparator />
              <CountdownBlock value={timeLeft.seconds} label="Giây" />
            </div>
          ) : (
            <p className="text-[#f74d1e]/80 text-[34px] font-medium mt-12">Chương trình đã kết thúc.</p>
          )}
        </div>
      )}

      {/* Product Grid */}
      {products.length > 0 && (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
          {products.map((product, i) => (
            <FlashSaleProductCard key={product.id} product={product} index={i} />
          ))}
        </div>
      )}
    </div>
  );
};

/* ── Countdown Block ── */
const CountdownBlock = ({ value, label }: { value: number; label: string }) => (
  <div className="flex flex-col items-center justify-center min-w-[60px] md:min-w-[80px]">
    <AnimatePresence mode="wait">
      <motion.span
        key={value}
        initial={{ y: -10, opacity: 0 }}
        animate={{ y: 0, opacity: 1 }}
        exit={{ y: 10, opacity: 0 }}
        transition={{ duration: 0.18 }}
        className="text-5xl md:text-7xl lg:text-[88px] font-bold tabular-nums leading-none tracking-tight"
      >
        {String(value).padStart(2, "0")}
      </motion.span>
    </AnimatePresence>
    <span className="text-sm md:text-lg font-sans mt-2 md:mt-4 opacity-90">
      {label}
    </span>
  </div>
);

const CountdownSeparator = () => (
  <div className="flex items-center justify-center h-full pb-8 md:pb-12">
    <span className="font-sans font-medium text-4xl md:text-6xl lg:text-[72px] leading-none">:</span>
  </div>
);

/* ── Flash Sale Product Card — transparent bg, giống collection ── */
const FlashSaleProductCard = ({ product, index }: { product: any; index: number }) => {
  const firstVariant = product.variants?.[0];
  const featuredVariant = product.home_featured_variant || firstVariant;
  const variant = featuredVariant || firstVariant;

  if (!variant) return null;

  const restingImage =
    (variant.main_image ? getImageUrl(variant.main_image) : null) ||
    (product.main_image ? getImageUrl(product.main_image) : null) ||
    "/placeholder.png";

  const price = variant.price ?? 0;
  const basePrice = variant.base_price ?? 0;
  const discount = variant.discount_percentage ?? 0;

  const displayTitle = product.sugar_level?.name
    ? `${product.brand?.name} • ${product.sugar_level.name}`
    : variant.flavor
      ? `${product.brand?.name} • ${variant.flavor}`
      : product.name;

  const formattedPrice = price > 0
    ? price.toLocaleString("vi-VN", { minimumFractionDigits: 3, maximumFractionDigits: 3 })
    : null;
  const formattedBase = basePrice > 0
    ? basePrice.toLocaleString("vi-VN", { minimumFractionDigits: 3, maximumFractionDigits: 3 })
    : null;

  return (
    <motion.div
      initial={{ opacity: 0, y: 16 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ delay: index * 0.05, duration: 0.3 }}
    >
      <Link
        href={`/products/${product.slug}`}
        className="group block bg-transparent overflow-hidden"
      >
        {/* Image */}
        <div className="relative aspect-square flex items-center justify-center p-4 overflow-hidden">
          <img
            src={restingImage}
            alt={product.name}
            className="w-full h-full object-contain"
          />
          {(product.card_tag || product.certificates?.[0]) && (
            <div className="absolute top-0 left-0">
              <span className="inline-flex items-center px-3 py-1.5 bg-[#e9f0ff] text-[#0213b0] text-[10px] font-bold">
                {(product.card_tag || product.certificates[0]).name}
              </span>
            </div>
          )}
          {discount > 0 && (
            <div className="absolute top-0 right-0 bg-red-500 text-white text-[10px] font-black px-2 py-1">
              -{discount}%
            </div>
          )}
        </div>

        {/* Info */}
        <div className="px-2 pb-4 flex flex-col gap-1.5">
          <p className="text-[10px] font-bold tracking-widest text-[#0213b0]/50 uppercase">
            {product.product_line?.name || "Vinamilk"}
          </p>
          <h3 className="text-[#0213b0] font-bold text-sm leading-tight line-clamp-2 group-hover:text-[#0213b0]/70 transition-colors">
            {displayTitle}
          </h3>
          {/* Short description */}
          {product.short_description && (
            <p className="text-[#0213b0]/50 text-[11px] line-clamp-2 leading-relaxed">
              {product.short_description.replace(/<[^>]*>/g, "")}
            </p>
          )}
          {/* Volume + Packaging */}
          <div className="mt-auto pt-2">
            <div className="flex items-center justify-between gap-2">
              {(variant.volume || variant.packaging_type) && (
                <div className="bg-[#0213b0]/5 px-2.5 py-1.5 rounded text-[10px] text-[#0213b0] font-bold leading-none">
                  {variant.volume}
                  {variant.packaging_type ? ` · ${variant.packaging_type}` : ""}
                </div>
              )}
              <div className="flex flex-col items-end shrink-0">
                {discount > 0 && formattedBase && (
                  <span className="text-[10px] text-[#0213b0]/40 line-through">{formattedBase}đ</span>
                )}
                {formattedPrice && (
                  <span className="text-[#0213b0] font-black text-xs">{formattedPrice}đ</span>
                )}
              </div>
            </div>
          </div>
        </div>
      </Link>
    </motion.div>
  );
};
