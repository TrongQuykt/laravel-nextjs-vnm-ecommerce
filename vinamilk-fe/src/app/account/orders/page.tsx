"use client";

import React, { useEffect, useState } from "react";
import { checkoutApi } from "@/lib/api";
import { motion } from "framer-motion";
import Link from "next/link";
import { Package, ChevronRight, ChevronLeft, Clock, CheckCircle, XCircle, Truck } from "lucide-react";

export default function OrdersPage() {
  const [orders, setOrders] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [totalItems, setTotalItems] = useState(0);

  useEffect(() => {
    fetchOrders(currentPage);
  }, [currentPage]);

  const fetchOrders = async (page: number) => {
    setLoading(true);
    try {
      const res = await checkoutApi.getOrders(page);
      setOrders(res.data || []);
      setTotalPages(res.last_page || 1);
      setTotalItems(res.total || 0);
    } catch (e) {
      console.error("Failed to fetch orders", e);
    } finally {
      setLoading(false);
    }
  };

  const getStatusDisplay = (status: string) => {
    switch (status) {
      case "pending":
        return { label: "Chờ tiếp nhận", color: "text-amber-600 bg-amber-50", icon: Clock };
      case "processing":
        return { label: "Chờ đóng gói", color: "text-blue-600 bg-blue-50", icon: Package };
      case "packed":
        return { label: "Đã đóng gói", color: "text-teal-600 bg-teal-50", icon: Package };
      case "shipping":
        return { label: "Đang giao hàng", color: "text-indigo-600 bg-indigo-50", icon: Truck };
      case "completed":
        return { label: "Đã hoàn tất", color: "text-green-600 bg-green-50", icon: CheckCircle };
      case "cancelled":
        return { label: "Đã hủy", color: "text-red-600 bg-red-50", icon: XCircle };
      default:
        return { label: status, color: "text-gray-600 bg-gray-50", icon: Clock };
    }
  };

  if (loading) {
    return (
      <div className="space-y-4">
        {[1, 2, 3].map((i) => (
          <div key={i} className="h-32 bg-gray-100 animate-pulse rounded-xl" />
        ))}
      </div>
    );
  }

  if (orders.length === 0) {
    return (
      <div className="bg-white rounded-3xl p-12 text-center border border-[#002094]/5 shadow-sm">
        <div className="w-20 h-20 bg-[#FDFCF0] rounded-full flex items-center justify-center mx-auto mb-6">
          <Package size={40} className="text-[#002094]/20" />
        </div>
        <h2 className="text-[24px] font-black text-[#002094] mb-2">Bạn chưa có đơn hàng nào</h2>
        <p className="text-gray-500 mb-8">Hãy mua sắm để tích điểm và nhận quà nhé!</p>
        <Link 
          href="/" 
          className="inline-block px-8 py-4 bg-[#002094] text-white font-black rounded-full hover:bg-[#001a7a] transition-all"
        >
          Mua sắm ngay
        </Link>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between mb-2">
        <h2 className="text-[28px] font-black text-[#002094]">Đơn hàng của tôi</h2>
        <span className="text-[14px] font-bold text-gray-400">{totalItems} đơn hàng</span>
      </div>

      <div className="space-y-4">
        {orders.map((order, idx) => {
          const status = getStatusDisplay(order.status);
          const StatusIcon = status.icon;

          return (
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: idx * 0.1 }}
              key={order.id}
            >
              <Link 
                href={`/account/orders/${order.order_number}`}
                className="group block bg-transparent border border-[#002094]/10 rounded-2xl p-6 hover:border-[#002094]/30 transition-all relative overflow-hidden"
              >
                {/* Status Bar */}
                <div className={`absolute top-0 left-0 w-1.5 h-full ${status.color.split(' ')[0].replace('text', 'bg')}`} />
                
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-6">
                  <div className="space-y-2">
                    <div className="flex items-center gap-3">
                      <span className="text-[18px] font-black text-[#002094]">#{order.order_number}</span>
                      <div className={`flex items-center gap-1.5 px-3 py-1 rounded-full text-[12px] font-black uppercase tracking-tight ${status.color}`}>
                        <StatusIcon size={14} />
                        {status.label}
                      </div>
                    </div>
                    <div className="flex items-center gap-4 text-[13px] text-gray-400 font-bold">
                      <span>{new Date(order.created_at).toLocaleDateString('vi-VN')}</span>
                      <span>•</span>
                      <span>{order.items_count || 0} sản phẩm</span>
                    </div>
                  </div>

                  <div className="flex items-center justify-between md:justify-end gap-8 border-t md:border-0 pt-4 md:pt-0">
                    <div className="text-right">
                      <p className="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Tổng thanh toán</p>
                      <p className="text-[20px] font-black text-[#002094]">
                        {Number(order.total_amount).toLocaleString()}đ
                      </p>
                    </div>
                    <div className="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-[#002094] group-hover:text-white transition-all">
                      <ChevronRight size={20} />
                    </div>
                  </div>
                </div>
              </Link>
            </motion.div>
          );
        })}
      </div>

      {totalPages > 1 && (
        <div className="flex items-center justify-center gap-2 mt-8">
          <button
            onClick={() => setCurrentPage(p => Math.max(1, p - 1))}
            disabled={currentPage === 1}
            className="w-10 h-10 rounded-full flex items-center justify-center border border-gray-200 text-gray-500 hover:border-[#002094] hover:text-[#002094] disabled:opacity-50 disabled:hover:border-gray-200 disabled:hover:text-gray-500 transition-all"
          >
            <ChevronLeft size={20} />
          </button>
          
          {Array.from({ length: totalPages }, (_, i) => i + 1).map(page => (
            <button
              key={page}
              onClick={() => setCurrentPage(page)}
              className={`w-10 h-10 rounded-full flex items-center justify-center font-bold transition-all ${
                currentPage === page 
                  ? "bg-[#002094] text-white border-transparent shadow-md" 
                  : "border border-gray-200 text-gray-500 hover:border-[#002094] hover:text-[#002094]"
              }`}
            >
              {page}
            </button>
          ))}

          <button
            onClick={() => setCurrentPage(p => Math.min(totalPages, p + 1))}
            disabled={currentPage === totalPages}
            className="w-10 h-10 rounded-full flex items-center justify-center border border-gray-200 text-gray-500 hover:border-[#002094] hover:text-[#002094] disabled:opacity-50 disabled:hover:border-gray-200 disabled:hover:text-gray-500 transition-all"
          >
            <ChevronRight size={20} />
          </button>
        </div>
      )}
    </div>
  );
}
