"use client";

import React, { useState, useEffect } from "react";
import { motion } from "framer-motion";

const sections = [
  { id: "deals",      label: "Ưu đãi trong tháng", number: "01" },
  { id: "flash-sale", label: "Flash Sale",          number: "02" },
  { id: "terms",      label: "Thể lệ chi tiết",     number: "03" },
];

export const SidebarNavigator = () => {
  const [activeSection, setActiveSection] = useState("deals");

  useEffect(() => {
    const handleScroll = () => {
      const scrollY = window.scrollY + 220;
      for (const section of sections) {
        const el = document.getElementById(section.id);
        if (el && scrollY >= el.offsetTop && scrollY < el.offsetTop + el.offsetHeight) {
          setActiveSection(section.id);
          break;
        }
      }
    };
    window.addEventListener("scroll", handleScroll, { passive: true });
    return () => window.removeEventListener("scroll", handleScroll);
  }, []);

  const scrollTo = (id: string) => {
    const el = document.getElementById(id);
    if (el) window.scrollTo({ top: el.offsetTop - 120, behavior: "smooth" });
  };

  return (
    <div className="hidden lg:block sticky top-32 h-fit w-56 pr-6 flex-shrink-0">
      <p className="text-[9px] font-black uppercase tracking-[0.4em] text-[#001c9a]/30 mb-5">
        Mục lục
      </p>
      <div className="flex flex-col gap-1">
        {sections.map((section) => {
          const isActive = activeSection === section.id;
          return (
            <button
              key={section.id}
              onClick={() => scrollTo(section.id)}
              className="group flex items-center gap-3 px-3 py-3 rounded-xl text-left transition-all duration-200 hover:bg-[#001c9a]/5"
            >
              <span className={`text-[10px] font-black tabular-nums transition-colors ${isActive ? "text-[#001c9a]" : "text-[#001c9a]/20"}`}>
                {section.number}
              </span>
              <div className="relative flex-1">
                <span className={`text-sm font-bold tracking-tight block transition-all duration-200 ${isActive ? "text-[#001c9a] translate-x-0.5" : "text-[#001c9a]/40 group-hover:text-[#001c9a]/60"}`}>
                  {section.label}
                </span>
                {isActive && (
                  <motion.div
                    layoutId="sidebar-active"
                    className="absolute -bottom-0.5 left-0 h-0.5 w-6 bg-[#001c9a] rounded-full"
                  />
                )}
              </div>
            </button>
          );
        })}
      </div>
    </div>
  );
};
