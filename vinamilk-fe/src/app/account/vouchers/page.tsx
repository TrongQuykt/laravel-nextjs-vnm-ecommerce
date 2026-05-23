'use client';

import React, { useState, useEffect } from 'react';
import { Ticket, Gift, Loader2, Calendar, ShieldCheck, ArrowRight, ShoppingBag } from 'lucide-react';
import { authFetchApi, getImageUrl } from '@/lib/api';
import Link from 'next/link';

interface PersonalReward {
  id: number;
  reward_id: number;
  points_spent: number;
  created_at: string;
  reward: {
    id: number;
    name: string;
    type: 'voucher' | 'gift';
    description: string;
    image: string | null;
    points_required: number;
  };
}

export default function VouchersPage() {
  const [rewards, setRewards] = useState<PersonalReward[]>([]);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState<'voucher' | 'gift'>('voucher');

  useEffect(() => {
    fetchMyRewards();
  }, []);

  const fetchMyRewards = async () => {
    try {
      const res = await authFetchApi<any>('/rewards/my-rewards');
      if (res && res.success) {
        setRewards(res.data || []);
      }
    } catch (e) {
      console.error("Failed to fetch my rewards", e);
    } finally {
      setLoading(false);
    }
  };

  const filteredRewards = rewards.filter(r => r.reward && r.reward.type === activeTab);

  const formatExpiry = (dateStr: string) => {
    const d = new Date(dateStr);
    // Vouchers có hạn dùng 30 ngày kể từ lúc quy đổi
    d.setDate(d.getDate() + 30);
    return d.toLocaleDateString('vi-VN', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    });
  };

  const formatDiscount = (name: string) => {
    const matches = name.match(/(\d+)K/i);
    if (matches) {
      return `${matches[1]}K`;
    }
    return 'Ưu Đãi';
  };

  return (
    <div className="bg-[#fffff1] rounded-3xl p-6 md:p-8 shadow-xl border border-[#002094]/5 min-h-[550px] flex flex-col transition-all">
      {/* Title Header */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
          <h2 className="text-[28px] font-black italic text-[#002094] leading-tight">Ví Voucher & Quà Tặng</h2>
          <p className="text-[12px] text-[#002094]/60 mt-1 font-medium">Nơi lưu trữ các đặc quyền quy đổi từ điểm Vinamilk Rewards của riêng bạn.</p>
        </div>
        <div className="flex items-center gap-2 bg-[#002094]/5 px-4 py-2 rounded-full self-start md:self-auto">
          <ShieldCheck size={16} className="text-[#002094]" />
          <span className="text-[11px] font-bold text-[#002094] uppercase tracking-wider">Đã xác minh tài khoản</span>
        </div>
      </div>

      {/* Modern Premium Tabs */}
      <div className="flex bg-[#002094]/5 p-1 rounded-xl mb-8 self-start w-full md:w-auto">
        <button
          onClick={() => setActiveTab('voucher')}
          className={`flex-1 md:flex-initial flex items-center justify-center gap-2 px-6 py-2.5 rounded-lg text-[13px] font-bold transition-all ${
            activeTab === 'voucher'
              ? 'bg-[#002094] text-white shadow-md'
              : 'text-[#002094]/60 hover:text-[#002094]'
          }`}
        >
          <Ticket size={16} />
          <span>Voucher của tôi ({rewards.filter(r => r.reward?.type === 'voucher').length})</span>
        </button>
        <button
          onClick={() => setActiveTab('gift')}
          className={`flex-1 md:flex-initial flex items-center justify-center gap-2 px-6 py-2.5 rounded-lg text-[13px] font-bold transition-all ${
            activeTab === 'gift'
              ? 'bg-[#002094] text-white shadow-md'
              : 'text-[#002094]/60 hover:text-[#002094]'
          }`}
        >
          <Gift size={16} />
          <span>Quà tặng vật phẩm ({rewards.filter(r => r.reward?.type === 'gift').length})</span>
        </button>
      </div>

      {/* Content Area */}
      {loading ? (
        <div className="flex-1 flex flex-col items-center justify-center py-20 gap-3 text-[#002094]">
          <Loader2 size={32} className="animate-spin opacity-80" />
          <span className="text-[13px] font-bold tracking-wide uppercase opacity-75">Đang đồng bộ ví...</span>
        </div>
      ) : filteredRewards.length === 0 ? (
        /* Premium Empty State */
        <div className="flex-1 flex flex-col items-center justify-center py-16 text-center animate-fade-in">
          <div className="w-24 h-24 bg-[#002094]/5 rounded-full flex items-center justify-center mb-6 border border-[#002094]/10 relative group">
            <div className="absolute inset-0 bg-[#002094]/5 rounded-full scale-100 group-hover:scale-110 transition-transform duration-500"></div>
            {activeTab === 'voucher' ? (
              <Ticket size={40} className="text-[#002094] opacity-40 relative z-10 transition-transform duration-500 group-hover:rotate-12" />
            ) : (
              <Gift size={40} className="text-[#002094] opacity-40 relative z-10 transition-transform duration-500 group-hover:scale-105" />
            )}
          </div>
          <h3 className="text-[18px] font-bold text-[#002094] mb-2">Ví trống</h3>
          <p className="text-[12px] text-[#002094]/60 max-w-[340px] leading-relaxed mb-6 font-medium">
            Bạn chưa quy đổi {activeTab === 'voucher' ? 'voucher giảm giá' : 'quà tặng vật phẩm'} nào từ điểm tích lũy của mình.
          </p>
          <Link
            href="/vinamilk-rewards"
            className="flex items-center gap-2 bg-[#002094] hover:bg-blue-900 text-white font-bold text-[12px] px-6 py-3 rounded-full uppercase tracking-wider shadow-lg hover:shadow-xl transition-all"
          >
            <span>Quy đổi ngay</span>
            <ArrowRight size={14} />
          </Link>
        </div>
      ) : (
        /* Reward Cards Grid */
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 animate-fade-in">
          {filteredRewards.map((item) => {
            const reward = item.reward;
            if (activeTab === 'voucher') {
              // VOUCHER CARD
              return (
                <div key={item.id} className="relative bg-white border border-[#002094]/10 rounded-2xl overflow-hidden hover:shadow-lg transition-all flex h-[140px] group">
                  {/* Left segment - Ticket edge style */}
                  <div className="w-[100px] bg-[#fffde7] flex flex-col items-center justify-center border-r border-dashed border-[#002094]/15 relative select-none shrink-0">
                    {/* Ticket notch top & bottom */}
                    <div className="absolute top-0 right-0 w-4 h-2 bg-[#fffff1] rounded-b-full translate-x-1/2 -translate-y-px border-b border-l border-r border-[#002094]/10"></div>
                    <div className="absolute bottom-0 right-0 w-4 h-2 bg-[#fffff1] rounded-t-full translate-x-1/2 translate-y-px border-t border-l border-r border-[#002094]/10"></div>
                    
                    <span className="text-[8px] font-black text-[#002094]/50 uppercase tracking-widest leading-none mb-1">GIẢM GIÁ</span>
                    <span className="text-[28px] font-black text-[#002094] leading-none mb-2">{formatDiscount(reward.name)}</span>
                    <span className="px-2 py-0.5 bg-[#002094]/5 text-[9px] font-black text-[#002094] rounded-full">Ví cá nhân</span>
                  </div>

                  {/* Right segment - Info */}
                  <div className="flex-1 p-4 flex flex-col justify-between min-w-0">
                    <div className="min-w-0">
                      <h4 className="font-bold text-[14px] text-[#002094] line-clamp-1 leading-snug group-hover:text-blue-900 transition-colors">{reward.name}</h4>
                      <p className="text-[10px] text-gray-500 mt-1.5 leading-relaxed line-clamp-2 pr-2">{reward.description}</p>
                    </div>
                    <div className="flex items-end justify-between mt-2 pt-2 border-t border-gray-50">
                      <div className="flex items-center gap-1.5 text-[10px] text-[#002094]/60 font-semibold">
                        <Calendar size={12} className="text-[#002094]" />
                        <span>HSD: {formatExpiry(item.created_at)}</span>
                      </div>
                    </div>
                  </div>
                </div>
              );
            } else {
              // GIFT CARD
              return (
                <div key={item.id} className="relative bg-white border border-[#002094]/10 rounded-2xl overflow-hidden hover:shadow-lg transition-all flex h-[140px] group">
                  {/* Left segment - Image */}
                  <div className="w-[110px] bg-white flex items-center justify-center p-3 relative shrink-0 border-r border-gray-100">
                    {reward.image ? (
                      <img
                        src={getImageUrl(reward.image) ?? reward.image}
                        alt={reward.name}
                        className="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105"
                      />
                    ) : (
                      <Gift size={32} className="text-[#002094] opacity-20" />
                    )}
                    <span className="absolute bottom-2 left-2 bg-green-500/10 text-green-700 text-[8px] font-black px-1.5 py-0.5 rounded-md uppercase tracking-wider">Mức Quà</span>
                  </div>

                  {/* Right segment - Info */}
                  <div className="flex-1 p-4 flex flex-col justify-between min-w-0">
                    <div className="min-w-0">
                      <h4 className="font-bold text-[14px] text-[#002094] line-clamp-1 leading-snug group-hover:text-blue-900 transition-colors">{reward.name}</h4>
                      <p className="text-[10px] text-gray-500 mt-1.5 leading-relaxed line-clamp-2 pr-2">{reward.description}</p>
                    </div>
                    <div className="flex items-end justify-between mt-2 pt-2 border-t border-gray-50">
                      <div className="flex items-center gap-1.5 text-[10px] text-[#002094]/60 font-semibold">
                        <Calendar size={12} className="text-[#002094]" />
                        <span>Quy đổi ngày: {new Date(item.created_at).toLocaleDateString('vi-VN')}</span>
                      </div>
                    </div>
                  </div>
                </div>
              );
            }
          })}
        </div>
      )}

      {/* Styled Micro-Animations Keyframes */}
      <style jsx global>{`
        @keyframes fade-in {
          from { opacity: 0; transform: translateY(8px); }
          to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
          animation: fade-in 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
      `}</style>
    </div>
  );
}
