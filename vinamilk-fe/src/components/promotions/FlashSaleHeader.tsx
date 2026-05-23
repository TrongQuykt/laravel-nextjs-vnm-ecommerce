"use client";

import React, { useState, useEffect } from "react";
import { FlashSale } from "@/types";
import { motion, AnimatePresence } from "framer-motion";

interface FlashSaleHeaderProps {
  data: FlashSale;
}

export const FlashSaleHeader = ({ data }: FlashSaleHeaderProps) => {
  const [timeLeft, setTimeLeft] = useState({ days: 0, hours: 0, minutes: 0, seconds: 0 });
  const [status, setStatus] = useState<"pending" | "ongoing" | "ended">("pending");

  useEffect(() => {
    if (!data?.start_time || !data?.end_time) return;

    const timer = setInterval(() => {
      const now = new Date().getTime();
      const start = new Date(data.start_time).getTime();
      const end = new Date(data.end_time).getTime();

      let targetTime = 0;
      if (now < start) {
        setStatus("pending");
        targetTime = start;
      } else if (now < end) {
        setStatus("ongoing");
        targetTime = end;
      } else {
        setStatus("ended");
        clearInterval(timer);
        return;
      }

      const diff = targetTime - now;
      setTimeLeft({
        days: Math.floor(diff / (1000 * 60 * 60 * 24)),
        hours: Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
        minutes: Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60)),
        seconds: Math.floor((diff % (1000 * 60)) / 1000),
      });
    }, 1000);

    return () => clearInterval(timer);
  }, [data?.start_time, data?.end_time]);

  return (
    <div className="flex flex-col items-center justify-center text-center mb-16">
      <h2 className="text-2xl md:text-3xl lg:text-[58px] font-sans font-black text-[#001c9a] tracking-tight leading-none mb-4">
        {data.title || "Flash sales, tuần lễ Vinamilk"}
      </h2>

      {data.content && (
        <p className="text-[#001c9a] text-base md:text-xl font-medium max-w-4xl mx-auto leading-relaxed px-4">
          {data.content}
        </p>
      )}

      {/* Countdown */}
      {status !== "ended" ? (
        <div className="flex items-center justify-center gap-3 md:gap-6 mt-12 md:mt-16 text-[#f74d1e]">
          <CountdownBlock value={timeLeft.days} label="Ngày" />
          <CountdownSeparator />
          <CountdownBlock value={timeLeft.hours} label="Giờ" />
          <CountdownSeparator />
          <CountdownBlock value={timeLeft.minutes} label="Phút" />
          <CountdownSeparator />
          <CountdownBlock value={timeLeft.seconds} label="Giây" />
        </div>
      ) : (
        <p className="text-[#f74d1e]/80 text-[34px] font-medium mt-12">Chương trình đã kết thúc.</p>
      )}
    </div>
  );
};

/* ── Countdown Block ── */
const CountdownBlock = ({ value, label }: { value: number; label: string }) => (
  <div className="flex flex-col items-center justify-center min-w-[60px] md:min-w-[80px]">
    <AnimatePresence mode="wait">
      <motion.span
        key={value}
        initial={{ y: -10, opacity: 0 }}
        animate={{ y: 0, opacity: 1 }}
        exit={{ y: 10, opacity: 0 }}
        transition={{ duration: 0.18 }}
        className="text-5xl md:text-7xl lg:text-[88px] font-bold tabular-nums leading-none tracking-tight"
      >
        {String(value).padStart(2, "0")}
      </motion.span>
    </AnimatePresence>
    <span className="text-sm md:text-lg font-sans mt-2 md:mt-4 opacity-90">
      {label}
    </span>
  </div>
);

const CountdownSeparator = () => (
  <div className="flex items-center justify-center h-full pb-8 md:pb-12">
    <span className="font-sans font-medium text-4xl md:text-6xl lg:text-[72px] leading-none">:</span>
  </div>
);
