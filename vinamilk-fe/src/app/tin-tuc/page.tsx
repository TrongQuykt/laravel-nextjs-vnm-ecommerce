import React from 'react';
import BlogListingClient from '@/components/blogs/BlogListingClient';
import { BlogCategory } from '@/types';

async function getBlogData(): Promise<BlogCategory[]> {
  const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/v1';
  const res = await fetch(`${apiUrl}/blogs`, {
    cache: 'no-store'
  });
  
  if (!res.ok) return [];
  return res.json();
}

export const metadata = {
  title: 'Vinamilk - Luôn vui khỏe | Blog kiến thức dinh dưỡng',
  description: 'Cập nhật kiến thức dinh dưỡng, làm đẹp và sức khỏe từ các chuyên gia Vinamilk.',
};

export default async function BlogPage() {
  const categories = await getBlogData();

  return <BlogListingClient categories={categories} />;
}
