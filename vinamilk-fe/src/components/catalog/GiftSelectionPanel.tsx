"use client";

import React, { useState, useEffect, useRef } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { X } from "lucide-react";
import { getImageUrl } from "@/lib/api";
import { MarketingReward } from "@/context/CartContext";

interface GiftSelectionPanelProps {
  reward: MarketingReward;
  onClose: () => void;
  onApply: (selectedProductIds: number[]) => void;
  isOpen: boolean;
  isStandalone?: boolean;
}

export default function GiftSelectionPanel({
  reward,
  onClose,
  onApply,
  isOpen,
  isStandalone = false,
}: GiftSelectionPanelProps) {
  const [selectedId, setSelectedId] = useState<number | null>(null);
  const panelRef = useRef<HTMLDivElement>(null);

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

  useEffect(() => {
    if (reward.selected_id) {
      setSelectedId(reward.selected_id);
    } else {
      setSelectedId(null);
    }
  }, [reward.selected_id, isOpen]);

  const options = reward.options || [];

  const handleApply = () => {
    if (selectedId) {
      onApply([selectedId]);
    }
  };

  return (
    <AnimatePresence>
      {isOpen && (
        <motion.div
          ref={panelRef}
          initial={{ x: isStandalone ? "100%" : 30, opacity: 0 }}
          animate={{ x: 0, opacity: 1 }}
          exit={{ x: isStandalone ? "100%" : 30, opacity: 0 }}
          transition={{ type: "spring", damping: 28, stiffness: 280 }}
          className={
            isStandalone
              ? "fixed top-3 bottom-3 z-[1005] flex flex-col overflow-hidden pointer-events-auto"
              : "absolute top-0 bottom-0 z-[1005] flex flex-col overflow-hidden pointer-events-auto"
          }
          style={{
            width: "460px",
            right: isStandalone ? "12px" : "100%",
            marginRight: isStandalone ? 0 : "12px",
            backgroundColor: "#fffff1",
            boxShadow: isStandalone ? "0 0 30px rgba(0,0,0,0.15)" : "-10px 0 30px rgba(0,0,0,0.12)",
            borderRadius: "24px"
          }}
        >
          {/* Header */}
          <div className="px-6 py-5 flex items-center justify-between border-b border-gray-200">
            <h2 className="text-[18px] font-black text-[#002060] uppercase tracking-wide">
              Đổi quà
            </h2>
            <button
              onClick={onClose}
              className="p-1.5 hover:bg-black/5 rounded-full transition-colors"
            >
              <X size={22} className="text-[#002060]" />
            </button>
          </div>

          {/* Options list */}
          <div className="flex-grow overflow-y-auto px-5 py-4 space-y-3 navy-scrollbar">
            {options.map((option: any) => {
              const isSelected = selectedId === option.id;
              return (
                <div
                  key={option.id}
                  onClick={() => setSelectedId(option.id)}
                  className="flex items-center gap-4 p-4 rounded-xl border-2 cursor-pointer transition-all"
                  style={{
                    borderColor: isSelected ? "#0213b0" : "#e5e7eb",
                    backgroundColor: isSelected ? "#f0f4ff" : "transparent",
                  }}
                >
                  {/* Image */}
                  <div
                    className="w-[72px] h-[72px] flex-shrink-0 rounded-xl overflow-hidden flex items-center justify-center"

                  >
                    {option.image ? (
                      <img
                        src={getImageUrl(option.image) || ""}
                        alt={option.name}
                        className="w-full h-full object-contain p-1"
                      />
                    ) : (
                      <div className="text-gray-300 text-3xl">🎁</div>
                    )}
                  </div>

                  {/* Info */}
                  <div className="flex-grow min-w-0">
                    <h4 className="text-[14px] font-bold text-[#002060] leading-snug">
                      {option.name}
                      {option.quantity > 1 ? ` x ${option.quantity}` : ""}
                    </h4>
                    <p className="text-[11px] text-gray-400 mt-0.5 uppercase tracking-wide">
                      {[option.volume, option.packing]
                        .filter(Boolean)
                        .join(" · ") || "Quà tặng Vinamilk"}
                    </p>
                  </div>

                  {/* Radio */}
                  <div
                    className="w-6 h-6 rounded-full border-2 flex-shrink-0 flex items-center justify-center transition-all"
                    style={{
                      borderColor: isSelected ? "#0213b0" : "#d1d5db",
                      backgroundColor: isSelected ? "#0213b0" : "white",
                    }}
                  >
                    {isSelected && (
                      <div className="w-2.5 h-2.5 rounded-full bg-white" />
                    )}
                  </div>
                </div>
              );
            })}
          </div>

          {/* Apply button */}
          <div className="px-5 py-5 border-t border-gray-200">
            <button
              disabled={!selectedId}
              onClick={handleApply}
              className="w-full py-4 rounded-xl font-black text-[15px] text-[#fffff1] transition-all disabled:opacity-40"
              style={{ backgroundColor: "#0213b0", letterSpacing: "0.05em" }}
            >
              Áp dụng
            </button>
          </div>
        </motion.div>
      )}
    </AnimatePresence>
  );
}
