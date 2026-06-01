import React from 'react';
import Link from 'next/link';

export default function CartPage() {
  return (
    <div className="bg-cream min-h-screen pt-40 pb-20 px-10 flex flex-col items-center justify-center text-v-navy">
      <h1 className="text-4xl font-sans font-black mb-6">Giỏ hàng</h1>
      <p className="text-lg opacity-60 mb-10 text-center max-w-md">
        Tính năng giỏ hàng đang được hoàn thiện. <br /> Vui lòng quay lại sau!
      </p>
      <Link 
        href="/" 
        className="px-8 py-3 bg-v-navy text-white rounded-full font-bold hover:bg-v-navy/80 transition-colors"
      >
        Tiếp tục mua sắm
      </Link>
    </div>
  );
}
