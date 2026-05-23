import React from 'react';
import BlogDetailClient from '@/components/blogs/BlogDetailClient';
import { BlogPost } from '@/types';
import { notFound } from 'next/navigation';

async function getBlogPost(slug: string) {
  const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/v1';
  const res = await fetch(`${apiUrl}/blogs/${slug}`, {
    cache: 'no-store'
  });
  
  if (!res.ok) return null;
  return res.json();
}

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const data = await getBlogPost(slug);
  if (!data) return { title: 'Bài viết không tồn tại' };

  return {
    title: `${data.post.title} | Vinamilk Blog`,
    description: data.post.excerpt || data.post.title,
    openGraph: {
      images: [data.post.banner_image ? `${process.env.NEXT_PUBLIC_API_URL}/storage/${data.post.banner_image}` : ''],
    },
  };
}

export default async function BlogPostPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const data = await getBlogPost(slug);

  if (!data || !data.post) {
    notFound();
  }

  return (
    <BlogDetailClient 
      post={data.post} 
      relatedPosts={data.related_posts} 
    />
  );
}
