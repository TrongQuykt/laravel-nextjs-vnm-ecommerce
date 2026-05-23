'use client';

import React from 'react';
import Link from 'next/link';
import { usePathname, useRouter } from 'next/navigation';
import { LogOut } from 'lucide-react';

const navItems = [
  { label: 'Hồ sơ cá nhân', href: '/account/profile' },
  { label: 'Địa chỉ', href: '/account/address' },
  { label: 'Ví voucher', href: '/account/vouchers' },
  { label: 'Đơn hàng', href: '/account/orders' },
  { label: 'Lịch sử quay thưởng', href: '/account/rewards' },
];

export default function AccountSidebar() {
  const pathname = usePathname();
  const router = useRouter();

  const handleLogout = async () => {
    try {
      const token = localStorage.getItem('auth_token');
      if (token) {
        await fetch(`${process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000/api/v1'}/logout`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });
      }
    } catch (e) {
      console.error('Logout error', e);
    } finally {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      router.push('/');
    }
  };

  return (
    <div className="flex flex-col space-y-1 w-full max-w-[280px]">
      {navItems.map((item) => {
        const isActive = pathname === item.href || pathname.startsWith(item.href + '/');
        
        return (
          <Link
            key={item.href}
            href={item.href}
            className={`px-5 py-4 text-[16px] font-bold border-b border-[#002094]/10 transition-colors ${
              isActive 
                ? 'bg-[#E9EDF5] text-[#002094]' 
                : 'text-[#002094] hover:bg-[#002094]/5'
            }`}
          >
            {item.label}
          </Link>
        );
      })}
      
      <button 
        onClick={handleLogout}
        className="flex items-center justify-between px-5 py-4 text-[#D32F2F] font-bold text-[16px] hover:bg-red-50 transition-colors mt-2 text-left"
      >
        <span>Đăng xuất</span>
        <LogOut size={18} />
      </button>
    </div>
  );
}
