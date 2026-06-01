"use client";

import React, { useState, useEffect } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { X, Search, MapPin, Navigation } from "lucide-react";
import { catalogApi } from "@/lib/api";

interface Store {
  id: number;
  name: string;
  phone: string;
  address: string;
  ward: string;
  district: string;
  province: string;
}

interface StoreSidebarProps {
  isOpen: boolean;
  onClose: () => void;
  selectedStoreId: number | null;
  onSelect: (store: Store) => void;
}

export default function StoreSidebar({
  isOpen,
  onClose,
  selectedStoreId,
  onSelect,
}: StoreSidebarProps) {
  const [stores, setStores] = useState<Store[]>([]);
  const [searchQuery, setSearchQuery] = useState("");
  const [selectedCity, setSelectedCity] = useState("Hồ Chí Minh");
  const [selectedDistrict, setSelectedDistrict] = useState("");

  useEffect(() => {
    if (isOpen) {
      loadStores();
    }
  }, [isOpen]);

  const loadStores = async () => {
    try {
      const res = await catalogApi.getStores();
      if (res.data) {
        setStores(res.data);
      }
    } catch (e) {
      console.error("Failed to load stores", e);
    }
  };

  const filteredStores = stores.filter((s) => {
    const matchesSearch = s.name.toLowerCase().includes(searchQuery.toLowerCase()) || 
                         s.address.toLowerCase().includes(searchQuery.toLowerCase());
    const matchesCity = !selectedCity || s.province.includes(selectedCity);
    const matchesDistrict = !selectedDistrict || s.district.includes(selectedDistrict);
    return matchesSearch && matchesCity && matchesDistrict;
  });

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
            className="fixed top-0 right-0 bottom-0 w-[500px] bg-white z-[2001] flex flex-col shadow-2xl"
          >
            <div className="px-6 py-6 border-b flex items-center justify-between">
              <h2 className="text-[20px] font-black text-[#0213b0]">Danh sách cửa hàng</h2>
              <button onClick={onClose} className="p-1 hover:bg-gray-100 rounded-full transition-colors">
                <X size={24} className="text-[#0213b0]" />
              </button>
            </div>

            <div className="p-6 space-y-4 border-b">
              <div className="flex gap-3">
                <div className="flex-1">
                  <label className="text-[11px] font-bold text-gray-400 uppercase mb-1 block">Thành phố</label>
                  <select 
                    value={selectedCity}
                    onChange={(e) => setSelectedCity(e.target.value)}
                    className="w-full border rounded-xl px-4 py-3 text-[14px] font-medium text-[#002060] outline-none focus:border-[#0213b0]"
                  >
                    <option value="Hồ Chí Minh">Hồ Chí Minh</option>
                    <option value="Hà Nội">Hà Nội</option>
                    <option value="Đà Nẵng">Đà Nẵng</option>
                  </select>
                </div>
                <div className="flex-1">
                  <label className="text-[11px] font-bold text-gray-400 uppercase mb-1 block">Quận / Huyện</label>
                  <select 
                    value={selectedDistrict}
                    onChange={(e) => setSelectedDistrict(e.target.value)}
                    className="w-full border rounded-xl px-4 py-3 text-[14px] font-medium text-[#002060] outline-none focus:border-[#0213b0]"
                  >
                    <option value="">Tất cả</option>
                    <option value="Tân Bình">Tân Bình</option>
                    <option value="Tân Phú">Tân Phú</option>
                    <option value="Quận 1">Quận 1</option>
                  </select>
                </div>
              </div>

              <div className="relative">
                <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" size={18} />
                <input 
                  type="text"
                  placeholder="Tìm kiếm cửa hàng..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="w-full border rounded-xl pl-11 pr-4 py-3 text-[14px] outline-none focus:border-[#0213b0]"
                />
              </div>
            </div>

            <div className="flex-1 overflow-y-auto px-6 py-6 space-y-4">
              {filteredStores.map((store) => (
                <div
                  key={store.id}
                  onClick={() => onSelect(store)}
                  className={`p-5 rounded-2xl border-2 transition-all cursor-pointer ${
                    selectedStoreId === store.id ? "border-[#0213b0] bg-[#f0f4ff]" : "border-gray-100 hover:border-gray-200"
                  }`}
                >
                  <div className="flex items-start justify-between gap-4">
                    <div className="flex items-center gap-2 px-2 py-0.5 bg-[#e7f9a1] text-[#0213b0] text-[10px] font-bold rounded uppercase tracking-wider">
                      Cửa hàng
                    </div>
                    <button className="text-[11px] font-bold text-[#0213b0] flex items-center gap-1 hover:underline">
                      <Navigation size={12} />
                      Chỉ đường
                    </button>
                  </div>
                  <h3 className="font-bold text-[#002060] mt-3">{store.name}</h3>
                  <p className="text-[13px] text-gray-500 mt-2 leading-relaxed">
                    {store.address}, {store.ward}, {store.district}, {store.province}
                  </p>
                </div>
              ))}

              {filteredStores.length === 0 && (
                <div className="text-center py-20">
                  <MapPin size={48} className="mx-auto text-gray-100 mb-4" />
                  <p className="text-gray-400">Không tìm thấy cửa hàng nào</p>
                </div>
              )}
            </div>

            <div className="p-6 border-t bg-[#f9f9f9] shadow-inner">
              <button
                onClick={onClose}
                className="w-full py-4 rounded-xl font-black text-[15px] text-[#fffff1] transition-all"
                style={{ backgroundColor: "#0213b0" }}
              >
                Cập nhật cửa hàng
              </button>
            </div>
          </motion.div>
        </>
      )}
    </AnimatePresence>
  );
}
