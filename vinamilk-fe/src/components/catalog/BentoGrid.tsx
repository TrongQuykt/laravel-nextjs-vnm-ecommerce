"use client";
 
import { Product } from "@/types";
import ProductCard from "./ProductCard";
 
interface BentoGridProps {
  products: Product[];
}
 
export default function BentoGrid({ products }: BentoGridProps) {
  return (
    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-x-8 gap-y-12">
      {products.map((product) => (
        <ProductCard 
          key={product.id} 
          product={product} 
          isFeatured={true}
        />
      ))}
    </div>
  );
}
