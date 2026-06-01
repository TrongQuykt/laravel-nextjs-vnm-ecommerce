import { catalogApi } from "@/lib/api";
import FilterSidebar from "@/components/catalog/FilterSidebar";
import Breadcrumbs from "@/components/ui/Breadcrumbs";
import SortSelect from "@/components/catalog/SortSelect";
import ProductGridSection from "@/components/catalog/ProductGridSection";
import ClearFiltersButton from "@/components/catalog/ClearFiltersButton";
import { Suspense } from "react";
import { FlashSaleHeader } from "@/components/promotions/FlashSaleHeader";
import Link from "next/link";
import { Ghost } from "lucide-react"; // Using Lucide Ghost as fallback for empty state icon

export default async function FlashSalesPage({
  searchParams,
}: {
  searchParams: Promise<{ [key: string]: string | string[] | undefined }>;
}) {
  const slug = "flash-sales";
  const resolvedSearchParams = await searchParams;
  const queryString = new URLSearchParams(resolvedSearchParams as any).toString();

  // Fetch promotions data to check if there is an active flash sale
  const promotionsData = await catalogApi.getPromotions();
  const flashSale = promotionsData.flash_sale;

  if (!flashSale) {
    // Empty state
    return (
      <div className="bg-[#fefef0] min-h-[80vh] flex flex-col items-center justify-center pt-40">
        <div className="flex flex-col items-center justify-center max-w-md text-center px-4">
          <div className="w-64 h-64 mb-8 text-[#001c9a]/10">
            {/* If there's an actual illustration, it goes here. Using a generic SVG for now */}
            <svg viewBox="0 0 200 200" fill="currentColor">
              <path d="M100 0C44.77 0 0 44.77 0 100s44.77 100 100 100 100-44.77 100-100S155.23 0 100 0zm0 180c-44.11 0-80-35.89-80-80s35.89-80 80-80 80 35.89 80 80-35.89 80-80 80z"/>
              <path d="M100 40c-33.08 0-60 26.92-60 60s26.92 60 60 60 60-26.92 60-60-26.92-60-60-60zm0 100c-22.06 0-40-17.94-40-40s17.94-40 40-40 40 17.94 40 40-17.94 40-40 40z"/>
            </svg>
          </div>
          <h2 className="text-lg md:text-xl font-bold text-[#001c9a] mb-6">
            Không có chương trình Flash Sale nào đang diễn ra.
          </h2>
          <Link
            href="/"
            className="inline-flex items-center justify-center px-8 py-3 border-2 border-[#001c9a] text-[#001c9a] font-bold rounded hover:bg-[#001c9a] hover:text-white transition-colors"
          >
            Trở lại
          </Link>
        </div>
      </div>
    );
  }

  // Fetch layout-level data for the sidebar and products count
  const initialData = await catalogApi.getCollection(slug, "");
  const productLines = initialData.product_lines || [];
  const totalProducts = initialData.meta ? initialData.meta.total : 0;

  const hasFilters = Object.keys(resolvedSearchParams).filter(k => k !== 'sort' && k !== 'page').length > 0;

  return (
    <div className="bg-[#fefef0] min-h-screen pt-28 pb-20">
      <div className="container mx-auto px-6 max-w-7xl">
        <Breadcrumbs
          items={[
            { label: "Flash Sale" }
          ]}
        />

        <FlashSaleHeader data={flashSale} />

        <div className="flex items-end justify-between mb-8 mt-16">
          <div className="flex flex-col gap-4">
            <div className="flex items-center gap-4">
              <h1 className="text-2xl md:text-4xl font-serif text-[#001c9a] first-letter:uppercase flex items-start gap-1">
                Sản phẩm Flash Sale
                <span className="text-lg md:text-xl mt-1">
                  {totalProducts}
                </span>
              </h1>
            </div>
            {hasFilters && <ClearFiltersButton slug={slug} />}
          </div>

          <SortSelect />
        </div>

        <div className="flex flex-col lg:flex-row gap-12 lg:gap-24 items-start">
          {/* Sidebar */}
          <div className="w-full lg:w-1/4 xl:w-1/5 shrink-0 bg-transparent">
            <FilterSidebar productLines={productLines} />
          </div>

          {/* Grid Area - Inside Suspense for independent loading */}
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
    </div>
  );
}
