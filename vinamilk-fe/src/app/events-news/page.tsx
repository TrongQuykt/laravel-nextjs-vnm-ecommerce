import React from 'react';
import EventNewsListingClient from '@/components/blogs/EventNewsListingClient';

async function getEventData() {
  const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/v1';
  const res = await fetch(`${apiUrl}/events-news`, {
    cache: 'no-store'
  });
  
  if (!res.ok) return [];
  return res.json();
}

export const metadata = {
  title: 'Vinamilk - Luôn vui khỏe | Tin sự kiện',
  description: 'Cập nhật các sự kiện, hoạt động và chương trình khuyến dụng từ Vinamilk.',
};

export default async function EventsNewsPage() {
  const events = await getEventData();

  return <EventNewsListingClient events={events} />;
}
