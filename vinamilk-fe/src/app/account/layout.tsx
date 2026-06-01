import React from 'react';
import Link from 'next/link';
import AccountSidebar from '@/components/account/AccountSidebar';

export default function AccountLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <div className="min-h-screen bg-[#FDFCF0] pt-30 pb-20">
      <div className="max-w-[1200px] mx-auto px-4 md:px-8">
        {/* Breadcrumbs */}
        <div className="flex items-center space-x-2 text-[13px] font-bold text-[#002094] mb-8">
          <Link href="/" className="hover:underline opacity-70">
            Trang chủ
          </Link>
          <span className="opacity-70">&gt;</span>
          <span>Tài Khoản</span>
        </div>

        <h1 className="text-[48px] md:text-[56px] font-black text-[#002094] tracking-tighter mb-12">
          Tài khoản
        </h1>

        <div className="flex flex-col md:flex-row gap-8 lg:gap-16">
          <aside className="w-full md:w-[280px] flex-shrink-0">
            <AccountSidebar />
          </aside>

          <main className="flex-grow min-w-0">
            {children}
          </main>
        </div>
      </div>
    </div>
  );
}
