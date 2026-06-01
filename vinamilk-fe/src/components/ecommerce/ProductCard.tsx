'use client';

import React from 'react';
import Image from 'next/image';
import { ShoppingCart, Plus } from 'lucide-react';

interface ProductCardProps {
  name: string;
  price: string;
  category: string;
  image?: string;
}

const ProductCard = ({ name, price, category }: ProductCardProps) => {
  return (
    <div className="group bg-white rounded-3xl p-4 border border-gray-100/50 hover:border-v-blue/10 hover:shadow-2xl transition-all duration-500">
      {/* Product Image Wrapper */}
      <div className="relative aspect-square bg-cream rounded-2xl overflow-hidden mb-5">
        <div className="absolute inset-0 flex items-center justify-center text-v-blue/5 font-black text-6xl rotate-12 select-none">
          VNM
        </div>
        
        {/* Quick add button (overlay) */}
        <button className="absolute bottom-4 right-4 bg-white text-v-blue p-3 rounded-xl shadow-xl opacity-0 group-hover:opacity-100 translate-y-2 group-hover:translate-y-0 transition-all duration-300 hover:bg-v-blue hover:text-white">
          <Plus className="w-5 h-5" />
        </button>
      </div>

      {/* Product Info */}
      <div className="space-y-2 px-1">
        <p className="text-[10px] uppercase tracking-widest font-black text-fresh-green bg-v-blue inline-block px-2 py-0.5 rounded">
            {category}
        </p>
        <h3 className="font-bold text-gray-800 line-clamp-2 leading-tight group-hover:text-v-blue transition-colors h-10">
          {name}
        </h3>
        
        <div className="flex items-center justify-between pt-2">
          <p className="text-xl font-black text-v-blue">
            {price} <span className="text-xs font-normal">đ</span>
          </p>
          <button className="text-v-blue/40 group-hover:text-v-blue transition-colors">
            <ShoppingCart className="w-5 h-5" />
          </button>
        </div>
      </div>
    </div>
  );
};

export default ProductCard;
