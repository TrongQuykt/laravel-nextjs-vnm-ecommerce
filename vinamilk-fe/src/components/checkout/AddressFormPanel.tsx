"use client";

import React, { useState, useEffect, useRef } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { X } from "lucide-react";
import { checkoutApi } from "@/lib/api";

interface Address {
  id?: number;
  first_name: string;
  last_name: string;
  phone: string;
  city: string;
  district: string;
  ward: string;
  detail: string;
  is_default: boolean;
}

interface AddressFormPanelProps {
  isOpen: boolean;
  onClose: () => void;
  initialData: Address | null;
  onSuccess: () => void;
}

export default function AddressFormPanel({
  isOpen,
  onClose,
  initialData,
  onSuccess,
}: AddressFormPanelProps) {
  const panelRef = useRef<HTMLDivElement>(null);
  const [provinces, setProvinces] = useState<any[]>([]);
  const [districts, setDistricts] = useState<any[]>([]);
  const [wards, setWards] = useState<any[]>([]);

  const [fullName, setFullName] = useState("");

  const [formData, setFormData] = useState<Address>({
    first_name: "",
    last_name: "",
    phone: "",
    city: "",
    district: "",
    ward: "",
    detail: "",
    is_default: false,
  });

  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState("");

  // Handle click outside
  useEffect(() => {
    if (!isOpen) return;
    const handleClickOutside = (e: MouseEvent) => {
      if (panelRef.current && !panelRef.current.contains(e.target as Node)) {
        onClose();
      }
    };
    document.addEventListener("mousedown", handleClickOutside, true);
    return () => document.removeEventListener("mousedown", handleClickOutside, true);
  }, [isOpen, onClose]);

  // Init data
  useEffect(() => {
    if (isOpen) {
      if (initialData) {
        setFormData(initialData);
        setFullName(`${initialData.last_name || ""} ${initialData.first_name || ""}`.trim());
      } else {
        setFormData({
          first_name: "",
          last_name: "",
          phone: "",
          city: "",
          district: "",
          ward: "",
          detail: "",
          is_default: false,
        });
        setFullName("");
      }
      setError("");
    }
  }, [isOpen, initialData]);

  // Fetch provinces
  useEffect(() => {
    if (isOpen && provinces.length === 0) {
      fetch("https://provinces.open-api.vn/api/?depth=3")
        .then((res) => res.json())
        .then((data) => setProvinces(data))
        .catch((err) => console.error("Error fetching provinces:", err));
    }
  }, [isOpen]);

  // Update districts
  useEffect(() => {
    if (formData.city && provinces.length > 0) {
      const selectedProvince = provinces.find((p) => p.name === formData.city);
      if (selectedProvince) {
        setDistricts(selectedProvince.districts || []);
        if (!initialData || initialData.city !== formData.city) {
            // Reset lower levels if city changed by user
            setWards([]);
        }
      }
    }
  }, [formData.city, provinces]);

  // Update wards
  useEffect(() => {
    if (formData.district && districts.length > 0) {
      const selectedDistrict = districts.find((d) => d.name === formData.district);
      if (selectedDistrict) {
        setWards(selectedDistrict.wards || []);
      }
    }
  }, [formData.district, districts]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setFormData((prev) => {
      const newData = { ...prev, [name]: value };
      if (name === "city") {
        newData.district = "";
        newData.ward = "";
      } else if (name === "district") {
        newData.ward = "";
      }
      return newData;
    });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);
    setError("");

    try {
      const token = localStorage.getItem("auth_token");
      const url = initialData?.id 
        ? `${process.env.NEXT_PUBLIC_API_URL || "http://127.0.0.1:8000/api/v1"}/user/addresses/${initialData.id}`
        : `${process.env.NEXT_PUBLIC_API_URL || "http://127.0.0.1:8000/api/v1"}/user/addresses`;
      
      const method = initialData?.id ? "PUT" : "POST";

      const parts = fullName.trim().split(" ");
      const last_name = parts.length > 1 ? parts.shift() || "" : fullName.trim();
      const first_name = parts.length > 0 ? parts.join(" ") : fullName.trim();

      const payload = {
        ...formData,
        last_name,
        first_name,
      };

      const res = await fetch(url, {
        method,
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(payload),
      });

      const data = await res.json();
      if (!res.ok) {
        throw new Error(data.message || "Lỗi khi lưu địa chỉ");
      }

      onSuccess();
      onClose();
    } catch (err: any) {
      setError(err.message);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <AnimatePresence>
      {isOpen && (
        <motion.div
          ref={panelRef}
          initial={{ x: 30, opacity: 0 }}
          animate={{ x: 0, opacity: 1 }}
          exit={{ x: 30, opacity: 0 }}
          transition={{ type: "spring", damping: 28, stiffness: 280 }}
          className="absolute top-0 bottom-0 z-[1005] flex flex-col overflow-hidden pointer-events-auto bg-white shadow-2xl"
          style={{
            width: "450px",
            right: "100%",
            marginRight: "12px",
            borderRadius: "16px",
            border: "1px solid rgba(2, 19, 176, 0.1)",
          }}
        >
          {/* Header */}
          <div className="px-6 py-6 border-b border-[#0213b0]/10 flex items-center justify-between">
            <h2 className="text-[16px] font-bold text-[#0213b0]">
              {initialData ? "Sửa địa chỉ" : "Thêm địa chỉ mới"}
            </h2>
            <button
              onClick={onClose}
              className="p-1 hover:bg-gray-100 rounded-full transition-colors"
            >
              <X size={20} className="text-[#0213b0]" />
            </button>
          </div>

          {/* Form Content */}
          <div className="flex-grow overflow-y-auto navy-scrollbar px-6 py-6 bg-white">
            <form id="address-form" onSubmit={handleSubmit} className="space-y-4">
              {error && (
                <div className="bg-red-50 text-red-500 p-3 text-sm rounded-md mb-4 font-medium">
                  {error}
                </div>
              )}

              <div className="bg-[#f6f9fc] px-4 pt-2.5 pb-2 border-b border-[#002060]/20 focus-within:border-[#002060] transition-colors">
                <label className="block text-[11px] text-[#002060] font-bold mb-0.5">Họ và tên *</label>
                <input
                  name="fullName"
                  value={fullName}
                  onChange={(e) => setFullName(e.target.value)}
                  placeholder=""
                  className="w-full bg-transparent text-[14px] text-[#002060] font-medium outline-none placeholder:text-[#002060]/40"
                  required
                />
              </div>

              <div className="bg-[#f6f9fc] px-4 pt-2.5 pb-2 border-b border-[#002060]/20 focus-within:border-[#002060] transition-colors">
                <label className="block text-[11px] text-[#002060] font-bold mb-0.5">Số điện thoại *</label>
                <div className="flex items-center">
                  <span className="text-[14px] font-medium text-[#002060] mr-2 flex items-center gap-1">
                    <span className="text-[16px]">🇻🇳</span> +84
                  </span>
                  <input
                    name="phone"
                    value={formData.phone}
                    onChange={handleChange}
                    placeholder=""
                    className="w-full bg-transparent text-[14px] text-[#002060] font-medium outline-none placeholder:text-[#002060]/40"
                    required
                  />
                </div>
              </div>

              <div className="bg-[#f6f9fc] px-4 pt-2.5 pb-2 border-b border-[#002060]/20 focus-within:border-[#002060] transition-colors">
                <label className="block text-[11px] text-[#002060] font-bold mb-0.5">Thành phố *</label>
                <select
                  name="city"
                  value={formData.city}
                  onChange={handleChange}
                  className="w-full bg-transparent text-[14px] text-[#002060] font-medium outline-none cursor-pointer appearance-none"
                  required
                >
                  <option value="" disabled>Chọn thành phố</option>
                  {provinces.map((p) => (
                    <option key={p.code} value={p.name}>{p.name}</option>
                  ))}
                </select>
              </div>

              <div className="bg-[#f6f9fc] px-4 pt-2.5 pb-2 border-b border-[#002060]/20 focus-within:border-[#002060] transition-colors">
                <label className="block text-[11px] text-[#002060] font-bold mb-0.5">Quận / Huyện *</label>
                <select
                  name="district"
                  value={formData.district}
                  onChange={handleChange}
                  className="w-full bg-transparent text-[14px] text-[#002060] font-medium outline-none cursor-pointer appearance-none"
                  required
                  disabled={!formData.city}
                >
                  <option value="" disabled>Chọn quận / huyện</option>
                  {districts.map((d) => (
                    <option key={d.code} value={d.name}>{d.name}</option>
                  ))}
                </select>
              </div>

              <div className="bg-[#f6f9fc] px-4 pt-2.5 pb-2 border-b border-[#002060]/20 focus-within:border-[#002060] transition-colors">
                <label className="block text-[11px] text-[#002060] font-bold mb-0.5">Phường / Xã *</label>
                <select
                  name="ward"
                  value={formData.ward}
                  onChange={handleChange}
                  className="w-full bg-transparent text-[14px] text-[#002060] font-medium outline-none cursor-pointer appearance-none"
                  required
                  disabled={!formData.district}
                >
                  <option value="" disabled>Chọn phường / xã</option>
                  {wards.map((w) => (
                    <option key={w.code} value={w.name}>{w.name}</option>
                  ))}
                </select>
              </div>

              <div className="bg-[#f6f9fc] px-4 pt-2.5 pb-2 border-b border-[#002060]/20 focus-within:border-[#002060] transition-colors">
                <label className="block text-[11px] text-[#002060] font-bold mb-0.5">Địa chỉ *</label>
                <input
                  name="detail"
                  value={formData.detail}
                  onChange={handleChange}
                  placeholder=""
                  className="w-full bg-transparent text-[14px] text-[#002060] font-medium outline-none placeholder:text-[#002060]/40"
                  required
                />
              </div>

              <div className="pt-4 flex items-center gap-3">
                <div 
                  className={`w-4 h-4 rounded-sm flex items-center justify-center cursor-pointer transition-colors ${
                    formData.is_default ? "bg-[#002060]" : "border border-[#002060]"
                  }`}
                  onClick={() => setFormData(prev => ({ ...prev, is_default: !prev.is_default }))}
                >
                  {formData.is_default && <span className="text-white text-[12px] leading-none">✓</span>}
                </div>
                <span 
                  className="text-[13px] font-bold text-[#002060] cursor-pointer"
                  onClick={() => setFormData(prev => ({ ...prev, is_default: !prev.is_default }))}
                >
                  Đặt làm địa chỉ mặc định
                </span>
              </div>
            </form>
          </div>

          {/* Footer */}
          <div className="p-6 border-t bg-white border-[#002060]/10">
            <button
              type="submit"
              form="address-form"
              disabled={isLoading}
              className="w-full py-4 rounded font-bold text-[15px] text-[#fffff1] transition-all disabled:opacity-50"
              style={{ backgroundColor: "#002060" }}
            >
              {isLoading ? "Đang xử lý..." : "Lưu thay đổi"}
            </button>
          </div>
        </motion.div>
      )}
    </AnimatePresence>
  );
}
