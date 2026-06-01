'use client';

import React, { useEffect, useMemo, useState } from 'react';
import Link from 'next/link';
import { Home, ChevronRight } from 'lucide-react';
import { BlogPost, BlogCategory } from '@/types';
import TableOfContents from './TableOfContents';
import RelatedProducts from './RelatedProducts';
import BlogCard from './BlogCard';
import BlogSidebarProducts from './BlogSidebarProducts';
import BlogMentionedProductsGrid from './BlogMentionedProductsGrid';
import BlogSidebarRelatedPosts from './BlogSidebarRelatedPosts';
import Image from 'next/image';
import { format } from 'date-fns';
import { vi } from 'date-fns/locale';
import { motion } from 'framer-motion';

interface BlogDetailClientProps {
  post: BlogPost;
  relatedPosts: BlogPost[];
}

const BlogDetailClient: React.FC<BlogDetailClientProps> = ({ post, relatedPosts }) => {
  const [isMounted, setIsMounted] = useState(false);

  useEffect(() => {
    setIsMounted(true);
  }, []);

  // Inject IDs into headings for TOC and jump links
  const processedContent = useMemo(() => {
    const content = post.content || '';
    if (!isMounted) return content;
    
    const parser = new DOMParser();
    const doc = parser.parseFromString(content, 'text/html');
    const headings = doc.querySelectorAll('h2, h3');
    
    headings.forEach((heading, index) => {
      const text = heading.textContent || '';
      const id = text.toLowerCase().replace(/\s+/g, '-') + '-' + index;
      heading.id = id;
    });
    
    return doc.body.innerHTML;
  }, [post.content, isMounted]);

  // Split content by [PRODUCT] tag to insert grid
  const contentParts = useMemo(() => {
    return processedContent.split('[PRODUCT]');
  }, [processedContent]);

  const storageUrl = process.env.NEXT_PUBLIC_STORAGE_URL || 'http://localhost:8000/storage';
  const bannerUrl = post.banner_image 
    ? `${storageUrl}/${post.banner_image}`
    : '/images/placeholder-blog.jpg';

  return (
    <main className="min-h-screen bg-[#FDFCF0] pb-20 pt-40">
      {/* Breadcrumbs */}
      <div className="max-w-[1440px] mx-auto px-10 md:px-20 pt-6">
        <nav className="flex items-center space-x-2 text-[11px] font-bold text-[#002094] uppercase tracking-tighter opacity-60">
          <Link href="/" className="hover:opacity-100">
            <Home className="w-3.5 h-3.5" />
          </Link>
          <ChevronRight className="w-3 h-3 opacity-30" />
          <Link href="/tin-tuc" className="hover:opacity-100">Luôn vui khỏe</Link>
          <ChevronRight className="w-3 h-3 opacity-30" />
          <span className="opacity-100 line-clamp-1">{post.title}</span>
        </nav>
      </div>

      <div className="max-w-[1440px] mx-auto px-10 md:px-20 mt-12">
        <div className="flex flex-col lg:flex-row gap-16 relative">
          {/* Sidebar - Left Column */}
          <aside className="lg:w-1/4">
            <div className="sticky top-32">
              <div className="bg-[#E9EDF5] rounded-t-xl overflow-hidden shadow-sm zigzag-bottom pb-4">
                {/* TOC Header - Blog Title */}
                <div className="p-5 border-b border-[#002094]/10">
                  <h4 className="text-[#002094] text-xs font-black uppercase leading-tight tracking-tight">
                    {post.title}
                  </h4>
                </div>
                
                {/* TOC List */}
                <div className="max-h-[66vh] overflow-y-auto scrollbar-hide px-2 py-4">
                  <TableOfContents content={post.content || ''} />
                </div>
              </div>

              {/* [NEW] Sidebar Products - Ảnh #1 */}
              <BlogSidebarProducts products={post.products || []} />

              {/* [NEW] Suggested Blogs - Ảnh #3 */}
              <BlogSidebarRelatedPosts posts={relatedPosts || []} />
            </div>
          </aside>

          {/* Main Content - Right Column */}
          <article className="lg:w-3/4">
            <div className="mb-10">
              <span className="text-[#002094] text-[11px] font-black uppercase tracking-widest mb-4 block">
                {post.category?.name || 'TIN TỨC'}
              </span>
              <h1 className="text-4xl md:text-5xl font-black text-[#002094] leading-[1.1] mb-6 tracking-tight">
                {post.title}
              </h1>
              <div className="flex items-center text-gray-500 text-xs font-medium space-x-4">
                <span className="font-bold text-[#002094]">Vinamilk</span>
                <span className="w-1 h-1 bg-gray-300 rounded-full" />
                <span>{format(new Date(post.published_at || post.created_at), "dd 'tháng' MM yyyy", { locale: vi })}</span>
              </div>
            </div>

            {/* Banner */}
            {bannerUrl && (
              <div className="relative aspect-[21/9] rounded-2xl overflow-hidden mb-12 shadow-sm">
                <Image 
                  src={bannerUrl} 
                  alt={post.title} 
                  fill 
                  className="object-cover"
                />
              </div>
            )}

            {/* Content Body with [PRODUCT] Injection - Ảnh #2 */}
            <div className="prose prose-lg max-w-none prose-headings:text-[#002094] prose-headings:font-black prose-h2:text-5xl prose-h3:text-2xl prose-p:text-[#002094] prose-p:opacity-90 prose-img:rounded-2xl prose-img:mx-auto prose-figcaption:text-center prose-figcaption:text-sm prose-figcaption:text-gray-500 prose-figcaption:mt-3 prose-figcaption:font-medium">
              {contentParts.map((part, index) => (
                <React.Fragment key={index}>
                  <div dangerouslySetInnerHTML={{ __html: part }} />
                  {index < contentParts.length - 1 && (
                    <BlogMentionedProductsGrid products={post.products || []} />
                  )}
                </React.Fragment>
              ))}
            </div>

            {/* Related Products Section (Bottom) */}
            {post.products && post.products.length > 0 && contentParts.length === 1 && (
              <div className="mt-20 pt-20 border-t border-gray-200/50">
                <RelatedProducts products={post.products} />
              </div>
            )}
          </article>
        </div>

        {/* Bottom Related Posts Section */}
        {relatedPosts && relatedPosts.length > 0 && (
          <div className="mt-24 pt-20 border-t border-gray-200/50">
            <h2 className="text-3xl font-black text-[#002094] mb-12 text-center uppercase tracking-tight">
              Bài viết cùng danh mục
            </h2>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
              {relatedPosts.slice(0, 4).map((relatedPost) => (
                <BlogCard key={relatedPost.id} post={relatedPost} />
              ))}
            </div>
          </div>
        )}
      </div>
    </main>
  );
};

export default BlogDetailClient;
