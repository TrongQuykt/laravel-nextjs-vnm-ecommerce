'use client';

import React, { useState, useEffect } from 'react';
import { Plus } from 'lucide-react';
import AddressSidebar from '@/components/account/AddressSidebar';

export default function AddressPage() {
  const [isSidebarOpen, setIsSidebarOpen] = useState(false);
  const [addresses, setAddresses] = useState<any[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  const fetchAddresses = async () => {
    setIsLoading(true);
    try {
      const token = localStorage.getItem('auth_token');
      if (!token) return;
      
      const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000/api/v1'}/user/addresses`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      });
      const data = await res.json();
      if (res.ok) {
        setAddresses(data.data || []);
      }
    } catch (err) {
      console.error('Lỗi khi tải danh sách địa chỉ:', err);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchAddresses();
  }, []);

  const handleDelete = async (id: number) => {
    if (!confirm('Bạn có chắc chắn muốn xóa địa chỉ này?')) return;
    
    try {
      const token = localStorage.getItem('auth_token');
      await fetch(`${process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000/api/v1'}/user/addresses/${id}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      });
      fetchAddresses();
    } catch (err) {
      console.error('Lỗi xóa địa chỉ:', err);
    }
  };

  return (
    <div className="w-full">
      <div className="flex flex-col md:flex-row md:items-center justify-between mb-6 pb-4 border-b border-[#002094]/10">
        <h2 className="text-[20px] font-bold text-[#002094] mb-4 md:mb-0">
          Sổ địa chỉ ({addresses.length})
        </h2>
        <button 
          onClick={() => setIsSidebarOpen(true)}
          className="flex items-center justify-center space-x-2 border border-[#002094] text-[#002094] px-6 py-2 rounded-sm font-bold hover:bg-[#002094]/5 transition-colors"
        >
          <Plus size={18} />
          <span>Thêm địa chỉ</span>
        </button>
      </div>

      {isLoading ? (
        <div className="w-full h-64 flex items-center justify-center">
          <div className="w-8 h-8 border-4 border-[#002094]/20 border-t-[#002094] rounded-full animate-spin" />
        </div>
      ) : addresses.length === 0 ? (
        <div className="bg-[#F2F5FA] rounded-xl flex flex-col items-center justify-center py-20 px-4 text-center">
          {/* Subtle placeholder image similar to the user screenshot */}
          <div className="mb-6 opacity-30 pointer-events-none select-none">
            <img src="/assets/images/empty-address.png" alt="Empty Address" className="w-48 h-auto" onError={(e) => e.currentTarget.style.display = 'none'} />
            {/* Fallback pattern if image is missing */}
            <div className="flex gap-4 opacity-50 justify-center">
               <div className="w-12 h-12 rounded-full bg-[#002094]/20" />
               <div className="w-12 h-12 rounded-lg bg-[#002094]/20" />
               <div className="w-12 h-12 rounded-tl-full rounded-br-full bg-[#002094]/20" />
            </div>
          </div>
          <p className="text-[18px] font-bold text-[#002094]">
            Sổ địa chỉ mới tinh, chờ thông tin mở hàng!
          </p>
        </div>
      ) : (
        <div className="space-y-4">
          {addresses.map(address => (
            <div key={address.id} className="border border-[#002094]/20 p-6 flex flex-col md:flex-row md:items-start justify-between bg-white relative">
              {address.is_default && (
                <div className="absolute top-0 right-0 bg-[#E0FB9B] text-[#002094] text-[12px] font-bold px-3 py-1 rounded-bl-lg">
                  Mặc định
                </div>
              )}
              <div className="space-y-2">
                <div className="flex items-center space-x-4">
                  <h3 className="text-[16px] font-bold text-[#002094] uppercase">
                    {address.last_name} {address.first_name}
                  </h3>
                  <span className="text-[15px] text-[#002094] opacity-70">
                    {address.phone}
                  </span>
                </div>
                <p className="text-[#002094] text-[15px]">
                  {address.detail}
                </p>
                <p className="text-[#002094] text-[15px]">
                  {address.ward}, {address.district}, {address.city}
                </p>
              </div>
              
              <div className="mt-4 md:mt-0 flex items-center space-x-6 text-[14px] font-bold text-[#002094]">
                {/* For future implementation: Edit button */}
                <button className="hover:underline">Chỉnh sửa</button>
                <button onClick={() => handleDelete(address.id)} className="hover:underline text-red-500">Xóa</button>
              </div>
            </div>
          ))}
        </div>
      )}

      <div className="mt-6 text-[12px] font-bold text-[#002094]/60">
        Mách bạn: Lưu trước các địa chỉ nhận hàng sẽ giúp bạn tiết kiệm thời gian khi mua sắm
      </div>

      <AddressSidebar 
        isOpen={isSidebarOpen}
        onClose={() => setIsSidebarOpen(false)}
        onSuccess={fetchAddresses}
      />
    </div>
  );
}
