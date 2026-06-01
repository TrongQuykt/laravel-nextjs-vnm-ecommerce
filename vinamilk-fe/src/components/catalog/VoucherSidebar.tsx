"use client";

import React, { useState, useEffect, useCallback, useRef } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { X, Tag, CheckCircle, Loader2, ChevronRight } from "lucide-react";
import { useCart } from "@/context/CartContext";
import { voucherApi, getImageUrl, authFetchApi } from "@/lib/api";

interface VoucherItem {
  id: number;
  code: string;
  name: string;
  description: string;
  banner_image: string | null;
  type: "percent" | "fixed";
  discount_value: number;
  max_discount_amount: number | null;
  min_order_amount: number;
  applicable_product_ids: number[] | null;
  total_quantity: number;
  used_count: number;
  expires_at: string | null;
  is_eligible: boolean;
  ineligible_reason: string | null;
  discount_amount: number;
}

const getVnd = (price: number) =>
  price > 0 && price < 10000 ? price * 1000 : price;

interface VoucherSidebarProps {
  isOpen?: boolean;
  onClose?: () => void;
}

export default function VoucherSidebar({ isOpen, onClose }: VoucherSidebarProps = {}) {
  const {
    isVoucherSidebarOpen: contextIsOpen,
    setIsVoucherSidebarOpen: contextSetIsOpen,
    items,
    totalBasePrice,
    totalProductDiscount,
    appliedVoucher,
    applyVoucher,
    removeVoucher,
    isApplyingVoucher,
    appliedRedemptions,
    applyRedemption,
    removeRedemption,
  } = useCart();

  const finalIsOpen = isOpen !== undefined ? isOpen : contextIsOpen;
  const handleClose = onClose || (() => contextSetIsOpen(false));
  const sidebarRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (isOpen !== undefined) return;
    if (!finalIsOpen) return;
    
    const handleClickOutside = (e: MouseEvent) => {
      if (sidebarRef.current && !sidebarRef.current.contains(e.target as Node)) {
        handleClose();
      }
    };
    
    document.addEventListener("mousedown", handleClickOutside, true);
    return () => document.removeEventListener("mousedown", handleClickOutside, true);
  }, [finalIsOpen, isOpen]);

  const [vouchers, setVouchers] = useState<VoucherItem[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [manualCode, setManualCode] = useState("");
  const [manualError, setManualError] = useState("");
  const [manualSuccess, setManualSuccess] = useState("");
  const [applyingId, setApplyingId] = useState<number | null>(null);
  const [detailVoucher, setDetailVoucher] = useState<VoucherItem | null>(null);
  const [activeTab, setActiveTab] = useState<"system" | "personal">("system");
  const [personalRewards, setPersonalRewards] = useState<any[]>([]);

  const cartTotal = totalBasePrice - totalProductDiscount;
  const cartItems = items.map((item) => ({
    product_id: item.product_id,
    price: getVnd(item.variant.price),
    quantity: item.quantity,
  }));

  const isLoggedIn =
    typeof window !== "undefined" && !!localStorage.getItem("auth_token");

  const loadVouchers = useCallback(async () => {
    if (!isLoggedIn) return;
    setIsLoading(true);
    try {
      const res = await voucherApi.getVouchers(cartTotal, cartItems);
      if (res.success) {
        const sorted = [...res.data].sort((a: VoucherItem, b: VoucherItem) => {
          if (a.is_eligible && !b.is_eligible) return -1;
          if (!a.is_eligible && b.is_eligible) return 1;
          return 0;
        });
        setVouchers(sorted);
      }
    } catch (e) {
      console.error("Failed to load vouchers", e);
    } finally {
      setIsLoading(false);
    }
  }, [cartTotal, JSON.stringify(cartItems), isLoggedIn]);

  const loadPersonalRewards = useCallback(async () => {
    if (!isLoggedIn) return;
    try {
      const res = await authFetchApi<any>("/rewards/my-rewards");
      if (res && res.data) {
        setPersonalRewards(res.data);
      }
    } catch (e) {
      console.error("Failed to load personal rewards", e);
    }
  }, [isLoggedIn]);

  useEffect(() => {
    if (finalIsOpen) {
      loadVouchers();
      loadPersonalRewards();
    }
  }, [finalIsOpen, loadVouchers, loadPersonalRewards]);

  const handleApply = async (code: string, voucherId?: number) => {
    setManualError("");
    setManualSuccess("");
    if (voucherId) setApplyingId(voucherId);
    const result = await applyVoucher(code);
    if (result.success) {
      setManualSuccess("Áp dụng voucher thành công!");
      setManualCode("");
      setDetailVoucher(null);
      await loadVouchers();
    } else {
      setManualError(result.message);
    }
    setApplyingId(null);
  };

  const handleManualApply = () => {
    if (!manualCode.trim()) return;
    handleApply(manualCode.trim());
  };

  const formatMaxDiscount = (v: VoucherItem) => {
    if (v.type === "percent" && v.max_discount_amount) {
      const val = v.max_discount_amount > 0 && v.max_discount_amount < 10000
        ? v.max_discount_amount * 1000
        : v.max_discount_amount;
      return `Tối đa ${val.toLocaleString("vi-VN")}đ`;
    }
    return null;
  };

  const formatExpiry = (date: string | null) => {
    if (!date) return null;
    return new Date(date).toLocaleDateString("vi-VN", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    });
  };

  const formatDiscount = (v: VoucherItem) => {
    if (v.type === "percent") return `${v.discount_value}%`;
    const val = v.discount_value > 0 && v.discount_value < 10000
      ? v.discount_value * 1000
      : v.discount_value;
    return `${val.toLocaleString("vi-VN")}đ`;
  };

  return (
    <AnimatePresence>
      {finalIsOpen && (
        <>
          <motion.div
            ref={sidebarRef}
            initial={{ x: isOpen !== undefined ? "100%" : 30, opacity: 0 }}
            animate={{ x: 0, opacity: 1 }}
            exit={{ x: isOpen !== undefined ? "100%" : 30, opacity: 0 }}
            transition={{ type: "spring", damping: 28, stiffness: 280 }}
            className="fixed top-3 bottom-3 z-[1002] flex flex-col overflow-hidden"
            style={{
              right: isOpen !== undefined ? "12px" : "504px",
              width: "440px",
              backgroundColor: "#fffff1",
              boxShadow: isOpen !== undefined ? "none" : "-10px 0 30px rgba(0,0,0,0.05)",
              borderRadius: isOpen !== undefined ? "0" : "24px",
              border: isOpen !== undefined ? "1px solid rgba(2, 19, 176, 0.1)" : "none"
            }}
            onClick={(e) => e.stopPropagation()}
          >
            {/* Detail overlay */}
            <AnimatePresence>
              {detailVoucher && (
                <motion.div
                  initial={{ x: "100%" }}
                  animate={{ x: 0 }}
                  exit={{ x: "100%" }}
                  transition={{ type: "spring", damping: 28, stiffness: 280 }}
                  className="absolute inset-0 z-10 flex flex-col"
                  style={{ backgroundColor: "#fffff1" }}
                >
                  {/* Detail header */}
                  <div className="px-5 py-5 flex items-center justify-between border-b border-gray-200">
                    <h3 className="text-[16px] font-black text-[#002060]">Chi tiết voucher</h3>
                    <button
                      onClick={() => setDetailVoucher(null)}
                      className="p-1.5 hover:bg-black/5 rounded-full transition-colors"
                    >
                      <X size={20} className="text-[#002060]" />
                    </button>
                  </div>

                  {/* Detail content */}
                  <div className="flex-1 overflow-y-auto navy-scrollbar">
                    <div
                      className="flex flex-col items-center justify-center py-10 px-6 relative"
                      style={{ backgroundColor: detailVoucher.is_eligible ? "#fffde7" : "#f5f5f5" }}
                    >
                      {detailVoucher.banner_image ? (
                        <img
                          src={getImageUrl(detailVoucher.banner_image) || detailVoucher.banner_image}
                          alt={detailVoucher.name}
                          className="w-full h-40 object-cover rounded-xl"
                        />
                      ) : (
                        <>
                          <p className={`text-[11px] font-black uppercase tracking-widest ${detailVoucher.is_eligible ? "text-[#7a5c00]" : "text-gray-400"}`}>
                            VOUCHER GIẢM
                          </p>
                          <p className={`text-6xl font-black mt-1 ${detailVoucher.is_eligible ? "text-[#002060]" : "text-gray-400"}`}>
                            {formatDiscount(detailVoucher)}
                          </p>
                          {formatMaxDiscount(detailVoucher) && (
                            <p className={`text-[13px] font-bold mt-1 ${detailVoucher.is_eligible ? "text-[#7a5c00]" : "text-gray-400"}`}>
                              {formatMaxDiscount(detailVoucher)}
                            </p>
                          )}
                        </>
                      )}
                    </div>

                    <div className="p-5 space-y-3">
                      <p className="text-[14px] font-bold text-[#002060]">{detailVoucher.name}</p>
                      {detailVoucher.expires_at && (
                        <p className="text-[13px] text-gray-500">
                          Hạn sử dụng: <span className="font-bold">{formatExpiry(detailVoucher.expires_at)}</span>
                        </p>
                      )}
                      {detailVoucher.description && (
                        <p className="text-[13px] text-gray-500 leading-relaxed">{detailVoucher.description}</p>
                      )}
                      {detailVoucher.min_order_amount > 0 && (
                        <p className="text-[13px] text-gray-500">
                          Đơn tối thiểu:{" "}
                          <span className="font-bold">{(detailVoucher.min_order_amount > 0 && detailVoucher.min_order_amount < 10000 ? detailVoucher.min_order_amount * 1000 : detailVoucher.min_order_amount).toLocaleString("vi-VN")}đ</span>
                        </p>
                      )}
                      {detailVoucher.total_quantity > 0 && (
                        <p className="text-[13px] text-gray-500">
                          Đã dùng: <span className="font-bold">{detailVoucher.used_count}/{detailVoucher.total_quantity}</span>
                        </p>
                      )}
                    </div>
                  </div>

                  {/* Detail apply button */}
                  <div className="p-5 border-t border-gray-200">
                    {detailVoucher.is_eligible ? (
                      <button
                        onClick={() => handleApply(detailVoucher.code, detailVoucher.id)}
                        disabled={isApplyingVoucher}
                        className="w-full py-4 rounded-xl font-black text-[14px] text-[#fffff1] flex items-center justify-center gap-2 transition-all disabled:opacity-60"
                        style={{ backgroundColor: "#0213b0" }}
                      >
                        {isApplyingVoucher && <Loader2 size={16} className="animate-spin" />}
                        Áp dụng
                      </button>
                    ) : (
                      <button
                        disabled
                        className="w-full py-4 rounded-xl font-black text-[14px] bg-gray-200 text-gray-400 cursor-not-allowed"
                      >
                        Không thể áp dụng
                      </button>
                    )}
                  </div>
                </motion.div>
              )}
            </AnimatePresence>

            {/* Header */}
            <div className="px-5 py-5 flex items-center justify-between border-b border-gray-200">
              <div className="flex items-center gap-2">
                <Tag size={18} className="text-[#002060]" />
                <h2 className="text-[18px] font-black text-[#002060]">Ví Voucher</h2>
              </div>
              <button
                onClick={() => handleClose()}
                className="p-1.5 hover:bg-black/5 rounded-full transition-colors"
              >
                <X size={20} className="text-[#002060]" />
              </button>
            </div>

            {/* Tabs */}
            <div className="flex border-b border-gray-200 px-5 bg-[#fffff1]">
              <button
                onClick={() => setActiveTab("system")}
                className={`flex-1 py-3.5 text-[13px] font-bold text-center border-b-2 transition-all ${
                  activeTab === "system"
                    ? "border-b-2 border-[#0213b0] text-[#0213b0]"
                    : "border-transparent text-gray-400 hover:text-gray-600"
                }`}
              >
                Voucher hệ thống
              </button>
              <button
                onClick={() => setActiveTab("personal")}
                className={`flex-1 py-3.5 text-[13px] font-bold text-center border-b-2 transition-all ${
                  activeTab === "personal"
                    ? "border-b-2 border-[#0213b0] text-[#0213b0]"
                    : "border-transparent text-gray-400 hover:text-gray-600"
                }`}
              >
                Ưu đãi cá nhân ({personalRewards.length})
              </button>
            </div>

            {/* Manual input */}
            <div className="px-5 py-4 border-b border-gray-200">
              <div className="flex gap-2">
                <input
                  type="text"
                  value={manualCode}
                  onChange={(e) => {
                    setManualCode(e.target.value.toUpperCase());
                    setManualError("");
                    setManualSuccess("");
                  }}
                  onKeyDown={(e) => e.key === "Enter" && handleManualApply()}
                  placeholder="Nhập mã voucher"
                  className="flex-1 border border-gray-300 rounded-lg px-4 py-2.5 text-[13px] text-[#002060] placeholder-gray-400 outline-none focus:border-[#0213b0] transition-colors uppercase bg-transparent font-medium"
                />
                <button
                  onClick={handleManualApply}
                  disabled={isApplyingVoucher || !manualCode.trim()}
                  className="px-5 py-2.5 rounded-lg text-[13px] font-bold text-[#fffff1] transition-all disabled:opacity-40 flex items-center gap-1.5 whitespace-nowrap"
                  style={{ backgroundColor: "#0213b0" }}
                >
                  {isApplyingVoucher && !applyingId && <Loader2 size={14} className="animate-spin" />}
                  Áp dụng
                </button>
              </div>

              {manualSuccess && (
                <p className="text-[12px] text-green-500 font-medium mt-2 flex items-center gap-1.5">
                  <CheckCircle size={13} />
                  {manualSuccess}
                </p>
              )}
              {manualError && (
                <p className="text-[12px] text-red-500 font-medium mt-2">{manualError}</p>
              )}
            </div>

            {/* Applied banner */}
            <AnimatePresence>
              {appliedVoucher && activeTab === "system" && (
                <motion.div
                  initial={{ height: 0, opacity: 0 }}
                  animate={{ height: "auto", opacity: 1 }}
                  exit={{ height: 0, opacity: 0 }}
                  className="overflow-hidden px-5 pt-3"
                >
                  <div className="bg-green-50 border border-green-200 rounded-xl px-4 py-3 flex items-center gap-3">
                    <CheckCircle size={18} className="text-green-500 flex-shrink-0" />
                    <div className="flex-grow min-w-0">
                      <p className="text-[13px] font-black text-green-700">Đã áp dụng voucher</p>
                      <p className="text-[11px] text-green-600">
                        Giảm {appliedVoucher.discount_amount.toLocaleString("vi-VN")}đ
                      </p>
                    </div>
                    <button
                      onClick={removeVoucher}
                      className="text-gray-400 hover:text-red-500 transition-colors"
                    >
                      <X size={14} />
                    </button>
                  </div>
                </motion.div>
              )}
            </AnimatePresence>

            {/* Voucher list */}
            <div className="flex-1 overflow-y-auto px-5 py-4 space-y-3 navy-scrollbar">
              {!isLoggedIn ? (
                <div className="flex flex-col items-center justify-center py-20 text-center gap-3">
                  <Tag size={36} className="text-gray-200" />
                  <p className="text-[13px] font-bold text-gray-400">
                    Vui lòng đăng nhập để xem voucher
                  </p>
                </div>
              ) : isLoading ? (
                <div className="flex items-center justify-center py-20 gap-2 text-[#002060]">
                  <Loader2 size={20} className="animate-spin" />
                  <span className="text-[13px] font-bold">Đang tải...</span>
                </div>
              ) : activeTab === "system" ? (
                vouchers.length === 0 ? (
                  <div className="flex flex-col items-center justify-center py-20 text-center gap-3">
                    <Tag size={36} className="text-gray-200" />
                    <p className="text-[13px] font-bold text-gray-400">Không có voucher khả dụng</p>
                  </div>
                ) : (
                  vouchers.map((v) => {
                    const isApplied = appliedVoucher?.code === v.code;
                    const isCurrentlyApplying = isApplyingVoucher && applyingId === v.id;
                    const maxDiscount = formatMaxDiscount(v);

                    return (
                      <div
                        key={v.id}
                        className={`
                          relative rounded-xl overflow-hidden border transition-all cursor-pointer
                          ${v.is_eligible
                            ? isApplied
                              ? "border-[#0213b0] ring-2 ring-[#0213b0]/20"
                              : "border-[#b3c6e0] hover:border-[#0213b0] hover:shadow-sm"
                            : "border-gray-200 opacity-55"
                          }
                        `}
                        onClick={() => setDetailVoucher(v)}
                      >
                        <div className="flex">
                          {/* Left badge */}
                          <div
                            className="flex-shrink-0 w-[108px] flex flex-col items-center justify-center py-5 px-3 border-r-2 border-dashed"
                            style={{
                              backgroundColor: v.is_eligible ? "#fffde7" : "#f5f5f5",
                              borderColor: v.is_eligible ? "#f5c842" : "#d0d0d0",
                            }}
                          >
                            {v.banner_image ? (
                              <img
                                src={getImageUrl(v.banner_image) || v.banner_image}
                                alt={v.name}
                                className="w-full h-16 object-cover rounded"
                              />
                            ) : (
                              <>
                                <p
                                  className="text-[8px] font-black uppercase tracking-wider text-center leading-tight"
                                  style={{ color: v.is_eligible ? "#7a5c00" : "#9ca3af" }}
                                >
                                  VOUCHER GIẢM
                                </p>
                                <p
                                  className="text-[26px] font-black leading-none mt-1"
                                  style={{ color: v.is_eligible ? "#002060" : "#9ca3af" }}
                                >
                                  {formatDiscount(v)}
                                </p>
                                {maxDiscount && (
                                  <p
                                    className="text-[8px] font-bold text-center leading-tight mt-1"
                                    style={{ color: v.is_eligible ? "#7a5c00" : "#9ca3af" }}
                                  >
                                    {maxDiscount}
                                  </p>
                                )}
                              </>
                            )}
                          </div>

                          {/* Right content */}
                          <div
                            className="flex-1 py-3 px-3 flex items-center gap-2"
                            style={{ backgroundColor: v.is_eligible ? "#ffffff" : "#fafafa" }}
                          >
                            <div className="flex-1 min-w-0">
                              <p
                                className="text-[12px] font-bold leading-snug line-clamp-2"
                                style={{ color: v.is_eligible ? "#002060" : "#9ca3af" }}
                              >
                                {v.name}
                              </p>
                              {v.expires_at && (
                                <p className="text-[10px] mt-1 text-gray-400">
                                  Hạn sử dụng: {formatExpiry(v.expires_at)}
                                </p>
                              )}
                            </div>

                            {/* Right action */}
                            <div
                              className="flex-shrink-0"
                              onClick={(e) => e.stopPropagation()}
                            >
                              {v.is_eligible ? (
                                isCurrentlyApplying ? (
                                  <Loader2 size={20} className="animate-spin text-[#0213b0]" />
                                ) : isApplied ? (
                                  <button
                                    onClick={() => removeVoucher()}
                                    className="w-6 h-6 rounded flex items-center justify-center"
                                    style={{ backgroundColor: "#0213b0" }}
                                  >
                                    <CheckCircle size={16} className="text-white" />
                                  </button>
                                ) : (
                                  <button
                                    onClick={() => {
                                      setApplyingId(v.id);
                                      handleApply(v.code, v.id);
                                    }}
                                    className="w-6 h-6 rounded border-2 hover:opacity-70 transition-all"
                                    style={{ borderColor: "#0213b0" }}
                                  />
                                )
                              ) : (
                                <ChevronRight size={16} className="text-gray-300" />
                              )}
                            </div>
                          </div>
                        </div>
                      </div>
                    );
                  })
                )
              ) : (
                personalRewards.length === 0 ? (
                  <div className="flex flex-col items-center justify-center py-20 text-center gap-3">
                    <Tag size={36} className="text-gray-200" />
                    <p className="text-[13px] font-bold text-gray-400">Không có ưu đãi cá nhân nào</p>
                  </div>
                ) : (
                  personalRewards.map((r) => {
                    const isApplied = appliedRedemptions.some((ar) => ar.id === r.id);
                    let isEligible = true;
                    let ineligibleReason = null;

                    if (r.reward.type === "voucher") {
                      const matches = r.reward.name.match(/(\d+)K.*?(\d+)K/i);
                      if (matches) {
                        const minOrder = parseInt(matches[2]) * 1000;
                        if (cartTotal < minOrder) {
                          isEligible = false;
                          ineligibleReason = `Đơn hàng tối thiểu ${minOrder.toLocaleString("vi-VN")}đ`;
                        }
                      }
                    }

                    return (
                      <div
                        key={r.id}
                        onClick={() => {
                          if (!isEligible) return;
                          if (isApplied) {
                            removeRedemption(r.id);
                          } else {
                            applyRedemption(r);
                          }
                        }}
                        className={`
                          relative rounded-xl overflow-hidden border transition-all cursor-pointer select-none
                          ${isEligible
                            ? isApplied
                              ? "border-[#0213b0] ring-2 ring-[#0213b0]/20"
                              : "border-[#b3c6e0] hover:border-[#0213b0] hover:shadow-sm"
                            : "border-gray-200 opacity-55 cursor-not-allowed"
                          }
                        `}
                      >
                        <div className="flex">
                          {/* Left badge */}
                          <div
                            className="flex-shrink-0 w-[108px] flex flex-col items-center justify-center py-5 px-3 border-r-2 border-dashed"
                            style={{
                              backgroundColor: isApplied ? "#e8f0fe" : isEligible ? "#fffde7" : "#f5f5f5",
                              borderColor: isApplied ? "#0213b0" : isEligible ? "#f5c842" : "#d0d0d0",
                            }}
                          >
                            {r.reward.image ? (
                              <img
                                src={getImageUrl(r.reward.image) || r.reward.image}
                                alt={r.reward.name}
                                className="w-full h-12 object-contain rounded"
                              />
                            ) : (
                              <Tag size={24} className={isApplied ? "text-[#0213b0]" : isEligible ? "text-[#7a5c00]" : "text-gray-400"} />
                            )}
                          </div>

                          {/* Right content */}
                          <div
                            className="flex-1 py-3 px-3 flex items-center gap-2"
                            style={{ backgroundColor: isApplied ? "#eef2ff" : isEligible ? "#ffffff" : "#fafafa" }}
                          >
                            <div className="flex-1 min-w-0">
                              <p
                                className="text-[12px] font-bold leading-snug line-clamp-2"
                                style={{ color: isApplied ? "#0213b0" : isEligible ? "#002060" : "#9ca3af" }}
                              >
                                {r.reward.name}
                              </p>
                              {ineligibleReason ? (
                                <p className="text-[10px] mt-1 text-red-500 font-semibold">
                                  {ineligibleReason}
                                </p>
                              ) : isApplied ? (
                                <p className="text-[10px] mt-1 text-[#0213b0] font-bold">
                                  ✓ Đã áp dụng — bấm để gỡ
                                </p>
                              ) : (
                                <p className="text-[10px] mt-1 text-green-600 font-bold">
                                  Sẵn sàng sử dụng — bấm để áp dụng
                                </p>
                              )}
                            </div>

                            {/* Right action icon */}
                            <div className="flex-shrink-0 pointer-events-none">
                              {isEligible ? (
                                isApplied ? (
                                  <div
                                    className="w-6 h-6 rounded flex items-center justify-center"
                                    style={{ backgroundColor: "#0213b0" }}
                                  >
                                    <CheckCircle size={16} className="text-white" />
                                  </div>
                                ) : (
                                  <div
                                    className="w-6 h-6 rounded border-2"
                                    style={{ borderColor: "#0213b0" }}
                                  />
                                )
                              ) : (
                                <ChevronRight size={16} className="text-gray-300" />
                              )}
                            </div>
                          </div>
                        </div>
                      </div>
                    );
                  })
                )
              )}
            </div>
          </motion.div>
        </>
      )}
    </AnimatePresence>
  );
}
