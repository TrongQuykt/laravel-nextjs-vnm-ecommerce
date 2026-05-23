'use client';

import React from 'react';
import Link from 'next/link';
import Image from 'next/image';
import { BlogPost } from '@/types';
import { format } from 'date-fns';
import { vi } from 'date-fns/locale';

interface BlogCardProps {
  post: BlogPost;
}

const BlogCard: React.FC<BlogCardProps> = ({ post }) => {
  const storageUrl = process.env.NEXT_PUBLIC_STORAGE_URL || 'http://localhost:8000/storage';
  const bannerUrl = post.banner_image
    ? `${storageUrl}/${post.banner_image}`
    : '/images/placeholder-blog.jpg';

  return (
    <Link href={`/tin-tuc/${post.slug}`} className="group block h-full">
      <div className="flex flex-col h-full bg-transparent">
        {/* Image Container */}
        <div className="relative aspect-[3/2] overflow-hidden rounded-[12px] mb-4">
          <Image
            src={bannerUrl}
            alt={post.title}
            fill
            className="object-cover"
          />
        </div>

        {/* Content */}
        <div className="flex flex-col flex-grow items-start px-1">
          <span className="text-[#002094] text-[10px] font-sans-serif mb-2 opacity-80">
            {post.category?.name || 'TIN TỨC'}
          </span>

          <h3 className="text-[#002094] text-[15px] md:text-[17px] font-black leading-[1.3] mb-3 line-clamp-3 group-hover:opacity-70 transition-opacity tracking-tight">
            {post.title}
          </h3>

          <span className="mt-auto text-gray-400 text-[11px] font-medium">
            {format(new Date(post.created_at || post.published_at), 'dd/MM/yyyy', { locale: vi })}
          </span>
        </div>
      </div>
    </Link>
  );
};

export default BlogCard;
