'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { Home, ChevronRight } from 'lucide-react';
import { BlogCategory } from '@/types';
import BlogSection from './BlogSection';
import { motion, AnimatePresence } from 'framer-motion';
import { format } from 'date-fns';
import { vi } from 'date-fns/locale';

interface BlogListingClientProps {
  categories: BlogCategory[];
  initialCategory?: string;
}

const BlogListingClient: React.FC<BlogListingClientProps> = ({ categories, initialCategory = 'all' }) => {
  const [activeCategory, setActiveCategory] = useState<string>(initialCategory);

  const filteredCategories = activeCategory === 'all'
    ? categories
    : categories.filter(cat => cat.slug === activeCategory);

  return (
    <main className="min-h-screen bg-[#FDFCF0] pb-20 pt-30 md:pt-30">
      {/* Breadcrumbs - Centered */}
      <div className="max-w-[1440px] mx-auto px-10 md:px-20 mb-12">
        <nav className="flex items-center justify-center space-x-3 text-[11px] font-bold text-[#002094] opacity-60">
          <Link href="/" className="hover:opacity-100 transition-opacity">
            <Home className="w-3.5 h-3.5" />
          </Link>
          <ChevronRight className="w-3 h-3 opacity-30" />
          <span className="text-[#002094]">Luôn vui khỏe</span>
        </nav>
      </div>

      {/* Hero Title */}
      <div className="pb-16 text-center">
        <h1 className="text-6xl md:text-7xl lg:text-[64px] font-sans font-black text-[#002094] mb-8 tracking-tight">
          Luôn vui khỏe
        </h1>

        {/* Category Tabs - Elegant & Minimalist */}
        <div className="max-w-[1440px] mx-auto px-8 md:px-16 overflow-hidden">
          <div className="flex items-center justify-center space-x-6 md:space-x-8 border-b border-[#2b59ff59] whitespace-nowrap overflow-x-auto scrollbar-hide pb-0">

            <button
              onClick={() => setActiveCategory('all')}
              className={`text-[11px] md:text-[13px] font-medium transition-all relative pb-3 ${activeCategory === 'all'
                ? 'text-[#002094]'
                : 'text-[#002094]/70 hover:text-[#002094]'
                }`}
            >
              <span className="relative inline-block">
                Khám phá
                {activeCategory === 'all' && (
                  <motion.div
                    layoutId="activeTab"
                    className="absolute bottom-[-1px] left-0 w-full h-[2px] bg-[#002094]"
                  />
                )}
              </span>
            </button>

            {categories.map((category) => (
              <button
                key={category.id}
                onClick={() => setActiveCategory(category.slug)}
                className={`text-[11px] md:text-[13px] font-medium transition-all relative pb-3 ${activeCategory === category.slug
                  ? 'text-[#002094]'
                  : 'text-[#002094]/70 hover:text-[#002094]'
                  }`}
              >
                <span className="relative inline-block">
                  {category.name}
                  {activeCategory === category.slug && (
                    <motion.div
                      layoutId="activeTab"
                      className="absolute bottom-[-1px] left-0 w-full h-[2px] bg-[#002094]"
                    />
                  )}
                </span>
              </button>
            ))}

          </div>
        </div>
      </div>

      {/* Main Content Area - Aligned with Navbar */}
      <div className="max-w-[1440px] mx-auto px-10 md:px-20">
        <AnimatePresence mode="wait">
          <motion.div
            key={activeCategory}
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -20 }}
            transition={{ duration: 0.3 }}
          >
            {filteredCategories.map((category) => (
              <BlogSection key={category.id} category={category} />
            ))}
          </motion.div>
        </AnimatePresence>
      </div>
    </main>
  );
};

export default BlogListingClient;
