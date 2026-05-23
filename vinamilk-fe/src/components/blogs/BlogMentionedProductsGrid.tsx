'use client';

import React from 'react';
import { Product } from '@/types';
import { getImageUrl } from '@/lib/api';
import { ShoppingCart } from 'lucide-react';
import Link from 'next/link';

interface BlogMentionedProductsGridProps {
  products: Product[];
}

const BlogMentionedProductsGrid: React.FC<BlogMentionedProductsGridProps> = ({ products }) => {
  const storageUrl = process.env.NEXT_PUBLIC_STORAGE_URL || 'http://localhost:8000/storage';

  if (!products || products.length === 0) return null;

  return (
    <div className="my-12 py-8 border-t border-b border-gray-100">
      <h4 className="text-[#002094] text-[15px] font-black mb-8 tracking-tight uppercase tracking-widest opacity-60">
        Sản phẩm được nhắc đến
      </h4>
      <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        {products.map((product) => {
          const mainVariant = product.variants && product.variants.length > 0 ? product.variants[0] : null;
          const price = mainVariant ? mainVariant.price : 0;
          
          // Lấy thông tin dung tích và quy cách
          const volume = mainVariant?.volume || '';
          const packing = mainVariant?.packaging_type || '';
          const subtitle = [volume, packing].filter(Boolean).join(', ');
          
          const imageUrl = product.main_image 
            ? (product.main_image.startsWith('http') ? product.main_image : `${storageUrl}/${product.main_image}`)
            : '/images/placeholder-product.jpg';

          return (
            <Link 
              key={product.id} 
              href={`/products/${product.slug}`}
              className="flex flex-col p-5 bg-transparent border border-[#002094]/10 rounded-2xl hover:bg-[#002094]/5 transition-all group h-full"
            >
              <div className="aspect-square w-full mb-6 relative overflow-hidden bg-transparent rounded-2xl p-0 shadow-none">
                <img 
                  src={imageUrl} 
                  alt={product.name}
                  className="w-full h-full object-contain transition-transform group-hover:scale-105"
                />
              </div>
              <div className="flex flex-col flex-grow">
                <h5 className="text-[#002094] text-[15px] font-bold leading-snug line-clamp-2 mb-2 group-hover:underline h-10 tracking-tight">
                  {product.name}
                </h5>
                {subtitle && (
                  <p className="text-[#002094]/60 text-[12px] font-medium mb-4">
                    {subtitle}
                  </p>
                )}
                <div className="mt-auto flex items-center justify-between">
                  <p className="text-[#002094] text-[16px] font-black tracking-tight">
                    {price > 0 
                      ? new Intl.NumberFormat('vi-VN').format(price) + 'đ' 
                      : 'Đang cập nhật'
                    }
                  </p>
                  <div className="w-10 h-10 flex items-center justify-center rounded-2xl bg-[#002094]/5 text-[#002094] group-hover:bg-[#002094] group-hover:text-white transition-all shadow-sm">
                    <ShoppingCart className="w-5 h-5" />
                  </div>
                </div>
              </div>
            </Link>
          );
        })}
      </div>
    </div>
  );
};

export default BlogMentionedProductsGrid;
