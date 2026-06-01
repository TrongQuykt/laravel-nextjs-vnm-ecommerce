"use client";

import React, { useState, useEffect, useRef } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { Search, X, History, TrendingUp, Sparkles, ArrowRight, Trash2 } from "lucide-react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { catalogApi, getImageUrl } from "@/lib/api";
import Image from "next/image";

interface SearchSidebarProps {
  isOpen: boolean;
  onClose: () => void;
}

export default function SearchSidebar({ isOpen, onClose }: SearchSidebarProps) {
  const router = useRouter();
  const [query, setQuery] = useState("");
  const [suggestions, setSuggestions] = useState<{ trending: any[], products: any[], recommendations: any[] }>({
    trending: [],
    products: [],
    recommendations: []
  });
  const [recentSearches, setRecentSearches] = useState<string[]>([]);
  const inputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    if (isOpen) {
      setTimeout(() => inputRef.current?.focus(), 100);
      const saved = localStorage.getItem("vinamilk_recent_searches");
      if (saved) setRecentSearches(JSON.parse(saved));
      catalogApi.getSearchSuggestions("").then(setSuggestions);
    }
  }, [isOpen]);

  useEffect(() => {
    const delayDebounce = setTimeout(() => {
      if (query.trim()) {
        catalogApi.getSearchSuggestions(query).then(setSuggestions);
      }
    }, 300);
    return () => clearTimeout(delayDebounce);
  }, [query]);

  const handleSearch = (searchTerm: string) => {
    if (!searchTerm.trim()) return;
    const updatedRecent = [searchTerm, ...recentSearches.filter(s => s !== searchTerm)].slice(0, 10);
    setRecentSearches(updatedRecent);
    localStorage.setItem("vinamilk_recent_searches", JSON.stringify(updatedRecent));
    onClose();
    router.push(`/search?q=${encodeURIComponent(searchTerm)}`);
  };

  const clearRecent = () => {
    setRecentSearches([]);
    localStorage.removeItem("vinamilk_recent_searches");
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
            className="fixed inset-0 bg-black/40 z-[200]"
          />

          <motion.div
            initial={{ x: "20%", opacity: 0, scale: 0.95 }}
            animate={{ x: 0, opacity: 1, scale: 1 }}
            exit={{ x: "20%", opacity: 0, scale: 0.95 }}
            transition={{ type: "spring", damping: 30, stiffness: 300 }}
            className="fixed right-4 top-4 bottom-4 w-full max-w-[480px] bg-[#fefef0] z-[201] rounded-[6px] flex flex-col overflow-hidden border border-[#001c9a]/5"
          >
            {/* Header */}
            <div className="p-8 pb-4">
              <div className="flex items-center justify-between mb-8">
                <div className="flex items-center gap-3">
                  <span className="text-[14px] font-bold text-[#001c9a]">Tìm trong Vinamilk</span>
                </div>
                <button onClick={onClose} className="flex items-center gap-1 text-[#001c9a] hover:opacity-70 transition-opacity">
                  <span className="text-[12px] font-bold">Đóng</span>
                </button>
              </div>

              {/* Input Area */}
              <div className="relative">
                <input
                  ref={inputRef}
                  type="text"
                  value={query}
                  onChange={(e) => setQuery(e.target.value)}
                  onKeyDown={(e) => e.key === "Enter" && handleSearch(query)}
                  placeholder=""
                  className="w-full bg-transparent border-b border-[#001c9a] py-3 text-[14px] text-[#001c9a] outline-none placeholder:text-[#001c9a]/20"
                />
              </div>
            </div>

            {/* Scrollable Content */}
            <div className="flex-1 overflow-y-auto px-8 pb-8 navy-scrollbar">
              {!query ? (
                <div className="space-y-10 pt-4">
                  {/* Recent Searches */}
                  {recentSearches.length > 0 && (
                    <section>
                      <div className="flex items-center justify-between mb-4">
                        <div className="flex items-center gap-2 text-[#001c9a] text-[11px] font-bold uppercase tracking-widest">
                          <History size={12} className="opacity-50" />
                          Tìm gần đây
                        </div>
                        <button onClick={clearRecent} className="text-[#001c9a]/30 hover:text-red-500 transition-colors">
                          <Trash2 size={12} />
                        </button>
                      </div>
                      <div className="flex flex-wrap gap-2">
                        {recentSearches.map((term, i) => (
                          <button
                            key={i}
                            onClick={() => handleSearch(term)}
                            className="px-3.5 py-1.5 bg-white/40 border border-[#001c9a]/10 rounded-full text-[11px] text-[#001c9a] hover:bg-[#001c9a] hover:text-white transition-all shadow-sm"
                          >
                            {term}
                          </button>
                        ))}
                      </div>
                    </section>
                  )}

                  {/* Trending */}
                  <section>
                    <div className="flex items-center gap-2 text-[#001c9a] text-[11px] font-bold uppercase tracking-widest mb-4">
                      <TrendingUp size={12} className="opacity-50" />
                      Xu hướng
                    </div>
                    <div className="flex flex-col gap-4">
                      {suggestions.trending?.map((item, i) => (
                        <button
                          key={i}
                          onClick={() => handleSearch(item.keyword)}
                          className="text-left text-[14px] font-medium text-[#001c9a] hover:translate-x-1 transition-transform"
                        >
                          {item.keyword}
                        </button>
                      ))}
                    </div>
                  </section>

                  {/* Dành cho bạn / Recommend */}
                  <section>
                    <div className="flex items-center gap-2 text-[#001c9a] text-[11px] font-bold uppercase tracking-widest mb-6 pt-4 border-t border-[#001c9a]/5">
                      <Sparkles size={12} className="opacity-50" />
                      Dành cho bạn
                    </div>
                    <div className="flex flex-col gap-6">
                      {suggestions.recommendations?.map((product: any) => (
                        <Link
                          key={product.id}
                          href={`/products/${product.slug}`}
                          onClick={onClose}
                          className="flex gap-4 group"
                        >
                          <div className="w-14 h-14 bg-white/40 rounded-xl p-2 flex items-center justify-center group-hover:scale-105 transition-transform">
                            {product.main_image && (
                              <Image
                                src={getImageUrl(product.main_image) || ""}
                                alt={product.name}
                                width={40} height={40}
                                className="object-contain"
                              />
                            )}
                          </div>
                          <div className="flex-1 py-1 flex flex-col justify-center">
                            <h4 className="text-[#001c9a] font-bold text-[13px] leading-tight mb-0.5 group-hover:text-v-blue transition-colors">
                              {product.name}
                            </h4>
                            {product.variants?.[0] && (
                              <p className="text-[#001c9a]/50 text-[11px] font-medium uppercase tracking-tight">
                                {product.variants[0].volume} / {product.variants[0].packaging_type}
                              </p>
                            )}
                          </div>
                        </Link>
                      ))}
                    </div>
                  </section>
                </div>
              ) : (
                <div className="space-y-10 pt-4">
                  {/* Product Matches */}
                  <section>
                    <div className="flex flex-col gap-5">
                      {suggestions.products?.map((product: any) => (
                        <Link
                          key={product.id}
                          href={`/products/${product.slug}`}
                          onClick={onClose}
                          className="flex gap-5 group"
                        >
                          <div className="w-16 h-16 bg-white/40 rounded-xl p-2.5 flex items-center justify-center group-hover:scale-105 transition-transform">
                            {product.main_image && (
                              <Image
                                src={getImageUrl(product.main_image) || ""}
                                alt={product.name}
                                width={50}
                                height={50}
                                className="object-contain"
                              />
                            )}
                          </div>
                          <div className="flex-1 py-1 flex flex-col justify-center">
                            <h4 className="text-[#001c9a] font-bold text-[13px] group-hover:text-v-blue transition-colors leading-snug">
                              {product.name}
                            </h4>
                            {product.variants?.[0] && (
                              <p className="text-[#001c9a]/50 text-[11px] font-medium uppercase mt-0.5">
                                {product.variants[0].volume} / {product.variants[0].packaging_type}
                              </p>
                            )}
                          </div>
                        </Link>
                      ))}
                    </div>
                  </section>

                  {/* View All Button */}
                  <button
                    onClick={() => handleSearch(query)}
                    className="w-full bg-[#001c9a] text-white py-4 rounded-xl font-bold text-[12px] uppercase tracking-widest flex items-center justify-center gap-3 hover:bg-[#0213b0] transition-colors shadow-lg"
                  >
                    Xem toàn bộ kết quả phù hợp
                    <ArrowRight size={14} />
                  </button>
                </div>
              )}
            </div>
          </motion.div>
        </>
      )}
    </AnimatePresence>
  );
}
