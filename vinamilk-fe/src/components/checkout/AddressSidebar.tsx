"use client";

import React, { useState, useEffect } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { X, MapPin, Plus, Edit2, CheckCircle } from "lucide-react";
import { checkoutApi } from "@/lib/api";
import AddressFormPanel from "./AddressFormPanel";

interface Address {
  id: number;
  first_name: string;
  last_name: string;
  phone: string;
  city: string;
  district: string;
  ward: string;
  detail: string;
  is_default: boolean;
}

interface AddressSidebarProps {
  isOpen: boolean;
  onClose: () => void;
  selectedAddressId: number | null;
  onSelect: (address: Address) => void;
}

export default function AddressSidebar({
  isOpen,
  onClose,
  selectedAddressId,
  onSelect,
}: AddressSidebarProps) {
  const [addresses, setAddresses] = useState<Address[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  
  // State for the AddressFormPanel
  const [isFormOpen, setIsFormOpen] = useState(false);
  const [editingAddress, setEditingAddress] = useState<Address | null>(null);

  useEffect(() => {
    if (isOpen) {
      loadAddresses();
    }
  }, [isOpen]);

  const loadAddresses = async () => {
    setIsLoading(true);
    try {
      const res = await checkoutApi.getAddresses();
      if (res.data) {
        setAddresses(res.data);
      }
    } catch (e) {
      console.error("Failed to load addresses", e);
    } finally {
      setIsLoading(false);
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
            onClick={() => {
              if (isFormOpen) {
                setIsFormOpen(false);
              } else {
                onClose();
              }
            }}
            className="fixed inset-0 bg-black/40 z-[2000]"
          />
          <motion.div
            initial={{ x: "100%" }}
            animate={{ x: 0 }}
            exit={{ x: "100%" }}
            transition={{ type: "spring", damping: 30, stiffness: 300 }}
            className="fixed top-0 right-0 bottom-0 w-[450px] bg-white z-[2001] flex flex-col shadow-2xl pointer-events-none"
          >
            {/* Address Form Panel */}
            <AddressFormPanel
              isOpen={isFormOpen}
              onClose={() => setIsFormOpen(false)}
              initialData={editingAddress}
              onSuccess={() => {
                loadAddresses();
              }}
            />

            <div className="flex-grow flex flex-col overflow-hidden pointer-events-auto bg-white shadow-2xl">
            <div className="px-6 py-6 border-b flex items-center justify-between">
              <h2 className="text-[20px] font-black text-[#0213b0]">Sổ địa chỉ</h2>
              <button onClick={onClose} className="p-1 hover:bg-gray-100 rounded-full transition-colors">
                <X size={24} className="text-[#0213b0]" />
              </button>
            </div>

            <div className="flex-1 overflow-y-auto navy-scrollbar px-6 py-6 space-y-4 bg-white">
              {isLoading ? (
                <div className="flex items-center justify-center py-20">
                  <div className="w-8 h-8 border-4 border-[#0213b0] border-t-transparent rounded-full animate-spin" />
                </div>
              ) : addresses.length === 0 ? (
                <div className="text-center py-10">
                  <MapPin size={48} className="mx-auto text-gray-200 mb-4" />
                  <p className="text-gray-400">Chưa có địa chỉ nào</p>
                </div>
              ) : (
                addresses.map((addr) => (
                  <div
                    key={addr.id}
                    onClick={() => onSelect(addr)}
                    className={`p-5 transition-all cursor-pointer relative flex items-start gap-4 ${
                      selectedAddressId === addr.id ? "border border-[#002060] bg-white" : "border-b border-gray-100 hover:bg-gray-50"
                    }`}
                    style={selectedAddressId === addr.id ? { borderRadius: "4px" } : {}}
                  >
                    {/* Radio button circle */}
                    <div className="flex-shrink-0 mt-1">
                      <div className={`w-4 h-4 rounded-full border flex items-center justify-center ${
                        selectedAddressId === addr.id ? "border-[#002060]" : "border-gray-300"
                      }`}>
                         {selectedAddressId === addr.id && <div className="w-2.5 h-2.5 rounded-full bg-[#002060]" />}
                      </div>
                    </div>
                    
                    <div className="flex-grow min-w-0">
                      <div className="flex items-start justify-between">
                        <div className="flex items-center gap-2">
                          <span className="font-medium text-[#002060] text-[15px]">
                            {addr.last_name} {addr.first_name}
                          </span>
                          <span className="text-[#002060]/40 text-[10px]">●</span>
                          <span className="text-[#002060] text-[15px]">{addr.phone}</span>
                        </div>
                        <button 
                          onClick={(e) => {
                            e.stopPropagation();
                            setEditingAddress(addr);
                            setIsFormOpen(true);
                          }}
                          className="text-[#002060]/60 hover:text-[#002060] transition-colors"
                        >
                          <Edit2 size={16} />
                        </button>
                      </div>
                      <p className="text-[13px] text-[#002060]/80 mt-2 leading-relaxed">
                        {addr.detail}, {addr.ward}, {addr.district}, {addr.city}
                      </p>
                      {addr.is_default && (
                        <span className="inline-block mt-3 px-3 py-1 bg-[#dcfce7] text-[#002060] text-[11px] font-bold rounded">
                          Mặc định
                        </span>
                      )}
                    </div>
                  </div>
                ))
              )}

              <button 
                onClick={() => {
                  setEditingAddress(null);
                  setIsFormOpen(true);
                }}
                className="w-full py-3.5 border border-[#002060] rounded flex items-center justify-center gap-2 text-[#002060] hover:bg-[#002060]/5 transition-all text-[14px]"
              >
                <Plus size={18} />
                Thêm địa chỉ
              </button>
            </div>

            <div className="p-6 border-t bg-[#f9f9f9]">
              <button
                onClick={onClose}
                className="w-full py-4 rounded-xl font-black text-[15px] text-[#fffff1] transition-all"
                style={{ backgroundColor: "#0213b0" }}
              >
                Cập nhật địa chỉ
              </button>
            </div>
            </div>
          </motion.div>
        </>
      )}
    </AnimatePresence>
  );
}
