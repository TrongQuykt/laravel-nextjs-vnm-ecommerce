"use client";

import { Product, ProductVariant } from "@/types";
import { useState, useMemo, useEffect } from "react";

interface VariantSelectorProps {
  product: Product;
  onVariantChange: (variant: ProductVariant) => void;
}

export default function VariantSelector({ product, onVariantChange }: VariantSelectorProps) {
  const variants = useMemo(() => product.variants || [], [product.variants]);

  const volumes = useMemo(() => {
    const unique = Array.from(new Set(variants.map(v => v.volume).filter(Boolean)));
    return unique;
  }, [variants]);

  const [selectedVolume, setSelectedVolume] = useState<string | null>(null);

  useEffect(() => {
    if (volumes.length > 0 && !selectedVolume) setSelectedVolume(volumes[0]);
  }, [volumes, selectedVolume]);

  const variantsInVolume = useMemo(() =>
    variants.filter(v => v.volume === selectedVolume),
    [variants, selectedVolume]
  );

  const [selectedVariantId, setSelectedVariantId] = useState<number | null>(null);

  useEffect(() => {
    if (variantsInVolume.length > 0) setSelectedVariantId(variantsInVolume[0].id);
  }, [selectedVolume, variantsInVolume]);

  const currentVariant = useMemo(() =>
    variants.find(v => v.id === selectedVariantId),
    [selectedVariantId, variants]
  );

  useEffect(() => {
    if (currentVariant) onVariantChange(currentVariant);
  }, [currentVariant, onVariantChange]);

  if (variants.length === 0) return null;

  return (
    <div className="flex flex-col gap-3">
      {/* Volume Tab Strip — no outer border, tab has bottom highlight */}
      {volumes.length > 0 && (
        <div className="flex">
          {volumes.map((vol, idx) => {
            const active = selectedVolume === vol;
            return (
              <button
                key={vol}
                onClick={() => setSelectedVolume(vol)}
                className={`flex-1 py-2.5 text-sm font-bold tracking-wide transition-all duration-200 border-b-2
                  ${active
                    ? "bg-[#d3e1ff] text-v-navy border-[#1e3a8a]"
                    : "bg-transparent text-v-navy border-transparent hover:bg-[#d3e1ff] hover:text-v-navy"
                  }`}
              >
                {vol}
              </button>
            );
          })}
        </div>
      )}

      {/* Packaging Rows */}
      <div className="flex flex-col">
        {variantsInVolume.map((variant, idx) => {
          const isActive = selectedVariantId === variant.id;
          const isLast = idx === variantsInVolume.length - 1;
          return (
            <button
              key={variant.id}
              onClick={() => setSelectedVariantId(variant.id)}
              className={`flex items-center justify-between px-3 py-3 text-left transition-all duration-200
                ${isActive ? "bg-[#eef2ff] font-sans" : "bg-transparent hover:bg-[#f8f9ff]"}
                ${!isLast ? "border-b border-[#1e3a8a]/10" : ""}
              `}
            >
              {/* Left: radio + name */}
              <div className="flex items-center gap-3">
                <div className={`w-4 h-4 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-colors ${isActive ? "border-[#1e3a8a]" : "border-[#1e3a8a]/30"
                  }`}>
                  {isActive && <div className="w-2 h-2 rounded-full bg-[#1e3a8a]" />}
                </div>
                <span className={`text-sm font-bold ${isActive ? "text-v-navy" : "text-v-navy/70"}`}>
                  {variant.packaging_type || "Sản phẩm lẻ"}
                </span>
              </div>

              {/* Right: discount + strikethrough + price */}
              <div className="flex items-center gap-2 text-right">
                {variant.discount_percentage > 0 && (
                  <>
                    <span className="text-[11px] font-bold text-red-500">
                      —{variant.discount_percentage}%
                    </span>
                    <span className="text-[11px] text-[#1e3a8a]/35 line-through">
                      {variant.base_price?.toLocaleString("vi-VN", { minimumFractionDigits: 3, maximumFractionDigits: 3 })}đ
                    </span>
                  </>
                )}
                <span className={`text-sm font-bold ${isActive ? "text-v-navy" : "text-v-navy/60"}`}>
                  {variant.price?.toLocaleString("vi-VN", { minimumFractionDigits: 3, maximumFractionDigits: 3 })}đ
                </span>
              </div>
            </button>
          );
        })}
      </div>
    </div>
  );
}