"use client";

import Link from "next/link";
import { ChevronRight, Home } from "lucide-react";

interface BreadcrumbsProps {
  items: { label: string; href?: string }[];
}

export default function Breadcrumbs({ items }: BreadcrumbsProps) {
  return (
    <nav className="flex items-center gap-2 text-[10px] md:text-[11px] font-bold uppercase tracking-widest text-v-navy/40 mb-6">
      <Link href="/" className="hover:text-v-navy transition-colors flex items-center gap-1.5">
        <Home size={12} />
      </Link>
      <ChevronRight size={12} />
      {items.map((item, index) => (
        <div key={index} className="flex items-center gap-2">
          {item.href ? (
            <Link href={item.href} className="hover:text-v-navy transition-colors">
              {item.label}
            </Link>
          ) : (
            <span className="text-v-navy/80">{item.label}</span>
          )}
          {index < items.length - 1 && <ChevronRight size={12} />}
        </div>
      ))}
    </nav>
  );
}
