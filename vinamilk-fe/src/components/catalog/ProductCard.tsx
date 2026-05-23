"use client";

import Link from "next/link";
import { motion } from "framer-motion";
import { Product } from "@/types";
import { getImageUrl } from "@/lib/api";
import { ShoppingCart } from "lucide-react";

interface ProductCardProps {
  product: Product;
  isFeatured?: boolean;
}

export default function ProductCard({ product, isFeatured = false }: ProductCardProps) {
  const featured = product.home_featured_variant;
  const firstVariant = product.variants?.[0];
  const representativeVariant = featured || firstVariant;

  if (!representativeVariant) return null;

  // Hover logic: Image 0 is resting, Image 1 is hover
  const gallery = Array.isArray(representativeVariant.images) ? representativeVariant.images : [];

  // Rule: isFeatured (Homepage) uses Gallery 0 -> 1.
  // Default (Collection/Search) uses Main Image.
  const restingImage = isFeatured
    ? (gallery[0] || representativeVariant.main_image || product.main_image)
    : (representativeVariant.main_image || product.main_image || gallery[0]);

  const hoverImage = isFeatured
    ? (gallery[1] || restingImage)
    : restingImage;

  const restingImageUrl = getImageUrl(restingImage);
  const hoverImageUrl = getImageUrl(hoverImage);

  const formattedPrice = representativeVariant.price.toLocaleString("vi-VN", {
    minimumFractionDigits: 3,
    maximumFractionDigits: 3
  });

  const displayTitle = product.sugar_level?.name
    ? `${product.brand?.name} • ${product.sugar_level.name}`
    : representativeVariant.flavor
      ? `${product.brand?.name} • ${representativeVariant.flavor}`
      : product.name;

  return (
    <Link href={`/products/${product.slug}`} className="block h-full">
      <div className="group relative bg-transparent flex flex-col h-full transition-all duration-300">
        {/* Image Section */}
        <div className="relative w-full aspect-square mb-6 flex items-center justify-center p-4 overflow-hidden bg-transparent transition-all duration-500">
          <div className="relative w-full h-full">
            {/* Resting Image */}
            <motion.img
              src={restingImageUrl || undefined}
              alt={product.name}
              className="absolute inset-0 w-full h-full object-contain transition-opacity duration-700 ease-in-out group-hover:opacity-0"
              initial={{ opacity: 1 }}
            />
            {/* Hover Image */}
            <motion.img
              src={hoverImageUrl || undefined}
              alt={`${product.name} - alternate`}
              className="absolute inset-0 w-full h-full object-contain opacity-0 transition-opacity duration-700 ease-in-out group-hover:opacity-100"
              initial={{ opacity: 0 }}
            />
          </div>

          {/* Featured Tag (Manageable) */}
          {(product.card_tag || product.certificates?.[0]) && (
            <div className="absolute top-0 left-0 z-10">
              <span className="inline-flex items-center justify-center px-4 py-2 bg-[#e9f0ff] text-[#0213b0] text-[10px] font-bold">
                {(product.card_tag || product.certificates![0]).name}
              </span>
            </div>
          )}
        </div>

        {/* Content Section */}
        <div className="flex flex-col flex-grow px-2">
          {/* Top Label: Product Line Name */}
          <p className="text-[10px] font-bold tracking-widest mb-2 text-[#0213b0]/60 uppercase">
            {product.product_line?.name || "Sản phẩm Vinamilk"}
          </p>

          {/* Title with Cart Icon */}
          <div className="flex items-start justify-between gap-4 mb-2">
            <h3 className="font-bold text-[#0213b0] text-lg md:text-lg leading-tight">
              {displayTitle}
            </h3>
            <div className="text-[#0213b0]/40 mt-1 hover:text-[#0213b0] transition-colors">
              <ShoppingCart size={18} />
            </div>
          </div>

          {/* Short Description */}
          <p className="text-[#0213b0]/50 text-[11px] md:text-xs line-clamp-2 mb-6 leading-relaxed">
            {product.short_description?.replace(/<[^>]*>/g, '')}
          </p>

          <div className="mt-auto">
            {/* Bottom Info Row: Volume & Packaging */}
            <div className="flex items-center justify-between gap-4">
              <div className="bg-[#0213b0]/5 px-3 py-2 rounded-md whitespace-nowrap overflow-hidden">
                <p className="text-[10px] md:text-[10px] text-[#0213b0] font-bold">
                  {representativeVariant.volume && `${representativeVariant.volume}`}
                  {representativeVariant.packaging_type && ` , ${representativeVariant.packaging_type}`}
                </p>
              </div>

              <div className="flex items-center gap-3 shrink-0">
                {representativeVariant.discount_percentage > 0 && (
                  <span className="text-[10px] md:text-xs font-bold text-[#0213b0]/50 line-through decoration-1">
                    -{representativeVariant.discount_percentage}%
                  </span>
                )}
                <span className="text-base md:text-sm font-black text-[#0213b0]">
                  {formattedPrice}đ
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Link>
  );
}
