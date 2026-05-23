import React from 'react';
import { catalogApi } from '@/lib/api';
import HomeClient from '@/components/home/HomeClient';

export default async function Home() {
  let homeData = null;
  try {
    const res = await catalogApi.getHomeData();
    homeData = res;
  } catch (error) {
    console.error("Failed to fetch home data:", error);
  }

  return (
    <div className="bg-cream min-h-screen">
      {homeData ? (
        <HomeClient data={homeData} />
      ) : (
        <div className="flex items-center justify-center min-h-[50vh] text-v-navy text-xl">
          Đang tải thông tin trang chủ...
        </div>
      )}
    </div>
  );
}
