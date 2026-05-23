"use client";

import React from "react";

export function NutritionFacts({ facts }: { facts: Array<{ key: string; value: string; unit: string }> | null }) {
  if (!facts || facts.length === 0) return null;

  return (
    <div className="w-full bg-white border border-v-navy/30 rounded-lg overflow-hidden flex flex-col">
      {/* Table Sub-header */}
      <div className="px-3 py-2 border-b-2 border-v-navy/30">
        <h3 className="text-[12px] font-sans font-bold text-v-navy leading-tight">
          Giá trị dinh dưỡng trung bình trong 100 ml*
        </h3>
      </div>

      {/* Data Rows */}
      <div className="flex flex-col">
        {facts.map((fact, i) => (
          <div
            key={i}
            className={`flex justify-between items-center px-3 py-1.5 ${i === facts.length - 1 ? "" : "border-b border-v-navy/15"
              }`}
          >
            <span className="text-[13px] text-v-navy/80 font-medium font-sans capitalize">{fact.key}</span>
            <div className="flex items-baseline gap-1">
              <span className="text-[14px] font-black text-v-navy font-sans tracking-tight">{fact.value}</span>
              <span className="text-[9px] text-v-navy/60 font-bold font-sans lowercase">{fact.unit}</span>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
