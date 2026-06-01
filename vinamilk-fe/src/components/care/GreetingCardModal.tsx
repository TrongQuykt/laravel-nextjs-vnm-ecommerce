"use client";

import React from "react";
import { CareGreetingCard } from "@/types/care";
import { X, Check, Trash2 } from "lucide-react";

interface Props {
  cards: CareGreetingCard[];
  selectedId: number | null;
  isOpen: boolean;
  onClose: () => void;
  onConfirm: (cardId: number | null, include: boolean) => void;
}

export function GreetingCardModal({ cards, selectedId, isOpen, onClose, onConfirm }: Props) {
  const [localId, setLocalId] = React.useState<number | null>(selectedId);

  React.useEffect(() => {
    if (isOpen) setLocalId(selectedId ?? cards[0]?.id ?? null);
  }, [isOpen, selectedId, cards]);

  if (!isOpen) return null;

  const selected = cards.find((c) => c.id === localId);

  return (
    <div className="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/80">
      <div
        className="bg-[#fffff1] rounded-xl w-full max-w-4xl overflow-hidden flex flex-col relative shadow-2xl"
        style={{ maxHeight: "90vh" }}
      >
        {/* Header */}
        <div className="flex items-center justify-between px-6 py-4 border-b border-[#001c9a]/10 flex-shrink-0">
          <div className="flex items-center gap-3">
            <button
              onClick={onClose}
              className="p-1.5 rounded-full hover:bg-[#001c9a]/5 text-[#001c9a]"
            >
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <line x1="19" y1="12" x2="5" y2="12" />
                <polyline points="12 19 5 12 12 5" />
              </svg>
            </button>
            <h3 className="text-lg font-bold text-[#001c9a] tracking-tight">Chọn lời nhắn</h3>
          </div>
          <button onClick={onClose} className="p-1.5 rounded-full hover:bg-[#001c9a]/5">
            <X size={18} className="text-[#001c9a]/50" strokeWidth={1.5} />
          </button>
        </div>

        {/* Body */}
        <div className="flex flex-col md:flex-row flex-1 overflow-hidden min-h-0">
          {/* Preview card - left */}
          <div className="md:w-[45%] flex-shrink-0 p-6 flex items-start justify-center bg-[#fffff1] border-r border-[#001c9a]/10 overflow-y-auto navy-scrollbar">
            <div className="m-3 h-74 w-51 overflow-hidden rounded-lg bg-[linear-gradient(to_right,#0213b0,#0213b0_10px,#e9f0f8_10px,#e9f0f8)] bg-[length:20px_100%] p-3 md:h-[420px] md:w-[280px] md:p-5">
              <div className="bg-white relative h-full w-full p-4 rounded-md flex flex-col justify-between">
                <div className="space-y-4">
                  <div className="font-serif italic text-sm text-[#001c9a] leading-relaxed">
                    {selected?.message || "Chọn mẫu thiệp bên phải"}
                  </div>
                </div>
                {/* Vinamilk logo SVG — bottom */}
                <svg
                  width="80"
                  height="26"
                  viewBox="0 0 115 38"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                  className="text-[#001c9a] mt-auto"
                >
                  <path d="M1.47756 37.3725H4.42017C4.49115 37.3725 4.53794 37.3255 4.53794 37.2542V35.8293C4.53794 35.758 4.49115 35.711 4.42017 35.711H3.07308C3.0021 35.711 2.95532 35.664 2.95532 35.5927V33.9895C2.95532 33.9182 3.0021 33.8712 3.07308 33.8712H4.31369C4.38468 33.8712 4.43146 33.8242 4.43146 33.7528V32.341C4.43146 32.2696 4.38468 32.2226 4.31369 32.2226H3.07308C3.0021 32.2226 2.95532 32.1756 2.95532 32.1043V30.9518C2.95532 30.7978 3.03759 30.7151 3.28604 30.7151C3.58127 30.7151 4.00717 30.7751 4.33789 30.9761C4.46857 31.0474 4.53955 31.0231 4.53955 30.8934V29.1833C4.53955 29.1119 4.49277 29.0649 4.42178 29.0649H1.24202C1.13554 29.0649 1.07585 29.1363 1.10005 29.2432C1.20653 29.7425 1.24202 30.0262 1.24202 31.0474V37.1375C1.24202 37.2915 1.32429 37.3742 1.47756 37.3742V37.3725Z" fill="currentColor" />
                  <path d="M17.607 0.863487H10.9297C10.4925 0.863487 10.2021 1.24118 10.3408 1.67074C10.7942 3.0648 10.7538 4.31135 10.6635 5.47522L9.61969 19.1856C9.58904 19.5828 9.10183 19.5828 9.07279 19.1856L8.04029 5.47522C7.94995 4.31135 7.90962 3.06642 8.36295 1.67074C8.5033 1.24118 8.21291 0.863487 7.7741 0.863487H0.916071C0.404663 0.863487 0.0836208 1.33844 0.286893 1.82474C0.895098 3.28364 1.19033 4.7166 1.36295 5.91613L4.20392 24.9855C4.27975 25.4913 4.71211 25.8657 5.2219 25.8657H13.2899C13.7997 25.8657 14.232 25.4913 14.3079 24.9855L17.1601 5.91613C17.3344 4.7166 17.6296 3.28364 18.2362 1.82474C18.4395 1.33682 18.1168 0.863487 17.607 0.863487Z" fill="currentColor" />
                  <path d="M24.1763 7.15064C23.5471 7.41811 22.8227 7.564 22.029 7.564C21.2353 7.564 20.5093 7.41973 19.8801 7.15388C19.3477 6.92857 18.9234 7.19603 18.9234 7.77473V25.1777C18.9234 25.5587 19.23 25.8667 19.6091 25.8667H24.4473C24.8264 25.8667 25.1329 25.5587 25.1329 25.1777V7.77148C25.1329 7.19117 24.707 6.9237 24.1746 7.15064H24.1763Z" fill="currentColor" />
                  <path d="M22.0016 0.492676C19.9672 0.492676 18.6218 1.59657 18.6766 3.41857C18.7315 5.1579 19.9963 6.34447 22.029 6.34447C24.0617 6.34447 25.354 5.1579 25.354 3.41857C25.354 1.67925 24.0359 0.492676 22.0016 0.492676Z" fill="currentColor" />
                </svg>
              </div>
            </div>
          </div>

          {/* Options list - right */}
          <div className="md:w-[55%] p-6 overflow-y-auto bg-[#fffff1]">
            <p className="text-xs font-bold text-[#001c9a] uppercase tracking-widest mb-5">Chọn lời nhắn</p>
            <div className="space-y-5">
              {cards.map((card) => (
                <div
                  key={card.id}
                  onClick={() => setLocalId(card.id)}
                  className="flex gap-3 cursor-pointer group"
                >
                  {/* Radio circle */}
                  <div className="pt-1 flex-shrink-0">
                    <div
                      className={`w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors ${localId === card.id
                          ? "border-[#001c9a]"
                          : "border-[#001c9a]/30 group-hover:border-[#001c9a]/60"
                        }`}
                    >
                      {localId === card.id && (
                        <div className="w-2.5 h-2.5 rounded-full bg-[#001c9a]" />
                      )}
                    </div>
                  </div>
                  {/* Content */}
                  <div className="flex-1">
                    <p
                      className={`text-base mb-1 font-serif italic transition-colors ${localId === card.id ? "text-[#001c9a]" : "text-[#001c9a]/70 group-hover:text-[#001c9a]"
                        }`}
                    >
                      {card.title}
                    </p>
                    <p className="text-[12px] text-[#001c9a]/60 uppercase leading-relaxed tracking-wide">
                      {card.message}
                    </p>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Footer */}
        <div className="flex items-center justify-end px-6 py-4 border-t border-[#001c9a]/10 gap-3 bg-[#fffff1] flex-shrink-0">
          <button
            type="button"
            onClick={() => onConfirm(null, false)}
            className="flex items-center gap-2 text-sm text-[#001c9a] border border-[#001c9a]/25 hover:bg-[#001c9a]/5 px-5 py-2.5 rounded-lg font-medium transition-colors"
          >
            <Trash2 size={15} /> Không gửi kèm thiệp
          </button>
          <button
            type="button"
            onClick={() => onConfirm(localId, true)}
            className="flex items-center gap-2 bg-[#001c9a] text-white px-7 py-2.5 rounded-lg font-bold text-sm hover:bg-[#001c9a]/90 transition-colors"
          >
            <Check size={15} /> Xác nhận lựa chọn
          </button>
        </div>
      </div>
    </div>
  );
}