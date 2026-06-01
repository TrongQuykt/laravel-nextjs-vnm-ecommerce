"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { Product } from "@/types";
import { catalogApi, getImageUrl } from "@/lib/api";

interface ProductLineNavigatorProps {
  currentProduct: Product;
}

interface ProductLineNavigatorProps {
  currentProduct: Product;
  lineProducts: Product[]; // ← nhận sẵn từ server
}

export function ProductLineNavigator({ currentProduct, lineProducts }: ProductLineNavigatorProps) {
  const [rows, setRows] = useState<Product[][]>([]);

  useEffect(() => {
    if (lineProducts.length === 0) return;

    const getColumns = () => {
      const width = window.innerWidth;
      if (width >= 768) return 6;
      if (width >= 640) return 4;
      return 3;
    };

    const buildRows = () => {
      const cols = getColumns();
      const result: Product[][] = [];
      for (let i = 0; i < lineProducts.length; i += cols) {
        result.push(lineProducts.slice(i, i + cols));
      }
      setRows(result);
    };

    buildRows();
    window.addEventListener("resize", buildRows);
    return () => window.removeEventListener("resize", buildRows);
  }, [lineProducts]);

  if (lineProducts.length <= 1) return null;
  return (
    <div className="w-full mt-6 mb-8">
      {rows.map((rowProducts, rowIndex) => (
        <div key={rowIndex} className="relative mb-2">

          {/* Đường kẻ xanh — z-30 đè lên ảnh */}
          <div
            className="absolute left-0 right-0 h-[6px] bg-v-navy z-30 pointer-events-none"
            style={{ top: "130px" }}
          />

          <div className="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-0">
            {rowProducts.map((product) => {
              const isActive = product.id === currentProduct.id;
              const featured = product.home_featured_variant;
              const firstVariant = product.variants?.[0];
              const representativeVariant = featured || firstVariant;
              const gallery = representativeVariant?.images || [];
              const displayImage = gallery.length > 0
                ? gallery[0]
                : (representativeVariant?.main_image || product.main_image);



              return (
                <Link
                  key={product.id}
                  href={`/products/${product.slug}`}
                  className="group flex flex-col relative z-10"
                >
                  <div
                    className={`flex flex-col transition-all duration-500 ${isActive
                      ? "bg-[#EBF3FF] rounded-3xl"
                      : "bg-transparent hover:bg-gray-50/50 rounded-3xl"
                      }`}
                  >
                    {/* Image container — overflow-hidden để scale không tràn sang ô khác */}
                    <div className="relative w-full h-40 overflow-hidden flex items-end justify-center">
                      <img
                        src={getImageUrl(displayImage) || ""}
                        alt={product.name}
                        className="object-contain object-bottom
                          transition-transform duration-500
                          scale-[1.6] group-hover:scale-[1.7] origin-bottom"
                        style={{ width: "100%", height: "100%", marginBottom: "0px" }}
                      />
                    </div>

                    {/* Spacer khớp đường kẻ */}
                    {/* <div className="w-full h-[2px]" /> */}

                    {/* Text */}
                    <div className="w-full pt-1 pb-3 px-1 text-center flex flex-col items-center min-h-[50px]">
                      <span
                        className={`text-[10px] sm:text-[12px] leading-tight tracking-tight text-center transition-colors ${isActive
                          ? "text-v-navy font-sans"
                          : "text-v-navy/40 font-sans group-hover:text-v-navy"
                          }`}
                      >
                        {product.sugar_level?.name || representativeVariant?.flavor || product.name.replace(/Vinamilk|Green Farm|Sữa tươi/gi, "").trim()}
                      </span>
                    </div>
                  </div>
                </Link>
              );
            })}
          </div>

        </div>
      ))}
    </div>
  );
}