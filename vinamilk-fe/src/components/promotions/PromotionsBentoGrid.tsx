"use client";

import React from "react";
import { PromotionBanner } from "@/types";
import { getImageUrl } from "@/lib/api";
import Link from "next/link";
import { ArrowUpRight } from "lucide-react";
import { useRouter } from "next/navigation";

interface PromotionsBentoGridProps {
  banners: PromotionBanner[];
}

const BannerCard = ({
  banner,
  size = "small",
}: {
  banner: PromotionBanner;
  size?: "large" | "small";
}) => {
  const router = useRouter();
  const imageUrl = getImageUrl(banner.image_path) || "";

  const handleModalClick = () => {
    router.push(`/khuyen-mai?banner=${banner.id}`);
  };

  const inner = (
    <div
      className={`relative w-full h-full overflow-hidden rounded-2xl group cursor-pointer select-none ${
        size === "large" ? "min-h-[300px] md:min-h-[420px]" : "min-h-[120px] md:min-h-[130px]"
      }`}
    >
      {/* Image */}
      <img
        src={imageUrl}
        alt={banner.title}
        className="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
      />

      {/* Hover overlay */}
      <div className="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-300" />

      {/* CTA Button */}
      {banner.type === "modal" ? (
        <div className="absolute bottom-3 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
          <span className="inline-flex items-center justify-center bg-white text-[#001c9a] text-xs font-bold px-4 py-2 rounded-lg shadow-lg">
            {banner.button_text || "Xem chi tiết"}
          </span>
        </div>
      ) : (
        <div className="absolute bottom-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
          <span className="w-8 h-8 bg-white flex items-center justify-center rounded-xl shadow-lg">
            <ArrowUpRight size={16} className="text-[#001c9a]" />
          </span>
        </div>
      )}
    </div>
  );

  if (banner.type === "link" && banner.link_url) {
    return (
      <Link href={banner.link_url} className="block w-full h-full">
        {inner}
      </Link>
    );
  }

  return (
    <button onClick={handleModalClick} className="block w-full h-full text-left">
      {inner}
    </button>
  );
};

export const PromotionsBentoGrid = ({ banners }: PromotionsBentoGridProps) => {
  if (!banners || banners.length === 0) return null;

  const mainBanner = banners[0];
  const sideBanners = banners.slice(1, 4);
  const extraBanners = banners.slice(4);

  return (
    <div className="w-full">
      {/* Main Bento: 1 large left + up to 3 small right */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
        {/* Large left banner — takes 2 cols */}
        <div className="md:col-span-2">
          <BannerCard banner={mainBanner} size="large" />
        </div>

        {/* Right column: up to 3 stacked small banners */}
        {sideBanners.length > 0 && (
          <div className="flex flex-col gap-3 md:gap-4">
            {sideBanners.map((banner) => (
              <div key={banner.id} className="flex-1">
                <BannerCard banner={banner} size="small" />
              </div>
            ))}
          </div>
        )}
      </div>

      {/* Extra banners in a row below if > 4 total */}
      {extraBanners.length > 0 && (
        <div className="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mt-3 md:mt-4">
          {extraBanners.map((banner) => (
            <div key={banner.id} className="aspect-[4/3]">
              <BannerCard banner={banner} size="small" />
            </div>
          ))}
        </div>
      )}
    </div>
  );
};
