"use client";

import React, { useEffect, useState } from "react";
import { useParams } from "next/navigation";
import { fetchApi } from "@/lib/api";

interface SupportPage {
  title: string;
  content: string;
}

export default function Page() {
  const { slug } = useParams();
  const [page, setPage] = useState<SupportPage | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!slug) return;

    setLoading(true);
    fetchApi<SupportPage>(`/support-pages/${slug}`)
      .then((data) => {
        setPage(data);
        setLoading(false);
      })
      .catch((err) => {
        console.error("Error fetching support page:", err);
        setLoading(false);
      });
  }, [slug]);

  if (loading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-[#002060]"></div>
      </div>
    );
  }

  if (!page) {
    return (
      <div className="text-center py-20">
        <h2 className="text-2xl font-bold text-[#002060]">Không tìm thấy trang</h2>
      </div>
    );
  }

  return (
    <article className="prose prose-lg max-w-none prose-headings:text-[#002060] prose-headings:font-serif prose-p:text-[#002060]/80">
      <h1 className="text-[42px] font-bold text-[#002060] mb-4 tracking-tight">
        {page.title}
      </h1>
      <div
        className="support-content text-[#002060]/80 leading-relaxed"
        dangerouslySetInnerHTML={{ __html: page.content }}
      />
    </article>
  );
}
