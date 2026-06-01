"use client";

const STEPS = ["Chọn sản phẩm", "Điều chỉnh gói", "Thanh toán"];

export function CareStepper({ current }: { current: 1 | 2 | 3 }) {
  return (
    <div className="flex items-center justify-center gap-4 md:gap-8 mb-10">
      {STEPS.map((label, i) => {
        const step = (i + 1) as 1 | 2 | 3;
        const active = step === current;
        const done = step < current;
        return (
          <div key={label} className="flex items-center gap-2 md:gap-3">
            <div
              className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold border-2 ${
                active
                  ? "bg-[#001c9a] border-[#001c9a] text-white"
                  : done
                    ? "bg-[#001c9a]/20 border-[#001c9a] text-[#001c9a]"
                    : "border-[#001c9a]/30 text-[#001c9a]/40"
              }`}
            >
              {done ? "✓" : step}
            </div>
            <span
              className={`text-xs md:text-sm font-semibold hidden sm:block ${
                active ? "text-[#001c9a]" : "text-[#001c9a]/50"
              }`}
            >
              {label}
            </span>
            {i < STEPS.length - 1 && (
              <div className="w-8 md:w-16 h-px bg-[#001c9a]/20 hidden sm:block" />
            )}
          </div>
        );
      })}
    </div>
  );
}
