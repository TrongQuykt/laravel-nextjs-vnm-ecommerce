"use client";

import React, { useEffect, useState } from "react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { fetchApi } from "@/lib/api";

interface SupportPage {
  id: number;
  slug: string;
  title: string;
}

export default function SupportLayout({ children }: { children: React.ReactNode }) {
  const [pages, setPages] = useState<SupportPage[]>([]);
  const pathname = usePathname();

  useEffect(() => {
    fetchApi<SupportPage[]>("/support-pages")
      .then((data) => setPages(data))
      .catch((err) => console.error("Error fetching support pages:", err));
  }, []);

  return (
    <div className="bg-[#fffff1] min-h-screen pt-40 pb-20">
      <div className="max-w-[1440px] mx-auto px-10 md:px-20">
        <div className="flex gap-12 max-lg:flex-col md:gap-20">
          {/* Sidebar */}
          <div className="w-full lg:w-[320px] flex-shrink-0">
            <div className="sticky top-28">
              <h2 className="text-[42px] font-bold text-[#002060] mb-6 tracking-tight">
                Hỗ trợ
              </h2>
              <nav className="flex flex-col gap-6">
                {pages.map((page) => {
                  const isActive = pathname === `/support/${page.slug}`;
                  return (
                    <Link
                      key={page.id}
                      href={`/support/${page.slug}`}
                      className={`text-[15px] transition-all hover:text-[#002060] leading-snug tracking-wide ${isActive
                        ? "text-[#002060] font-bold"
                        : "text-[#002060]/80 font-medium"
                        }`}
                    >
                      {page.title}
                    </Link>
                  );
                })}
              </nav>
            </div>
          </div>

          {/* Content */}
          <main className="flex-grow min-w-0">
            {children}
          </main>
        </div>
      </div>
    </div>
  );
}
