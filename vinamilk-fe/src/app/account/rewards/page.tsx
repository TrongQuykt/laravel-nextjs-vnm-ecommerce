import React from 'react';
import { Gift } from 'lucide-react';

export default function RewardsPage() {
  return (
    <div className="bg-white rounded-3xl p-8 shadow-sm border border-[#002094]/5 min-h-[400px] flex flex-col items-center justify-center text-center">
      <div className="w-20 h-20 bg-[#FDFCF0] rounded-full flex items-center justify-center mb-6">
        <Gift size={40} className="text-[#002094] opacity-20" />
      </div>
      <h2 className="text-[24px] font-black text-[#002094] mb-2">Lịch sử quay thưởng</h2>
      <p className="text-[#002094]/60 max-w-[320px]">
        Lịch sử nhận quà và trúng thưởng của bạn sẽ được hiển thị tại đây.
      </p>
    </div>
  );
}
