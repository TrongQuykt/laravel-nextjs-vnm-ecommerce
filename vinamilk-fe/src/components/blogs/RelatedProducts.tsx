'use client';

import React, { useRef } from 'react';
import { Product } from '@/types';
import Image from 'next/image';
import { ChevronLeft, ChevronRight, ShoppingCart } from 'lucide-react';

interface RelatedProductsProps {
  products: Product[];
}

const RelatedProducts: React.FC<RelatedProductsProps> = ({ products }) => {
  const scrollRef = useRef<HTMLDivElement>(null);

  if (!products || products.length === 0) return null;

  const scroll = (direction: 'left' | 'right') => {
    if (scrollRef.current) {
      const { scrollLeft, clientWidth } = scrollRef.current;
      const scrollTo = direction === 'left' ? scrollLeft - clientWidth : scrollLeft + clientWidth;
      scrollRef.current.scrollTo({ left: scrollTo, behavior: 'smooth' });
    }
  };

  return (
    <div className="bg-[#fff9f0] rounded-2xl p-8 my-16">
      <div className="flex items-center justify-between mb-8">
        <h3 className="text-2xl md:text-3xl font-sans font-black text-[#001c9a]">
          Sản phẩm được nhắc đến
        </h3>
        
        <div className="flex space-x-2">
          <button 
            onClick={() => scroll('left')}
            className="w-10 h-10 rounded-full border border-[#001c9a] flex items-center justify-center text-[#001c9a] hover:bg-[#001c9a] hover:text-white transition-all"
          >
            <ChevronLeft className="w-6 h-6" />
          </button>
          <button 
            onClick={() => scroll('right')}
            className="w-10 h-10 rounded-full border border-[#001c9a] flex items-center justify-center text-[#001c9a] hover:bg-[#001c9a] hover:text-white transition-all"
          >
            <ChevronRight className="w-6 h-6" />
          </button>
        </div>
      </div>

      <div 
        ref={scrollRef}
        className="flex space-x-4 md:space-x-6 overflow-x-auto scrollbar-hide snap-x snap-mandatory"
      >
        {products.map((product) => {
          const displayPrice = product.home_featured_variant?.price || 0;
          const displayVolume = product.home_featured_variant?.volume || "";
          const storageUrl = process.env.NEXT_PUBLIC_STORAGE_URL || 'http://localhost:8000/storage';
          const imageUrl = product.main_image 
            ? `${storageUrl}/${product.main_image}`
            : '/images/placeholder-product.jpg';

          return (
            <div 
              key={product.id} 
              className="min-w-[200px] md:min-w-[280px] bg-white rounded-xl p-4 md:p-6 snap-start flex flex-col group shadow-sm hover:shadow-md transition-shadow"
            >
              <div className="relative aspect-square mb-4">
                <Image
                  src={imageUrl}
                  alt={product.name}
                  fill
                  className="object-contain transition-transform group-hover:scale-110"
                />
              </div>
              
              <h4 className="text-[#001c9a] font-bold text-sm md:text-base line-clamp-2 mb-2 flex-grow">
                {product.name}
              </h4>
              
              <p className="text-gray-500 text-xs md:text-sm mb-4">
                {displayVolume}
              </p>
              
              <div className="flex items-center justify-between mt-auto">
                <span className="text-[#001c9a] font-black text-sm md:text-lg">
                  {new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(displayPrice)}
                </span>
                
                <button className="w-8 h-8 md:w-10 md:h-10 rounded-full border-2 border-[#001c9a] flex items-center justify-center text-[#001c9a] hover:bg-[#001c9a] hover:text-white transition-all">
                  <ShoppingCart className="w-4 h-4 md:w-5 md:h-5" />
                </button>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
};

export default RelatedProducts;
