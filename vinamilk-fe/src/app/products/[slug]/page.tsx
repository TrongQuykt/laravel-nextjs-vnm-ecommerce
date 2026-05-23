// src/app/products/[slug]/page.tsx
import { catalogApi } from "@/lib/api";
import ProductDetailView from "@/components/catalog/ProductDetailView";
import { Product } from "@/types";
import { notFound } from "next/navigation";

export default async function ProductPage({
  params,
}: {
  params: Promise<{ slug: string }>;
}) {
  const { slug } = await params;

  try {
    const response = await catalogApi.getProduct(slug);
    const product: Product = response.data;

    if (!product) return notFound();

    // Fetch line products trên server luôn, song song không được vì cần product trước
    let lineProducts: Product[] = [];
    if (product.category?.slug && product.product_line?.slug) {
      try {
        const lineRes = await catalogApi.getLineProducts(
          product.category.slug,
          product.product_line.slug
        );
        lineProducts = lineRes.data || lineRes;
      } catch {
        // Không crash cả trang nếu fetch line thất bại
      }
    }

    return (
      <div className="bg-white min-h-screen">
        <ProductDetailView product={product} lineProducts={lineProducts} />
      </div>
    );
  } catch (error) {
    console.error("Failed to fetch product:", error);
    return notFound();
  }
}