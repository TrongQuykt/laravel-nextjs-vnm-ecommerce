"use client";

import React, { useState, useEffect } from "react";
import { PromotionBanner, Product } from "@/types";
import { getImageUrl } from "@/lib/api";
import { findPromotionBannerFromUrl } from "@/lib/promotionBanner";
import { PromotionModal } from "./PromotionModal";
import Link from "next/link";
import { ArrowUpRight } from "lucide-react";
import { motion } from "framer-motion";
import { useSearchParams } from "next/navigation";

interface MonthlyDealsGridProps {
  banners: PromotionBanner[];
  modalProducts: Product[];
}

function formatDate(dateStr: string | null) {
  if (!dateStr) return null;
  const d = new Date(dateStr);
  return `${String(d.getDate()).padStart(2, "0")}/${String(d.getMonth() + 1).padStart(2, "0")}/${d.getFullYear()}`;
}

/** Banner card hiển thị kiểu Vinamilk (không overlay text, text đã nằm trong ảnh) */
const BannerCard = ({
  banner,
  onClick,
}: {
  banner: PromotionBanner;
  onClick?: () => void;
}) => {
  const inner = (
    <motion.div
      className="relative w-full h-full overflow-hidden rounded-2xl group"
    >
      {/* Image */}
      <img
        src={getImageUrl(banner.image_path) || ""}
        alt={banner.title}
        className="absolute inset-0 w-full h-full object-cover"
      />

      {/* Button theo Type */}
      {banner.type === "modal" ? (
        // Nút Mua ngay (Modal) - Nằm giữa dưới
        <div className="absolute bottom-4 left-1/2 -translate-x-1/2 flex justify-center w-full">
          <span className="inline-flex items-center justify-center bg-[#fffff1] text-[#001c9a] text-sm font-semibold px-5 py-2 rounded-lg border border-[#001c9a] hover:bg-[#e9ecf5] transition-colors duration-200">
            {banner.button_text || "Mua ngay"}
          </span>
        </div>
      ) : (
        // Icon mũi tên (Link) - Góc phải dưới
        <div className="absolute bottom-4 right-4">
          <span className="w-8 h-8 sm:w-10 sm:h-10 bg-[#fdfdf9] border border-[#001c9a] flex items-center justify-center rounded-xl hover:bg-[#e9ecf5] transition-colors duration-200">
            <ArrowUpRight size={20} className="text-[#001c9a]" />
          </span>
        </div>
      )}
    </motion.div>
  );

  if (banner.type === "link" && banner.link_url) {
    return (
      <Link href={banner.link_url} className="block w-full h-full">
        {inner}
      </Link>
    );
  }

  return (
    <button
      onClick={onClick}
      className="block w-full h-full text-left"
    >
      {inner}
    </button>
  );
};

export const MonthlyDealsGrid = ({ banners, modalProducts }: MonthlyDealsGridProps) => {
  const [selectedBanner, setSelectedBanner] = useState<PromotionBanner | null>(null);
  const searchParams = useSearchParams();

  useEffect(() => {
    if (!banners?.length) return;

    const fromQuery = findPromotionBannerFromUrl(
      banners,
      searchParams.get("banner"),
      typeof window !== "undefined" ? window.location.hash.slice(1) : ""
    );

    if (fromQuery) {
      setSelectedBanner(fromQuery);
      requestAnimationFrame(() => {
        document.getElementById("deals")?.scrollIntoView({ behavior: "smooth", block: "start" });
      });
    }
  }, [banners, searchParams]);

  if (!banners || banners.length === 0) return null;

  return (
    <div id="deals" className="scroll-mt-24 mb-32">
      {/* Section Header */}
      <div className="flex items-center gap-4 mb-8">
        <span className="text-[10px] font-black uppercase tracking-[0.35em] text-[#001c9a]/40">01</span>
        <h2 className="text-sm font-black uppercase tracking-[0.25em] text-[#001c9a]">
          Các ưu đãi trong tháng
        </h2>
      </div>

      {/* Bento Grid — 2 cột (Desktop), 1 cột (Mobile) */}
      <div
        className="grid gap-3 sm:gap-4 grid-cols-1 md:grid-cols-2"
      >
        {banners.map((banner) => {
          // Ràng buộc số cột và hàng: Mobile luôn là 1 cột, Desktop tối đa 2 cột
          const isMobile = typeof window !== 'undefined' && window.innerWidth < 768;
          const colSpan = isMobile ? 1 : Math.min(Math.max(banner.col_span ?? 1, 1), 2);
          const rowSpan = Math.min(Math.max(banner.row_span ?? 1, 1), 2);

          // Tính toán Aspect Ratio chuẩn để ảnh không bị crop
          // - 1 cột, 2 hàng -> Vuông 1:1
          // - 1 cột, 1 hàng -> Ngang 2:1
          // - 2 cột, 1 hàng -> Ngang dài 4:1 (hoặc 3:1)
          // - 2 cột, 2 hàng -> Ngang 2:1
          let ratio = "2 / 1"; // Default
          if (colSpan === 1 && rowSpan === 2) ratio = "1 / 1";
          if (colSpan === 1 && rowSpan === 1) ratio = "2 / 1";
          if (colSpan === 2 && rowSpan === 1) ratio = "4 / 1";
          if (colSpan === 2 && rowSpan === 2) ratio = "2 / 1";

          return (
            <div
              key={banner.id}
              style={{
                gridColumn: `span ${colSpan}`,
                gridRow: `span ${rowSpan}`,
                aspectRatio: ratio,
              }}
              className="min-h-0 w-full h-full"
            >
              <BannerCard
                banner={banner}
                onClick={() => setSelectedBanner(banner)}
              />
            </div>
          );
        })}
      </div>

      {/* Modal Popup */}
      <PromotionModal
        banner={selectedBanner}
        products={modalProducts}
        isOpen={!!selectedBanner}
        onClose={() => setSelectedBanner(null)}
      />
    </div>
  );
};
