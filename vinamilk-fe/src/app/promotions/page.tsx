import { catalogApi } from "@/lib/api";
import FilterSidebar from "@/components/catalog/FilterSidebar";
import Breadcrumbs from "@/components/ui/Breadcrumbs";
import SortSelect from "@/components/catalog/SortSelect";
import ProductGridSection from "@/components/catalog/ProductGridSection";
import ClearFiltersButton from "@/components/catalog/ClearFiltersButton";
import { PromotionsBentoGrid } from "@/components/promotions/PromotionsBentoGrid";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import { Suspense } from "react";
import { Tag } from "lucide-react";

export const revalidate = 60;

export const metadata = {
  title: "Ưu Đãi - Vinamilk",
  description: "Khám phá các sản phẩm đang được giảm giá từ Vinamilk. Mua hàng ngay để không bỏ lỡ ưu đãi hấp dẫn.",
};

export default async function PromotionsPage({
  searchParams,
}: {
  searchParams: Promise<{ [key: string]: string | string[] | undefined }>;
}) {
  const slug = "promotions";
  const resolvedSearchParams = await searchParams;
  const queryString = new URLSearchParams(resolvedSearchParams as any).toString();

  const [bannersData, initialData] = await Promise.all([
    catalogApi.getPromotionsPageBanners(),
    catalogApi.getCollection(slug, ""),
  ]);

  const banners = bannersData?.banners || [];
  const productLines = initialData.product_lines || [];
  const totalProducts = initialData.meta?.total ?? 0;
  const hasFilters = Object.keys(resolvedSearchParams).filter(k => k !== 'sort' && k !== 'page').length > 0;

  return (
    <div className="bg-[#fefef0] min-h-screen">
      <Navbar />

      <main className="pt-28 pb-20">
        <div className="container mx-auto px-6 max-w-7xl">
          <Breadcrumbs items={[{ label: "Ưu Đãi" }]} />

          {/* Page Title */}
          <div className="flex items-end justify-between mb-8">
            <div className="flex flex-col gap-2">
              <div className="flex items-center gap-3">
                <Tag size={28} className="text-[#001c9a]" strokeWidth={1.5} />
                <h1 className="text-4xl md:text-6xl font-serif text-[#001c9a] flex items-start gap-1">
                  Ưu Đãi
                  <span className="text-xl md:text-2xl mt-1 ml-1">{totalProducts}</span>
                </h1>
              </div>
              <p className="text-[#001c9a]/50 text-sm">Các sản phẩm đang được giảm giá, sắp xếp theo % giảm cao nhất</p>
            </div>
          </div>

          {/* Bento Grid Banners */}
          {banners.length > 0 && (
            <div className="mb-12">
              <PromotionsBentoGrid banners={banners} />
            </div>
          )}

          {/* Catalog Section */}
          <div className="flex items-end justify-between mb-8">
            <div className="flex flex-col gap-4">
              {hasFilters && <ClearFiltersButton slug={slug} />}
            </div>
            <SortSelect />
          </div>

          <div className="flex flex-col lg:flex-row gap-12 lg:gap-24 items-start">
            {/* Sidebar */}
            <div className="w-full lg:w-1/4 xl:w-1/5 shrink-0 bg-transparent">
              <FilterSidebar productLines={productLines} />
            </div>

            {/* Grid Area */}
            <div className="flex-grow w-full lg:w-3/4 xl:w-4/5 min-h-[800px]">
              <Suspense
                key={queryString}
                fallback={
                  <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-x-8 gap-y-16">
                    {[1, 2, 3, 4, 5, 6].map(i => (
                      <div key={i} className="space-y-4 animate-pulse">
                        <div className="aspect-square bg-[#001c9a]/5 rounded-[2rem]" />
                        <div className="h-4 bg-[#001c9a]/5 w-1/2 rounded" />
                        <div className="h-4 bg-[#001c9a]/5 w-3/4 rounded" />
                      </div>
                    ))}
                  </div>
                }
              >
                <ProductGridSection slug={slug} queryString={queryString} />
              </Suspense>
            </div>
          </div>
        </div>
      </main>

      <Footer />
    </div>
  );
}
