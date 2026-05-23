'use client';

import React from 'react';
import { Product } from '@/types';
import { getImageUrl } from '@/lib/api';
import { ShoppingCart } from 'lucide-react';
import Link from 'next/link';

interface BlogSidebarProductsProps {
  products: Product[];
}

const BlogSidebarProducts: React.FC<BlogSidebarProductsProps> = ({ products }) => {
  const storageUrl = process.env.NEXT_PUBLIC_STORAGE_URL || 'http://localhost:8000/storage';
  
  if (!products || products.length === 0) return null;

  return (
    <div className="mt-10">
      <h4 className="text-[#002094] text-[15px] font-black mb-6 tracking-tight">
        Sản phẩm được gợi ý. Thử ngay!
      </h4>
      <div className="space-y-4">
        {products.map((product) => {
          const mainVariant = product.variants && product.variants.length > 0 ? product.variants[0] : null;
          const price = mainVariant ? mainVariant.price : 0;
          
          // Lấy thông tin dung tích và quy cách (Ưu tiên variant, dự phòng sang product)
          const volume = mainVariant?.volume || '';
          const packing = mainVariant?.packaging_type || '';
          const subtitle = [packing, volume].filter(Boolean).join(' ');
          
          const imageUrl = product.main_image 
            ? (product.main_image.startsWith('http') ? product.main_image : `${storageUrl}/${product.main_image}`)
            : '/images/placeholder-product.jpg';

          return (
            <Link 
              key={product.id} 
              href={`/products/${product.slug}`}
              className="flex items-center p-4 bg-transparent border border-[#002094]/10 rounded-2xl hover:bg-[#002094]/5 transition-all group"
            >
              <div className="w-20 h-20 flex-shrink-0 relative bg-transparent rounded-2xl p-0 overflow-hidden">
                <img 
                  src={imageUrl} 
                  alt={product.name}
                  className="w-full h-full object-contain"
                />
              </div>
              <div className="ml-5 flex-grow min-w-0">
                <h5 className="text-[#002094] text-[15px] font-bold leading-tight line-clamp-2 mb-1 group-hover:underline tracking-tight">
                  {product.name}
                </h5>
                {subtitle && (
                  <p className="text-[#002094]/60 text-[12px] font-medium mb-1 truncate">
                    {subtitle}
                  </p>
                )}
                <p className="text-[#002094] text-[15px] font-black tracking-tight">
                  {price > 0 
                    ? new Intl.NumberFormat('vi-VN').format(price) + 'đ' 
                    : 'Đang cập nhật'
                  }
                </p>
              </div>
              <div className="ml-2 w-10 h-10 flex items-center justify-center rounded-2xl bg-[#002094]/5 text-[#002094] group-hover:bg-[#002094] group-hover:text-white transition-all">
                <ShoppingCart className="w-5 h-5" />
              </div>
            </Link>
          );
        })}
      </div>
    </div>
  );
};

export default BlogSidebarProducts;
