"use client";

import { useRouter, useSearchParams } from "next/navigation";
import { useCallback, useMemo, useState, useEffect, useRef } from "react";
import { Check, ChevronDown } from "lucide-react";

const SORT_OPTIONS = [
  { value: "latest", label: "Liên quan" },
  { value: "price_asc", label: "Giá tăng dần" },
  { value: "price_desc", label: "Giá giảm dần" },
  { value: "popular", label: "Bán chạy" },
];

export default function SortSelect() {
  const router = useRouter();
  const searchParams = useSearchParams();

  const [open, setOpen] = useState(false);
  const wrapperRef = useRef<HTMLDivElement>(null);

  const currentValue = searchParams.get("sort") || "latest";

  const selectedOption = useMemo(() => {
    return (
      SORT_OPTIONS.find((item) => item.value === currentValue) ||
      SORT_OPTIONS[0]
    );
  }, [currentValue]);

  const createQueryString = useCallback(
    (name: string, value: string) => {
      const params = new URLSearchParams(searchParams.toString());
      params.set(name, value);
      return params.toString();
    },
    [searchParams]
  );

  const handleSelect = (value: string) => {
    router.push(`?${createQueryString("sort", value)}`, { scroll: false });
    setOpen(false);
  };

  useEffect(() => {
    function handleClickOutside(e: MouseEvent) {
      if (
        wrapperRef.current &&
        !wrapperRef.current.contains(e.target as Node)
      ) {
        setOpen(false);
      }
    }

    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  return (
    <div ref={wrapperRef} className="relative w-[230px]">
      {/* Button */}
      <button
        type="button"
        onClick={() => setOpen(!open)}
        className="w-full rounded-xl border border-[#001c9a] bg-transparent px-5 py-3 text-xs font-bold tracking-widest text-[#001c9a] flex items-center justify-between"
      >
        <span>
          Xếp theo:{" "}
          <span className="normal-case tracking-normal ml-1">
            {selectedOption.label}
          </span>
        </span>

        <ChevronDown
          size={16}
          className={`transition-transform duration-200 ${open ? "rotate-180" : ""
            }`}
        />
      </button>

      {/* Dropdown */}
      {open && (
        <div className="absolute left-0 top-[50px] w-full rounded-xl border border-[#001c9a] bg-[#fefef0]/95 backdrop-blur-md p-2 shadow-xl z-50">
          {SORT_OPTIONS.map((item) => {
            const active = item.value === currentValue;

            return (
              <button
                key={item.value}
                type="button"
                onClick={() => handleSelect(item.value)}
                className={`w-full flex items-center gap-3 px-3 py-3 rounded-md text-xs text-[#001c9a] transition-all text-left
                  ${active ? "bg-gray-200" : "hover:bg-white/60"}`}
              >
                <span className="w-4 flex justify-center">
                  {active && <Check size={16} />}
                </span>

                <span>{item.label}</span>
              </button>
            );
          })}
        </div>
      )}
    </div>
  );
}