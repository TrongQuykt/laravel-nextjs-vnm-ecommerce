'use client';

import React from 'react';
import { BlogPost } from '@/types';
import Link from 'next/link';
import { format } from 'date-fns';

interface BlogSidebarRelatedPostsProps {
  posts: BlogPost[];
}

const BlogSidebarRelatedPosts: React.FC<BlogSidebarRelatedPostsProps> = ({ posts }) => {
  if (!posts || posts.length === 0) return null;

  return (
    <div className="mt-12 pt-12">
      <h4 className="text-[#002094] text-[15px] font-black mb-4 tracking-tight">
        Có thể bạn quan tâm
      </h4>
      <div className="w-full h-[1px] bg-[#002094]/10 mb-8" />
      
      <div className="space-y-10">
        {posts.map((post) => (
          <div key={post.id} className="group">
            <Link 
              href={`/tin-tuc/${post.slug}`}
              className="text-[#002094] text-[16px] md:text-[18px] font-bold leading-snug hover:opacity-70 transition-opacity line-clamp-3 block mb-3 tracking-tight"
            >
              {post.title}
            </Link>
            <p className="text-gray-400 text-[12px] font-bold tracking-widest mb-8">
              {format(new Date(post.published_at || post.created_at), 'dd/MM/yyyy')}
            </p>
            <div className="w-full h-[1px] bg-[#002094]/10" />
          </div>
        ))}
      </div>
    </div>
  );
};

export default BlogSidebarRelatedPosts;
