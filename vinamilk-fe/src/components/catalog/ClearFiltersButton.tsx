"use client";

import { useRouter } from "next/navigation";
import { Trash2 } from "lucide-react";

export default function ClearFiltersButton({ slug }: { slug: string }) {
  const router = useRouter();

  return (
    <button
      onClick={() => router.push(`/collections/${slug}`, { scroll: false })}
      className="flex items-center gap-2 px-6 py-3 border border-[#001c9a] rounded-xl text-[#001c9a] font-bold text-sm hover:bg-[#001c9a] hover:text-white transition-all w-fit group shadow-sm bg-white/20 backdrop-blur-sm"
    >
      <Trash2 size={16} className="group-hover:rotate-12 transition-transform" />
      <span>Xoá bộ lọc</span>
    </button>
  );
}
