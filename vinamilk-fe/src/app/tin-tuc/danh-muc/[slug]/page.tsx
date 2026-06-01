import React from 'react';
import BlogListingClient from '@/components/blogs/BlogListingClient';
import { BlogCategory } from '@/types';
import { notFound } from 'next/navigation';

async function getCategoryData(slug: string): Promise<BlogCategory | null> {
  const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/v1';
  // Note: We'll fetch all categories and filter or have a specific endpoint
  const res = await fetch(`${apiUrl}/blogs`, { cache: 'no-store' });
  if (!res.ok) return null;
  const categories: BlogCategory[] = await res.json();
  return categories.find(cat => cat.slug === slug) || null;
}

export default async function CategoryPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/v1';
  
  // Fetch all categories for the sidebar/tabs and find the active one
  const res = await fetch(`${apiUrl}/blogs`, { cache: 'no-store' });
  if (!res.ok) notFound();
  const categories: BlogCategory[] = await res.json();
  
  const category = categories.find(cat => cat.slug === slug);
  if (!category) notFound();

  return (
    <BlogListingClient 
      categories={categories} 
      initialCategory={slug} 
    />
  );
}
