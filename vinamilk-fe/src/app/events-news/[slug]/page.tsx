import React from 'react';
import BlogDetailClient from '@/components/blogs/BlogDetailClient';
import { notFound } from 'next/navigation';

async function getEventNews(slug: string) {
  const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/v1';
  const res = await fetch(`${apiUrl}/events-news/${slug}`, {
    cache: 'no-store'
  });
  
  if (!res.ok) return null;
  return res.json();
}

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const data = await getEventNews(slug);
  if (!data) return { title: 'Sự kiện không tồn tại' };

  return {
    title: `${data.title} | Vinamilk Sự kiện`,
    description: data.excerpt || data.title,
    openGraph: {
      images: [data.banner_image ? `${process.env.NEXT_PUBLIC_API_URL}/storage/${data.banner_image}` : ''],
    },
  };
}

export default async function EventNewsPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const data = await getEventNews(slug);

  if (!data) {
    notFound();
  }

  return (
    <BlogDetailClient 
      post={data} 
      relatedPosts={[]} 
      isEventNews={true}
    />
  );
}
