"use client";

import React, { useEffect, useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { useCart } from "@/context/CartContext";
import { getImageUrl } from "@/lib/api";

export function FlyToCart() {
  const { flyItem, cartIconRef } = useCart();
  const [targetPos, setTargetPos] = useState({ x: 0, y: 0 });

  useEffect(() => {
    if (flyItem && cartIconRef.current) {
      const rect = cartIconRef.current.getBoundingClientRect();
      setTargetPos({ x: rect.left + rect.width / 2, y: rect.top + rect.height / 2 });
    }
  }, [flyItem, cartIconRef]);

  return (
    <AnimatePresence>
      {flyItem && targetPos.x !== 0 && (
        <motion.div
          initial={{
            position: "fixed",
            top: flyItem.startPos.y,
            left: flyItem.startPos.x,
            width: 80,
            height: 80,
            opacity: 1,
            scale: 1,
            zIndex: 9999,
            x: "-50%",
            y: "-50%",
          }}
          animate={{
            top: targetPos.y,
            left: targetPos.x,
            width: 20,
            height: 20,
            opacity: 0.2,
            scale: 0.5,
          }}
          transition={{
            duration: 0.8,
            ease: [0.16, 1, 0.3, 1], // Custom cubic-bezier for a smooth arc-like feel
          }}
          className="pointer-events-none"
        >
          <div className="w-full h-full rounded-full border-2 border-v-navy/20 bg-white shadow-2xl overflow-hidden p-1">
            <img src={getImageUrl(flyItem.src) || ""} alt="" className="w-full h-full object-contain" />
          </div>
        </motion.div>
      )}
    </AnimatePresence>
  );
}
