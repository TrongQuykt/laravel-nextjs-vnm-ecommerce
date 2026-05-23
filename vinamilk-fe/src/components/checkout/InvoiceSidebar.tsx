"use client";

import React, { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { X, Receipt } from "lucide-react";

interface InvoiceSidebarProps {
  isOpen: boolean;
  onClose: () => void;
  onApply: (data: any) => void;
  initialData?: any;
}

export default function InvoiceSidebar({
  isOpen,
  onClose,
  onApply,
  initialData,
}: InvoiceSidebarProps) {
  const [type, setType] = useState<"company" | "personal">(initialData?.type || "company");
  const [formData, setFormData] = useState({
    tax_code: initialData?.tax_code || "",
    name: initialData?.name || "",
    phone: initialData?.phone || "",
    address: initialData?.address || "",
    email: initialData?.email || "",
  });

  const handleUpdate = (e: React.FormEvent) => {
    e.preventDefault();
    onApply({ ...formData, type });
    onClose();
  };

  return (
    <AnimatePresence>
      {isOpen && (
        <>
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            onClick={onClose}
            className="fixed inset-0 bg-black/40 z-[2000]"
          />
          <motion.div
            initial={{ x: "100%" }}
            animate={{ x: 0 }}
            exit={{ x: "100%" }}
            transition={{ type: "spring", damping: 30, stiffness: 300 }}
            className="fixed top-0 right-0 bottom-0 w-[450px] bg-[#fffff1] z-[2001] flex flex-col shadow-2xl"
          >
            <div className="px-6 py-6 border-b border-[#002060] flex items-center justify-between">
              <h2 className="text-[24px] font-medium text-[#002060]">Yêu cầu hoá đơn điện tử</h2>
              <button onClick={onClose} className="p-1 hover:bg-[#002060]/10 rounded-full transition-colors">
                <X size={24} className="text-[#002060]" />
              </button>
            </div>

            <form id="invoice-form" onSubmit={handleUpdate} className="flex-1 overflow-y-auto navy-scrollbar px-6 py-8 space-y-8 bg-[#fffff1]">
              <div>
                <h3 className="text-[12px] font-bold text-[#002060] uppercase tracking-widest mb-4">HOÁ ĐƠN NÀY DÀNH CHO</h3>
                <div className="space-y-2">
                  {[
                    { id: "company", label: "Công ty" },
                    { id: "personal", label: "Cá nhân" },
                  ].map((opt) => (
                    <label
                      key={opt.id}
                      onClick={() => setType(opt.id as any)}
                      className={`flex items-center gap-3 p-3 cursor-pointer transition-colors ${
                        type === opt.id ? "bg-[#f6f9fc]" : "bg-transparent"
                      }`}
                    >
                      <div className={`w-4 h-4 rounded-full border flex items-center justify-center ${
                        type === opt.id ? "border-[#002060]" : "border-[#002060]"
                      }`}>
                         {type === opt.id && <div className="w-2 h-2 rounded-full bg-[#002060]" />}
                      </div>
                      <span className="text-[14px] font-medium text-[#002060]">
                        {opt.label}
                      </span>
                    </label>
                  ))}
                </div>
              </div>

              <div>
                <h3 className="text-[12px] font-bold text-[#002060] uppercase tracking-widest mb-4">THÔNG TIN</h3>
                <div className="space-y-4">
                  <div className="bg-[#f6f9fc] px-4 pt-2.5 pb-2 border-b border-[#002060]/20 focus-within:border-[#002060] transition-colors">
                    <label className="block text-[11px] text-[#002060] font-bold mb-0.5">Mã số thuế *</label>
                    <input
                      type="text"
                      value={formData.tax_code}
                      onChange={(e) => setFormData({ ...formData, tax_code: e.target.value })}
                      placeholder=""
                      className="w-full bg-transparent text-[14px] text-[#002060] font-medium outline-none placeholder:text-[#002060]/40"
                      required
                    />
                  </div>
                  <div className="bg-[#f6f9fc] px-4 pt-2.5 pb-2 border-b border-[#002060]/20 focus-within:border-[#002060] transition-colors">
                    <label className="block text-[11px] text-[#002060] font-bold mb-0.5">
                      {type === "company" ? "Tên công ty *" : "Họ và tên *"}
                    </label>
                    <input
                      type="text"
                      value={formData.name}
                      onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                      placeholder=""
                      className="w-full bg-transparent text-[14px] text-[#002060] font-medium outline-none placeholder:text-[#002060]/40"
                      required
                    />
                  </div>
                  <div className="bg-[#f6f9fc] px-4 pt-2.5 pb-2 border-b border-[#002060]/20 focus-within:border-[#002060] transition-colors">
                    <label className="block text-[11px] text-[#002060] font-bold mb-0.5">Số điện thoại *</label>
                    <input
                      type="text"
                      value={formData.phone}
                      onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                      placeholder=""
                      className="w-full bg-transparent text-[14px] text-[#002060] font-medium outline-none placeholder:text-[#002060]/40"
                      required
                    />
                  </div>
                  <div className="bg-[#f6f9fc] px-4 pt-2.5 pb-2 border-b border-[#002060]/20 focus-within:border-[#002060] transition-colors">
                    <label className="block text-[11px] text-[#002060] font-bold mb-0.5">Địa chỉ *</label>
                    <input
                      type="text"
                      value={formData.address}
                      onChange={(e) => setFormData({ ...formData, address: e.target.value })}
                      placeholder=""
                      className="w-full bg-transparent text-[14px] text-[#002060] font-medium outline-none placeholder:text-[#002060]/40"
                      required
                    />
                  </div>
                  <div className="bg-[#f6f9fc] px-4 pt-2.5 pb-2 border-b border-[#002060]/20 focus-within:border-[#002060] transition-colors">
                    <label className="block text-[11px] text-[#002060] font-bold mb-0.5">Địa chỉ Email *</label>
                    <input
                      type="email"
                      value={formData.email}
                      onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                      placeholder=""
                      className="w-full bg-transparent text-[14px] text-[#002060] font-medium outline-none placeholder:text-[#002060]/40"
                      required
                    />
                  </div>
                </div>
              </div>
            </form>

            <div className="p-6 border-t bg-[#fffff1]">
              <button
                type="submit"
                form="invoice-form"
                className="w-full py-4 rounded font-bold text-[15px] text-[#fffff1] transition-all"
                style={{ backgroundColor: "#002060" }}
              >
                Xác nhận
              </button>
            </div>
          </motion.div>
        </>
      )}
    </AnimatePresence>
  );
}
