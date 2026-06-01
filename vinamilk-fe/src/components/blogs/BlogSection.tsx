'use client';

import React from 'react';
import Link from 'next/link';
import { BlogCategory } from '@/types';
import BlogCard from './BlogCard';
import { ChevronRight } from 'lucide-react';

interface BlogSectionProps {
  category: BlogCategory;
}

const BlogSection: React.FC<BlogSectionProps> = ({ category }) => {
  if (!category.posts || category.posts.length === 0) return null;

  return (
    <section className="mb-20">
      <div className="relative flex items-center justify-center mb-10 pb-4">
        <h2 className="text-2xl md:text-3xl lg:text-[32px] font-sans font-black text-[#002094] tracking-tight">
          {category.name}
        </h2>

        <Link
          href={`/tin-tuc/danh-muc/${category.slug}`}
          className="absolute right-0 flex items-center text-v-navy hover:text-v-pink font-semibold text-[10px] md:text-xs transition-all group tracking-tighter"
        >
          Xem thêm
          <ChevronRight className="w-3.5 h-3.5 ml-0.5 transition-transform group-hover:translate-x-1" />
        </Link>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-12">
        {category.posts.slice(0, 4).map((post) => (
          <BlogCard key={post.id} post={{ ...post, category }} />
        ))}
      </div>
    </section>
  );
};

export default BlogSection;
