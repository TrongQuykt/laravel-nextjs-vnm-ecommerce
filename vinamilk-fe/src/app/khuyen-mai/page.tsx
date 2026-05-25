import React from "react";
import { catalogApi, getImageUrl } from "@/lib/api";
import { SidebarNavigator } from "@/components/promotions/SidebarNavigator";
import { MonthlyDealsGrid } from "@/components/promotions/MonthlyDealsGrid";
import { FlashSaleSection } from "@/components/promotions/FlashSaleSection";
import { PromotionTerms } from "@/components/promotions/PromotionTerms";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import Link from "next/link";
import { Suspense } from "react";

export const revalidate = 60;

export default async function KhuyenMaiPage() {
  const data = await catalogApi.getPromotions();
  const settings = data.settings;

  return (
    <div className="bg-cream min-h-screen">
      <Navbar />

      <main className="pt-26">
        {/* Hero Banner */}
        {settings?.hero_image_path && (
          <div className="w-full overflow-hidden">
            <div className="relative w-full">
              <img
                src={settings.hero_image_path}
                alt={settings.hero_title || "Khuyến mãi Vinamilk"}
                className="w-full object-cover max-h-[70vh]"
              />
              {(settings.hero_title || settings.hero_subtitle) && (
                <div className="absolute inset-0 bg-gradient-to-t from-black/50 via-black/10 to-transparent flex flex-col items-start justify-end p-8 md:p-16">
                  {settings.hero_title && (
                    <h1 className="text-3xl md:text-5xl lg:text-6xl font-sans font-black text-white uppercase tracking-tighter mb-3 drop-shadow-xl max-w-2xl leading-none">
                      {settings.hero_title}
                    </h1>
                  )}
                  {settings.hero_subtitle && (
                    <p className="text-white/80 text-base md:text-lg font-medium max-w-xl drop-shadow">
                      {settings.hero_subtitle}
                    </p>
                  )}
                </div>
              )}
              {settings.hero_link_url && (
                <Link href={settings.hero_link_url} className="absolute inset-0 z-10">
                  <span className="sr-only">Xem chi tiết</span>
                </Link>
              )}
            </div>
          </div>
        )}

        {/* Main Content */}
        <div className="container mx-auto px-4 md:px-6 max-w-7xl py-16 md:py-24">
          <div className="flex gap-8 lg:gap-12">
            {/* Sidebar */}
            <SidebarNavigator />

            {/* Content */}
            <div className="flex-1 min-w-0">
              <Suspense fallback={null}>
                <MonthlyDealsGrid
                  banners={data.banners}
                  modalProducts={data.modal_products}
                />
              </Suspense>

              <div className="h-px w-full mb-20" />

              <FlashSaleSection
                data={data.flash_sale}
                products={data.flash_sale_products}
              />

              <div className="h-px w-full mb-20" />

              <PromotionTerms terms={data.terms} />
            </div>
          </div>
        </div>
      </main>

      <Footer />
    </div>
  );
}
