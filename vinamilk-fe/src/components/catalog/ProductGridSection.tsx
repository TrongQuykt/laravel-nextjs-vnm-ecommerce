import { catalogApi } from "@/lib/api";
import ProductCard from "./ProductCard";
import { PaginatedResponse, Product } from "@/types";

interface ProductGridSectionProps {
  slug: string;
  queryString: string;
}

export default async function ProductGridSection({ slug, queryString }: ProductGridSectionProps) {
  const response: PaginatedResponse<Product> = await catalogApi.getCollection(slug, queryString);
  const products = response.data;

  if (products.length === 0) {
    return (
      <div className="py-40 text-center bg-transparent">
        <p className="text-v-navy/20 font-serif text-2xl">Không tìm thấy sản phẩm phù hợp</p>
      </div>
    );
  }

  return (
    <>
      <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-x-8 gap-y-16">
        {products.map((product) => (
          <ProductCard key={product.id} product={product} />
        ))}
      </div>
      
      {/* Pagination */}
      {response.meta.last_page > 1 && (
        <div className="mt-20 flex justify-center">
            <button className="px-10 py-5 bg-white/50 backdrop-blur-md border border-v-navy/10 text-v-navy rounded-full font-bold uppercase tracking-widest text-[11px]  shadow-sm hover:shadow-sm group">
                Xem thêm sản phẩm
                <span className="ml-2 inline-block group-hover:translate-y-1 transition-transform">↓</span>
            </button>
        </div>
      )}
    </>
  );
}
