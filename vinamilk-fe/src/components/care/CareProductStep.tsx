"use client";

import React, { useEffect, useState } from "react";
import { careApi } from "@/lib/api";
import { CareProduct } from "@/types/care";
import { useCareWizard, formatVnd } from "@/context/CareWizardContext";
import { CareStepper } from "./CareStepper";
import { Lock } from "lucide-react";
import Link from "next/link";

export function CareProductStep() {
  const { draft, selectProduct } = useCareWizard();
  const [products, setProducts] = useState<CareProduct[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    careApi.getProducts().then((r) => setProducts(r.products || [])).finally(() => setLoading(false));
  }, []);

  const selected = draft.product;

  return (
    <div className="pb-28">
      <CareStepper current={1} />
      <h1 className="text-3xl md:text-4xl font-bold text-[#001c9a] text-center mb-2">Chọn sản phẩm</h1>
      <p className="text-center text-[#001c9a]/60 mb-10">
        Để tạo thành gói phù hợp với nhu cầu của người bạn thương
      </p>

      {loading ? (
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {[1, 2, 3].map((i) => (
            <div key={i} className="h-80 bg-[#001c9a]/5 rounded-2xl animate-pulse" />
          ))}
        </div>
      ) : products.length === 0 ? (
        <p className="text-center text-[#001c9a]/60">Chưa có sản phẩm Care. Vui lòng cấu hình trong admin.</p>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {products.map((p) => (
            <button
              key={p.id}
              type="button"
              onClick={() => selectProduct(p)}
              className={`text-left rounded-2xl border-2 p-4 transition-all hover:shadow-lg ${
                draft.careProductId === p.id
                  ? "border-[#001c9a] bg-[#d3e1ff]/30"
                  : "border-transparent bg-white/60"
              }`}
            >
              <div className="aspect-square flex items-center justify-center mb-4">
                {p.image && <img src={p.image} alt={p.name} className="max-h-full object-contain" />}
              </div>
              <p className="text-[10px] font-bold text-[#001c9a]/50 uppercase">{p.category_name}</p>
              <h3 className="font-bold text-[#001c9a] text-lg mb-1">{p.name}</h3>
              {p.short_description && (
                <p className="text-xs text-[#001c9a]/60 line-clamp-2 mb-2">{p.short_description}</p>
              )}
              <div className="mt-3 flex items-baseline gap-2">
                {p.discount_percent > 0 && (
                  <span className="text-sm font-bold text-[#001c9a]">-{p.discount_percent}%</span>
                )}
                <span className="text-lg font-black text-[#001c9a]">{formatVnd(p.care_price)}</span>
              </div>
            </button>
          ))}
        </div>
      )}

      {selected && (
        <div className="fixed bottom-0 left-0 right-0 z-50 bg-[#d3e1ff]/90 backdrop-blur border-t border-[#001c9a]/10">
          <div className="container mx-auto px-6 py-4 flex items-center justify-between max-w-5xl">
            <div className="text-[#001c9a]">
              <span className="text-sm opacity-70">Số sản phẩm: </span>
              <span className="font-bold">1</span>
              <span className="mx-4 text-sm opacity-70">Giá tạm tính: </span>
              <span className="font-black text-lg">{formatVnd(selected.care_price)}</span>
            </div>
            <Link
              href="/care/dang-ky?step=2"
              className="flex items-center gap-2 bg-[#001c9a] text-white px-8 py-3 rounded-full font-bold text-sm"
            >
              <Lock size={14} /> Đăng ký ngay
            </Link>
          </div>
        </div>
      )}
    </div>
  );
}
