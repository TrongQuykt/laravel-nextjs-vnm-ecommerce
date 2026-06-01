"use client";

import React, { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { X, CheckCircle2 } from "lucide-react";
import { getImageUrl } from "@/lib/api";
import { MarketingReward } from "@/context/CartContext";

interface GiftSelectionModalProps {
  reward: MarketingReward;
  onClose: () => void;
  onApply: (selectedProductIds: number[]) => void;
}

export default function GiftSelectionModal({ reward, onClose, onApply }: GiftSelectionModalProps) {
  const [selectedId, setSelectedId] = useState<number | null>(null);

  // Updated to match backend EnrichedCart gift structure
  const options = reward.options || [];
  const maxChoices = reward.pick_count || 1;

  const handleApply = () => {
    if (selectedId) {
      onApply([selectedId]);
      onClose();
    }
  };

  return (
    <AnimatePresence>
      <div className="fixed inset-0 z-[2000] flex items-center justify-center p-4">
        <motion.div
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0 }}
          onClick={onClose}
          className="absolute inset-0 bg-black/60 backdrop-blur-sm"
        />
        <motion.div
          initial={{ scale: 0.9, opacity: 0, y: 20 }}
          animate={{ scale: 1, opacity: 1, y: 0 }}
          exit={{ scale: 0.9, opacity: 0, y: 20 }}
          className="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[80vh]"
        >
          {/* Header */}
          <div className="px-6 py-4 border-b flex items-center justify-between bg-[#f8faff]">
            <h2 className="text-xl font-black text-v-navy">Đổi quà</h2>
            <button onClick={onClose} className="p-2 hover:bg-v-navy/5 rounded-full transition-colors">
              <X size={24} className="text-v-navy" />
            </button>
          </div>

          {/* List */}
          <div className="flex-grow overflow-y-auto p-6 space-y-4 custom-scrollbar-navy">
            {options.map((option: any) => (
              <div 
                key={option.id}
                onClick={() => setSelectedId(option.id)}
                className={`flex items-center gap-4 p-4 rounded-2xl border-2 transition-all cursor-pointer group
                  ${selectedId === option.id ? 'border-[#0213b0] bg-blue-50/30' : 'border-gray-100 hover:border-gray-200'}
                `}
              >
                <div className="w-16 h-16 bg-white rounded-xl flex-shrink-0 p-1 border border-gray-100 group-hover:scale-105 transition-transform">
                  <img 
                    src={getImageUrl(option.image) || ""} 
                    alt={option.name} 
                    className="w-full h-full object-contain"
                  />
                </div>
                <div className="flex-grow">
                  <h3 className="text-sm font-bold text-v-navy leading-tight mb-1">
                    {option.name} x {option.quantity || 1}
                  </h3>
                  <p className="text-[11px] text-v-navy/50 font-medium">
                    {option.description}
                  </p>
                </div>
                <div className={`w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all
                  ${selectedId === option.id ? 'bg-[#0213b0] border-[#0213b0]' : 'border-gray-200'}
                `}>
                  {selectedId === option.id && <CheckCircle2 size={16} className="text-white" />}
                </div>
              </div>
            ))}
          </div>

          {/* Footer */}
          <div className="p-6 border-t bg-gray-50/50">
            <button 
              disabled={!selectedId}
              onClick={handleApply}
              className="w-full bg-[#0213b0] text-white py-4 rounded-xl text-sm font-bold tracking-widest uppercase hover:bg-[#172d6e] transition-all shadow-xl shadow-blue-900/10 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Áp dụng
            </button>
          </div>
        </motion.div>
      </div>
    </AnimatePresence>
  );
}
