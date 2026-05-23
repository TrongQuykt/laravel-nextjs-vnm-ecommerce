"use client";

import React, { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { X, Clock } from "lucide-react";

interface PickupTimeSidebarProps {
  isOpen: boolean;
  onClose: () => void;
  onSelect: (date: Date, timeSlot: string) => void;
  initialDate?: Date;
  initialTimeSlot?: string;
}

const TIME_SLOTS = [
  "09:00 - 11:00",
  "11:00 - 13:00",
  "13:00 - 15:00",
  "15:00 - 17:00",
  "17:00 - 19:00",
  "19:00 - 20:00",
];

export default function PickupTimeSidebar({
  isOpen,
  onClose,
  onSelect,
  initialDate,
  initialTimeSlot,
}: PickupTimeSidebarProps) {
  const [selectedDate, setSelectedDate] = useState<Date>(initialDate || new Date());
  const [selectedSlot, setSelectedSlot] = useState<string>(initialTimeSlot || TIME_SLOTS[0]);

  // Generate 3 days starting from today (today + 2 days)
  const dates = Array.from({ length: 3 }, (_, i) => {
    const d = new Date();
    d.setDate(d.getDate() + i);
    return d;
  });

  const formatDate = (date: Date) => {
    const days = ["Chủ Nhật", "Thứ 2", "Thứ 3", "Thứ 4", "Thứ 5", "Thứ 6", "Thứ 7"];
    const dayName = days[date.getDay()];
    const dateStr = date.toLocaleDateString("vi-VN", { day: "2-digit", month: "2-digit", year: "numeric" });
    return { dayName, dateStr };
  };

  const handleUpdate = () => {
    onSelect(selectedDate, selectedSlot);
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
            className="fixed top-0 right-0 bottom-0 w-[450px] bg-white z-[2001] flex flex-col shadow-2xl"
          >
            <div className="px-6 py-6 border-b flex items-center justify-between">
              <h2 className="text-[20px] font-black text-[#0213b0]">Thời gian dự kiến nhận hàng</h2>
              <button onClick={onClose} className="p-1 hover:bg-gray-100 rounded-full transition-colors">
                <X size={24} className="text-[#0213b0]" />
              </button>
            </div>

            <div className="p-6 bg-[#f0f4ff] border-b flex items-center gap-4">
              <div className="w-10 h-10 rounded-full bg-[#0213b0] flex items-center justify-center text-white">
                <Clock size={20} />
              </div>
              <div>
                <p className="text-[14px] font-black text-[#0213b0]">{selectedSlot}</p>
                <p className="text-[12px] font-bold text-[#0213b0]/70">
                  {formatDate(selectedDate).dayName}, {formatDate(selectedDate).dateStr}
                </p>
              </div>
            </div>

            <div className="flex-1 overflow-y-auto px-6 py-6 space-y-8">
              <div>
                <h3 className="text-[12px] font-black text-gray-400 uppercase tracking-widest mb-4">Chọn ngày nhận</h3>
                <div className="grid grid-cols-2 gap-3">
                  {dates.map((date, i) => {
                    const { dayName, dateStr } = formatDate(date);
                    const isSelected = date.toDateString() === selectedDate.toDateString();
                    return (
                      <button
                        key={i}
                        onClick={() => setSelectedDate(date)}
                        className={`p-3 rounded-xl border-2 text-left transition-all ${
                          isSelected ? "border-[#0213b0] bg-[#f0f4ff]" : "border-gray-100 hover:border-gray-200"
                        }`}
                      >
                        <p className={`text-[12px] font-bold ${isSelected ? "text-[#0213b0]" : "text-gray-500"}`}>{dayName}</p>
                        <p className={`text-[13px] font-black ${isSelected ? "text-[#002060]" : "text-[#002060]"}`}>{dateStr}</p>
                      </button>
                    );
                  })}
                </div>
              </div>

              <div>
                <h3 className="text-[12px] font-black text-gray-400 uppercase tracking-widest mb-4">Chọn giờ nhận</h3>
                <div className="grid grid-cols-2 gap-3">
                  {TIME_SLOTS.map((slot) => {
                    const isSelected = slot === selectedSlot;
                    return (
                      <button
                        key={slot}
                        onClick={() => setSelectedSlot(slot)}
                        className={`p-3 rounded-xl border-2 text-center transition-all ${
                          isSelected ? "border-[#0213b0] bg-[#f0f4ff]" : "border-gray-100 hover:border-gray-200"
                        }`}
                      >
                        <span className={`text-[13px] font-bold ${isSelected ? "text-[#0213b0]" : "text-[#002060]"}`}>
                          {slot}
                        </span>
                      </button>
                    );
                  })}
                </div>
              </div>
            </div>

            <div className="p-6 border-t bg-white">
              <button
                onClick={handleUpdate}
                className="w-full py-4 rounded-xl font-black text-[15px] text-[#fffff1] transition-all"
                style={{ backgroundColor: "#0213b0" }}
              >
                Cập nhật thời gian
              </button>
              <button onClick={onClose} className="w-full mt-3 py-2 text-[13px] font-bold text-[#0213b0] hover:underline">
                Chọn lại thời gian sớm nhất
              </button>
            </div>
          </motion.div>
        </>
      )}
    </AnimatePresence>
  );
}
