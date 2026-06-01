"use client";

import React from "react";
import { motion, AnimatePresence } from "framer-motion";
import { X, Trash2, Minus, Plus, TicketPercent } from "lucide-react";
import { useCart, MarketingReward } from "@/context/CartContext";
import Link from "next/link";
import { getImageUrl } from "@/lib/api";
import GiftSelectionPanel from "./GiftSelectionPanel";
import VoucherSidebar from "./VoucherSidebar";

const getVnd = (price: number) =>
  price > 0 && price < 10000 ? price * 1000 : price;

export default function CartSidebar() {
  const {
    isSidebarOpen,
    setIsSidebarOpen,
    isVoucherSidebarOpen,
    setIsVoucherSidebarOpen,
    items,
    updateQuantity,
    removeItem,
    subtotal,
    totalItems,
    totalBasePrice,
    totalProductDiscount,
    rewards,
    isEvaluating,
    selectReward,
    appliedVoucher,
    voucherDiscount,
    removeVoucher,
  } = useCart();

  const [selectedRewardToChange, setSelectedRewardToChange] =
    React.useState<MarketingReward | null>(null);

  return (
    <AnimatePresence>
      {isSidebarOpen && (
        <>
          {/* Backdrop */}
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            onClick={() => {
              if (selectedRewardToChange || isVoucherSidebarOpen) {
                setSelectedRewardToChange(null);
                setIsVoucherSidebarOpen(false);
              } else {
                setIsSidebarOpen(false);
              }
            }}
            className="fixed inset-0 bg-black/40 z-[1000]"
          />

          {/* Voucher Sidebar (renders left of cart) */}
          <VoucherSidebar />

          {/* Cart Sidebar */}
          <motion.div
            initial={{ x: "100%" }}
            animate={{ x: 0 }}
            exit={{ x: "100%" }}
            transition={{ type: "spring", damping: 28, stiffness: 280 }}
            className="fixed top-3 right-3 bottom-3 w-[480px] z-[1001] flex flex-col"
            style={{
              backgroundColor: "transparent",
              pointerEvents: "none"
            }}
          >
            {/* Gift Selection Panel */}
            <GiftSelectionPanel
              isOpen={!!selectedRewardToChange}
              reward={selectedRewardToChange || ({} as any)}
              onClose={() => setSelectedRewardToChange(null)}
              onApply={(selectionIds) => {
                if (selectedRewardToChange?.rule_id) {
                  selectReward(selectedRewardToChange.rule_id, selectionIds);
                }
                setSelectedRewardToChange(null);
              }}
            />

            {/* Main Content Div */}
            <div
              className="flex-grow flex flex-col overflow-hidden pointer-events-auto shadow-2xl"
              style={{
                backgroundColor: "#fffff1",
                borderRadius: "24px"
              }}
            >

              {/* Header */}
              <div className="px-6 pt-6 pb-4 flex items-center justify-between">
                <div className="flex items-baseline gap-1.5">
                  <h2 className="text-[22px] font-black text-v-navy">Giỏ hàng</h2>
                  <sup className="text-[14px] font-black text-v-navy">{totalItems}</sup>
                </div>
                <button
                  onClick={() => setIsSidebarOpen(false)}
                  className="p-1.5 hover:bg-black/5 rounded-full transition-colors"
                >
                  <X size={22} className="text-[#002060]" />
                </button>
              </div>

              {/* Items */}
              <div className="flex-grow overflow-y-auto px-5 space-y-4 pb-2">
                {items.length === 0 ? (
                  <div className="h-full flex flex-col items-center justify-center text-center gap-4 py-20">
                    <TicketPercent size={48} className="text-gray-200" />
                    <p className="text-[15px] font-bold text-gray-400">Giỏ hàng chưa có sản phẩm</p>
                    <button
                      onClick={() => setIsSidebarOpen(false)}
                      className="px-8 py-3 rounded-lg font-bold text-[13px] text-[#fffff1]"
                      style={{ backgroundColor: "#0213b0" }}
                    >
                      Tiếp tục mua sắm
                    </button>
                  </div>
                ) : (
                  <>
                    {items.map((item) => {
                      const currentPrice = getVnd(item.variant.price);
                      const basePrice = getVnd(item.variant.base_price || item.variant.price);
                      const hasDiscount = basePrice > currentPrice;

                      return (
                        <div key={item.variant_id} className="flex gap-3">
                          {/* Image */}
                          <div
                            className="w-[72px] h-[72px] flex-shrink-0 rounded-xl overflow-hidden flex items-center justify-center"

                          >
                            <img
                              src={getImageUrl(item.variant.main_image || item.product.main_image) || ""}
                              alt={item.product.name}
                              className="w-full h-full object-contain p-1"
                            />
                          </div>

                          {/* Info */}
                          <div className="flex-grow min-w-0">
                            <div className="flex items-start justify-between gap-2">
                              <h3 className="text-[13px] font-bold text-[#002060] leading-snug line-clamp-2 flex-grow">
                                {item.product.name}
                              </h3>
                              <button
                                onClick={() => removeItem(item.variant_id)}
                                className="flex-shrink-0 text-gray-300 hover:text-red-400 transition-colors mt-0.5"
                              >
                                <Trash2 size={14} />
                              </button>
                            </div>

                            <p className="text-[11px] text-v-navy mt-0.5">
                              {[item.variant.volume, item.variant.packaging_type]
                                .filter(Boolean)
                                .join(" · ")}
                            </p>

                            <div className="flex items-center justify-between mt-2">
                              {/* Qty */}
                              <div
                                className="flex items-center rounded-md overflow-hidden border"
                                style={{ borderColor: "#d0d8e8" }}
                              >
                                <button
                                  onClick={() => updateQuantity(item.variant_id, item.quantity - 1)}
                                  className="w-8 h-8 flex items-center justify-center text-[#002060] hover:bg-gray-100 transition-colors"
                                >
                                  <Minus size={13} strokeWidth={3} />
                                </button>
                                <span className="w-8 text-center text-[13px] font-bold text-[#002060] border-x"
                                  style={{ borderColor: "#d0d8e8" }}>
                                  {item.quantity}
                                </span>
                                <button
                                  onClick={() => updateQuantity(item.variant_id, item.quantity + 1)}
                                  className="w-8 h-8 flex items-center justify-center text-[#002060] hover:bg-gray-100 transition-colors"
                                >
                                  <Plus size={13} strokeWidth={3} />
                                </button>
                              </div>

                              {/* Price */}
                              <div className="text-right">
                                {hasDiscount && (
                                  <p className="text-[11px] text-gray-400 line-through">
                                    {(basePrice * item.quantity).toLocaleString("vi-VN")}đ
                                  </p>
                                )}
                                <p className="text-[15px] font-black text-v-navy">
                                  {(currentPrice * item.quantity).toLocaleString("vi-VN")}đ
                                </p>
                              </div>
                            </div>
                          </div>
                        </div>
                      );
                    })}

                    {/* Gifts / Rewards */}
                    {(rewards.length > 0 || isEvaluating) && (
                      <div className="space-y-2">
                        {isEvaluating && (
                          <div className="flex items-center gap-2 py-2 text-[11px] text-[#002060]">
                            <motion.div
                              animate={{ rotate: 360 }}
                              transition={{ repeat: Infinity, duration: 1, ease: "linear" }}
                              className="w-3.5 h-3.5 border-2 border-blue-200 border-t-[#002060] rounded-full"
                            />
                            Đang tính ưu đãi...
                          </div>
                        )}

                        {rewards.map((reward, idx) => {
                          if (reward.type === "fixed") {
                            return (
                              <div
                                key={idx}
                                className="flex items-center gap-3 rounded-xl px-3 py-2.5"
                                style={{ backgroundColor: "#e7f9a1" }}
                              >
                                <div className="relative w-10 h-10 flex-shrink-0">
                                  <img
                                    src={getImageUrl(reward.image) || ""}
                                    className="w-full h-full object-contain"
                                    alt="gift"
                                  />
                                  <span className="absolute -top-1.5 -left-1.5 bg-red-500 text-[#fffff1] text-[9px] font-black w-4 h-4 rounded-full flex items-center justify-center">
                                    {reward.quantity}
                                  </span>
                                </div>
                                <p className="flex-grow text-[12px] font-bold text-[#002060] line-clamp-1">{reward.name}</p>
                              </div>
                            );
                          }

                          if (reward.type === "choice") {
                            const displayOption = reward.selected_option || reward.options?.[0];
                            return (
                              <div
                                key={idx}
                                className="flex items-center gap-3 rounded-xl px-3 py-2.5"
                                style={{ backgroundColor: "#e7f9a1" }}
                              >
                                <div className="relative w-10 h-10 flex-shrink-0">
                                  {displayOption?.image ? (
                                    <img
                                      src={getImageUrl(displayOption.image) || ""}
                                      className="w-full h-full object-contain"
                                      alt="gift"
                                    />
                                  ) : (
                                    <div className="w-10 h-10 rounded-lg bg-white flex items-center justify-center">
                                      <TicketPercent size={18} className="text-yellow-400" />
                                    </div>
                                  )}
                                </div>
                                <div className="flex-grow min-w-0">
                                  <p className="text-[12px] font-bold text-v-navy line-clamp-1">
                                    {reward.selected_option ? reward.selected_option.name : (reward.from_rule || "Chọn quà tặng")}
                                  </p>
                                  <p className="text-[10px] text-v-navy/60">
                                    {reward.selected_option ? "Đã chọn" : `Chọn ${reward.pick_count} món quà`}
                                  </p>
                                </div>
                                <button
                                  onClick={() => setSelectedRewardToChange(reward)}
                                  className="text-[12px] font-black text-v-navy flex-shrink-0"
                                >
                                  Đổi quà
                                </button>
                              </div>
                            );
                          }
                          return null;
                        })}
                      </div>
                    )}
                  </>
                )}
              </div>

              {/* Footer */}
              {items.length > 0 && (
                <div className="flex-shrink-0">
                  {/* Wavy top border */}
                  <svg viewBox="0 0 480 16" className="w-full" style={{ display: "block", marginBottom: -1 }}>
                    <path
                      d="M0,8 C20,0 40,16 60,8 C80,0 100,16 120,8 C140,0 160,16 180,8 C200,0 220,16 240,8 C260,0 280,16 300,8 C320,0 340,16 360,8 C380,0 400,16 420,8 C440,0 460,16 480,8 L480,16 L0,16 Z"
                      fill="#e9f0f8"
                    />
                  </svg>

                  <div className="px-5 pt-3 pb-5 space-y-2" style={{ backgroundColor: "#e9f0f8" }}>
                    {/* Tổng tiền hàng */}
                    <div className="flex justify-between text-[13px] text-v-navy">
                      <span>Tổng tiền hàng</span>
                      <span className="font-bold text-v-navy">{totalBasePrice.toLocaleString("vi-VN")}đ</span>
                    </div>

                    {/* Giảm giá sản phẩm */}
                    {totalProductDiscount > 0 && (
                      <div className="flex justify-between text-[13px] text-v-navy">
                        <span>Giảm giá sản phẩm</span>
                        <span className="font-bold text-red-500">
                          -{totalProductDiscount.toLocaleString("vi-VN")}đ
                        </span>
                      </div>
                    )}

                    {/* Voucher giảm giá */}
                    <div className="flex justify-between text-[13px] text-v-navy">
                      <span>Voucher giảm giá</span>
                      <span className={`font-bold ${voucherDiscount > 0 ? "text-red-500" : ""}`}>
                        {voucherDiscount > 0
                          ? `-${voucherDiscount.toLocaleString("vi-VN")}đ`
                          : "0đ"}
                      </span>
                    </div>

                    {/* Voucher add button */}
                    <button
                      onClick={() => setIsVoucherSidebarOpen(true)}
                      className="w-full flex items-center gap-3 px-4 py-3 rounded-xl border transition-all"
                      style={{
                        backgroundColor: voucherDiscount > 0 ? "#dcfce7" : "#e7f9a1",
                        borderColor: voucherDiscount > 0 ? "#86efac" : "#c8f56a",
                      }}
                    >
                      <TicketPercent size={18} className="text-[#002060] flex-shrink-0" />
                      <span className="flex-grow text-left text-[13px] font-medium text-[#002060]">
                        {appliedVoucher ? "Đã áp dụng 1 voucher" : "Thêm voucher để giảm giá"}
                      </span>
                      {appliedVoucher ? (
                        <button
                          onClick={(e) => {
                            e.stopPropagation();
                            removeVoucher();
                          }}
                          className="text-[#002060]/50 hover:text-red-500 transition-colors"
                        >
                          <X size={14} />
                        </button>
                      ) : (
                        <Plus size={16} className="text-[#002060]" />
                      )}
                    </button>

                    {/* Divider */}
                    <div className="border-t border-[#002060]/10 my-1" />

                    {/* Total */}
                    <div className="flex justify-between items-center">
                      <span className="text-[15px] font-bold text-v-navy">Tổng</span>
                      <span className="text-[20px] font-black text-v-navy">
                        {subtotal.toLocaleString("vi-VN")}đ
                      </span>
                    </div>

                    {/* Checkout button */}
                    <Link
                      href="/checkout"
                      onClick={() => setIsSidebarOpen(false)}
                      className="flex items-center justify-center gap-2 w-full py-4 rounded-xl font-bold text-[14px] text-[#fffff1] transition-all hover:opacity-90"
                      style={{ backgroundColor: "#0213b0" }}
                    >
                      Tới trang thanh toán
                      <span className="text-lg">→</span>
                    </Link>
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
