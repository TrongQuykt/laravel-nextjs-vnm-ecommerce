"use client";

import React, { useState } from "react";
import { PromotionTerm } from "@/types";
import { motion, AnimatePresence } from "framer-motion";
import { Plus, Minus } from "lucide-react";

interface PromotionTermsProps {
  terms: PromotionTerm[];
}

export const PromotionTerms = ({ terms }: PromotionTermsProps) => {
  const [openId, setOpenId] = useState<number | null>(null);

  if (!terms || terms.length === 0) return null;

  return (
    <div id="terms" className="scroll-mt-24 mb-32 w-full">
      {/* Header */}
      <div className="flex items-center gap-4 mb-10">
        <span className="text-[10px] font-black uppercase tracking-[0.35em] text-[#001c9a]/40">03</span>
        <h2 className="text-sm font-black uppercase tracking-[0.25em] text-[#001c9a]">
          Thể lệ chương trình
        </h2>
        <div className="flex-1 h-px bg-[#001c9a]/10" />
      </div>

      {/* Accordion */}
      <div className="space-y-0 w-full border-t border-[#001c9a]/10">
        {terms.map((term, idx) => {
          const isOpen = openId === term.id;
          return (
            <div key={term.id} className="border-b border-[#001c9a]/10">
              <button
                onClick={() => setOpenId(isOpen ? null : term.id)}
                className="w-full py-5 flex items-center justify-between text-left group"
              >
                <div className="flex items-center gap-4">
                  <span className="text-[10px] font-black text-[#001c9a]/20 tabular-nums">
                    {String(idx + 1).padStart(2, "0")}
                  </span>
                  <span className={`text-base md:text-lg font-bold tracking-tight transition-colors ${isOpen ? "text-[#001c9a]" : "text-[#001c9a]/70 group-hover:text-[#001c9a]"}`}>
                    {term.title}
                  </span>
                </div>
                <div className={`w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 transition-all duration-200 ${isOpen ? "bg-[#001c9a] text-white" : "bg-[#001c9a]/5 text-[#001c9a]"}`}>
                  {isOpen ? <Minus size={14} /> : <Plus size={14} />}
                </div>
              </button>

              <AnimatePresence>
                {isOpen && (
                  <motion.div
                    initial={{ height: 0, opacity: 0 }}
                    animate={{ height: "auto", opacity: 1 }}
                    exit={{ height: 0, opacity: 0 }}
                    transition={{ duration: 0.3, ease: "easeInOut" }}
                    className="overflow-hidden"
                  >
                    <div className="pb-10 pl-9 pr-2 overflow-hidden">
                      {(() => {
                        const content = term.content || "";
                        const tables = term.table_data || [];

                        const TableComponent = ({ rows }: { rows: any[] }) => {
                          if (!rows || rows.length === 0) return null;

                          // Chỉ hiện cột nếu hàng tiêu đề (hàng 0) có nội dung
                          const showCol1 = !!rows[0]?.col1;
                          const showCol2 = !!rows[0]?.col2;
                          const showCol3 = !!rows[0]?.col3;
                          const showCol4 = !!rows[0]?.col4;
                          const showCol5 = !!rows[0]?.col5;

                          return (
                            <div className="my-6 overflow-x-auto">
                              <table className="w-full border-collapse text-sm bg-transparent border border-[#001c9a]">
                                <thead>
                                  <tr>
                                    {showCol1 && <th className="border border-[#001c9a] p-3 text-[#001c9a] font-bold text-left">{rows[0].col1}</th>}
                                    {showCol2 && <th className="border border-[#001c9a] p-3 text-[#001c9a] font-bold text-left">{rows[0].col2}</th>}
                                    {showCol3 && <th className="border border-[#001c9a] p-3 text-[#001c9a] font-bold text-left">{rows[0].col3}</th>}
                                    {showCol4 && <th className="border border-[#001c9a] p-3 text-[#001c9a] font-bold text-left">{rows[0].col4}</th>}
                                    {showCol5 && <th className="border border-[#001c9a] p-3 text-[#001c9a] font-bold text-left">{rows[0].col5}</th>}
                                  </tr>
                                </thead>
                                <tbody>
                                  {rows.slice(1).map((row: any, i: number) => (
                                    <tr key={i}>
                                      {showCol1 && <td className="border border-[#001c9a] p-3 align-top text-[#001c9a]">{row.col1}</td>}
                                      {showCol2 && <td className="border border-[#001c9a] p-3 align-top text-[#001c9a]">{row.col2}</td>}
                                      {showCol3 && <td className="border border-[#001c9a] p-3 align-top text-[#001c9a]">{row.col3}</td>}
                                      {showCol4 && <td className="border border-[#001c9a] p-3 align-top text-[#001c9a]">{row.col4}</td>}
                                      {showCol5 && <td className="border border-[#001c9a] p-3 align-top text-[#001c9a]">{row.col5}</td>}
                                    </tr>
                                  ))}
                                </tbody>
                              </table>
                            </div>
                          );
                        };

                        const proseClasses = "prose prose-sm max-w-none text-[#001c9a]/70 leading-relaxed prose-headings:text-[#001c9a] prose-headings:font-bold prose-a:text-blue-600 prose-a:underline prose-ul:list-disc prose-ul:ml-5 prose-li:my-1.5 prose-ol:list-decimal prose-ol:ml-5 prose-strong:text-[#001c9a] prose-strong:font-semibold";

                        const regex = /(?:<p>)?\[TABLE(?::(\w+))?\](?:<\/p>)?/g;
                        const parts = content.split(regex);
                        
                        const elements = [];
                        for (let i = 0; i < parts.length; i += 2) {
                          const textPart = parts[i];
                          const tableId = parts[i + 1];

                          if (textPart) {
                            elements.push(
                              <div key={`text-${i}`} className={proseClasses} dangerouslySetInnerHTML={{ __html: textPart }} />
                            );
                          }

                          if (tableId !== undefined || (i + 1 < parts.length)) {
                            const searchId = tableId || "1";
                            const tableData = tables.find(t => t.table_id === searchId) || (tableId === undefined ? tables[0] : null);
                            
                            if (tableData && tableData.rows) {
                              elements.push(<TableComponent key={`table-${i}`} rows={tableData.rows} />);
                            }
                          }
                        }

                        if (tables.length > 0 && !content.includes('[TABLE')) {
                          return (
                            <>
                              <div className={proseClasses} dangerouslySetInnerHTML={{ __html: content }} />
                              {tables.map((t, idx) => (
                                <TableComponent key={idx} rows={t.rows} />
                              ))}
                            </>
                          );
                        }

                        return <>{elements}</>;
                      })()}
                    </div>
                  </motion.div>
                )}
              </AnimatePresence>
            </div>
          );
        })}
      </div>
    </div>
  );
};
