"use client";

import React from "react";
import { PromotionsPageBanner } from "@/types";
import { getImageUrl } from "@/lib/api";
import Link from "next/link";
import { ArrowUpRight } from "lucide-react";
import { useRouter } from "next/navigation";

interface PromotionsBentoGridProps {
  banners: PromotionsPageBanner[];
}

const BannerCard = ({
  banner,
  size = "small",
}: {
  banner: PromotionsPageBanner;
  size?: "large" | "small";
}) => {
  const router = useRouter();
  const imageUrl = getImageUrl(banner.image_path) || "";

  const handleModalClick = () => {
    if (banner.promotion_banner_id) {
      router.push(`/khuyen-mai?banner=${banner.promotion_banner_id}`);
      return;
    }
    if (banner.link_url?.startsWith("/khuyen-mai")) {
      router.push(banner.link_url);
      return;
    }
    router.push("/khuyen-mai");
  };

  const inner = (
    <div
      className={`relative w-full h-full overflow-hidden group cursor-pointer select-none ${
        size === "large" ? "min-h-[300px] md:min-h-[420px]" : "min-h-[120px] md:min-h-[130px]"
      }`}
    >
      <img
        src={imageUrl}
        alt={banner.title}
        className="absolute inset-0 w-full h-full object-cover"
      />

      <div className="absolute inset-0 bg-black/0" />

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
    const href =
      banner.link_url.startsWith("/khuyen-mai") && banner.promotion_banner_id
        ? `/khuyen-mai?banner=${banner.promotion_banner_id}`
        : banner.link_url;
    return (
      <Link href={href} className="block w-full h-full">
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

  const heroBanners = banners.filter((b) => b.layout_slot === "hero");
  const sideBanners = banners.filter((b) => b.layout_slot === "side").slice(0, 3);
  const extraBanners = banners.filter((b) => b.layout_slot === "extra");

  const mainBanner = heroBanners[0] ?? banners[0];
  const fallbackSide =
    sideBanners.length > 0
      ? sideBanners
      : banners.filter((b) => b.id !== mainBanner?.id).slice(0, 3);

  if (!mainBanner) return null;

  return (
    <div className="w-full">
      <div className="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
        <div className="md:col-span-2">
          <BannerCard banner={mainBanner} size="large" />
        </div>

        {fallbackSide.length > 0 && (
          <div className="flex flex-col gap-3 md:gap-4">
            {fallbackSide.map((banner) => (
              <div key={banner.id} className="flex-1">
                <BannerCard banner={banner} size="small" />
              </div>
            ))}
          </div>
        )}
      </div>

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
