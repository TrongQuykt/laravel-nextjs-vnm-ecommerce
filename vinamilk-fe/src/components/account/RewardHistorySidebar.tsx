"use client";

import React, { useState, useEffect } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { X, Loader2 } from "lucide-react";
import { authFetchApi } from "@/lib/api";

interface RewardHistorySidebarProps {
  isOpen: boolean;
  onClose: () => void;
}

export default function RewardHistorySidebar({ isOpen, onClose }: RewardHistorySidebarProps) {
  const [historyData, setHistoryData] = useState<any[]>([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (isOpen) {
      fetchHistory();
    }
  }, [isOpen]);

  const fetchHistory = async () => {
    setLoading(true);
    try {
      const res = await authFetchApi<any>("/rewards/history");
      if (res && res.data) {
        setHistoryData(res.data);
      }
    } catch (error) {
      console.error(error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <AnimatePresence>
      {isOpen && (
        <>
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 bg-black/40 z-[9998]"
            onClick={onClose}
          />
          <motion.div
            initial={{ x: "100%", opacity: 0 }}
            animate={{ x: 0, opacity: 1 }}
            exit={{ x: "100%", opacity: 0 }}
            transition={{ type: "spring", damping: 28, stiffness: 280 }}
            className="fixed top-3 bottom-3 right-3 z-[9999] flex flex-col overflow-hidden bg-[#fffff1] shadow-2xl rounded-[24px]"
            style={{ width: "440px" }}
          >
            {/* Header */}
            <div className="px-6 py-5 flex items-center justify-between border-b border-blue-100">
              <h2 className="text-[18px] font-black text-[#002094]">Lịch sử điểm thưởng</h2>
              <button
                onClick={onClose}
                className="p-1.5 hover:bg-black/5 rounded-full transition-colors"
              >
                <X size={20} className="text-[#002094]" />
              </button>
            </div>

            {/* Content */}
            <div className="flex-1 overflow-y-auto px-6 py-4 custom-scrollbar">
              {loading ? (
                <div className="flex items-center justify-center py-20 gap-2 text-[#002094]">
                  <Loader2 size={20} className="animate-spin" />
                  <span className="text-[13px] font-bold">Đang tải...</span>
                </div>
              ) : historyData.length === 0 ? (
                <div className="flex flex-col items-center justify-center py-20 text-center gap-3">
                  <p className="text-[13px] font-bold text-gray-400">Chưa có lịch sử điểm thưởng</p>
                </div>
              ) : (
                <div className="space-y-6">
                  {historyData.map((item, index) => (
                    <div key={item.id} className={index !== historyData.length - 1 ? "border-b border-dotted border-blue-200 pb-6" : ""}>
                      <div className="inline-block bg-blue-50 text-[#002094] text-[12px] font-bold px-3 py-1 rounded-md mb-3">
                        {item.date}
                      </div>
                      <div className="flex justify-between items-start mb-4">
                        <p className="text-[14px] font-bold text-[#002094] flex-1 pr-4">
                          {item.title}
                        </p>
                        <span className="text-[15px] font-black text-[#002094] whitespace-nowrap">
                          {item.points > 0 ? "+" : ""}{item.points.toLocaleString()}
                        </span>
                      </div>
                      <p className="text-[13px] font-bold text-[#002094]">
                        Số điểm tích lũy: <span className="font-black">{item.runningTotal.toLocaleString()}</span>
                      </p>
                    </div>
                  ))}
                  <div className="flex justify-center pt-4">
                    <button className="border border-[#002094] text-[#002094] px-6 py-2 rounded text-[13px] font-bold hover:bg-blue-50 transition-colors flex items-center gap-2">
                      Xem thêm
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                  </div>
                </div>
              )}
            </div>
          </motion.div>
        </>
      )}
    </AnimatePresence>
  );
}
