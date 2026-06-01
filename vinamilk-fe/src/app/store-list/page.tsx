'use client';

import React, { useState, useEffect, useMemo } from 'react';
import dynamic from 'next/dynamic';
import { ChevronLeft, ChevronDown, MapPin } from 'lucide-react';
import Navbar from '@/components/layout/Navbar';

// Dynamically import map component to avoid SSR issues with Leaflet
const StoreMap = dynamic(() => import('@/components/store-list/StoreMap'), { 
  ssr: false,
  loading: () => (
    <div className="w-full h-full flex items-center justify-center bg-[#FDFCF0]">
      <div className="w-10 h-10 border-4 border-[#002094]/20 border-t-[#002094] rounded-full animate-spin"></div>
    </div>
  )
});

import { StoreLocation } from '@/components/store-list/StoreMap';

export default function StoreListPage() {
  const [stores, setStores] = useState<StoreLocation[]>([]);
  const [selectedProvince, setSelectedProvince] = useState<string | null>(null);
  const [selectedDistrict, setSelectedDistrict] = useState<string>('Tất cả quận/huyện');
  const [selectedStore, setSelectedStore] = useState<StoreLocation | null>(null);

  // Fetch stores from API
  useEffect(() => {
    fetch(`${process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000/api/v1'}/stores`)
      .then(res => res.json())
      .then(data => {
        if (data && data.data) {
          setStores(data.data);
        }
      })
      .catch(err => console.error("Error fetching stores:", err));
  }, []);

  // Compute province counts
  const provinceCounts = useMemo(() => {
    const counts: Record<string, number> = {};
    stores.forEach(store => {
      if (store.province) {
        counts[store.province] = (counts[store.province] || 0) + 1;
      }
    });
    // Convert to sorted array
    return Object.entries(counts)
      .map(([name, count]) => ({ name, count }))
      .sort((a, b) => a.name.localeCompare(b.name));
  }, [stores]);

  // Compute districts for selected province
  const districtsInProvince = useMemo(() => {
    if (!selectedProvince) return [];
    const dists = new Set<string>();
    stores.forEach(store => {
      if (store.province === selectedProvince && store.district) {
        dists.add(store.district);
      }
    });
    return Array.from(dists).sort();
  }, [selectedProvince, stores]);

  // Filtered stores
  const filteredStores = useMemo(() => {
    if (!selectedProvince) return [];
    return stores.filter(store => {
      if (store.province !== selectedProvince) return false;
      if (selectedDistrict !== 'Tất cả quận/huyện' && store.district !== selectedDistrict) return false;
      return true;
    });
  }, [stores, selectedProvince, selectedDistrict]);

  const handleProvinceClick = (province: string) => {
    setSelectedProvince(province);
    setSelectedDistrict('Tất cả quận/huyện');
    setSelectedStore(null);
  };

  const handleBackClick = () => {
    setSelectedProvince(null);
    setSelectedDistrict('Tất cả quận/huyện');
    setSelectedStore(null);
  };

  return (
    <div className="flex flex-col h-screen overflow-hidden">
      <Navbar />
      
      <div className="relative flex-1 flex">
        {/* Map Background */}
        <div className="absolute inset-0 z-0">
          <StoreMap 
            stores={stores} 
            selectedStore={selectedStore} 
            selectedProvince={selectedProvince}
          />
        </div>

        {/* Sidebar Overlay */}
        <div className="absolute top-[120px] right-4 md:right-10 z-[50] w-[90vw] md:w-[380px] h-[calc(100vh-160px)] bg-[#FDFCF0] shadow-2xl rounded-md flex flex-col pointer-events-auto">
          
          {/* Header */}
          <div className="flex items-center justify-between p-5 border-b border-[#002094]/10 flex-shrink-0">
            {selectedProvince ? (
              <button 
                onClick={handleBackClick}
                className="text-[#002094] hover:opacity-70 transition-opacity p-1"
              >
                <ChevronLeft size={20} strokeWidth={2.5} />
              </button>
            ) : (
              <div className="w-7"></div>
            )}
            <h2 className="text-[20px] font-bold text-[#002094] text-center flex-1">
              {selectedProvince ? selectedProvince : 'Danh sách cửa hàng'}
            </h2>
            <div className="w-7"></div>
          </div>

          {/* List Content */}
          <div className="flex-1 overflow-y-auto scrollbar-thin scrollbar-thumb-[#002094]/20 p-5 navy-scrollbar">
            {!selectedProvince ? (
              /* Màn 1: Danh sách Tỉnh/Thành */
              <div className="space-y-0">
                {provinceCounts.map(prov => (
                  <button
                    key={prov.name}
                    onClick={() => handleProvinceClick(prov.name)}
                    className="w-full flex items-center justify-between py-3 border-b border-[#002094]/10 text-left hover:bg-[#002094]/5 transition-colors group"
                  >
                    <span className="text-[#002094] text-[14px]">
                      {prov.name} ({prov.count})
                    </span>
                    <ChevronLeft className="text-[#002094] opacity-50 group-hover:opacity-100 rotate-180 transition-all" size={14} />
                  </button>
                ))}
              </div>
            ) : (
              /* Màn 2: Danh sách Cửa hàng của 1 Tỉnh */
              <div className="space-y-5">
                {/* Filter */}
                <div className="flex items-center justify-between">
                  <span className="text-[#002094] font-medium text-[14px] whitespace-nowrap mr-4">Lọc theo:</span>
                  <div className="relative w-full">
                    <select
                      className="w-full bg-[#EFEFEF] text-[#002094] text-[13px] px-3 py-2 appearance-none rounded-none border-b border-[#002094] focus:outline-none"
                      value={selectedDistrict}
                      onChange={(e) => setSelectedDistrict(e.target.value)}
                    >
                      <option value="Tất cả quận/huyện">Tất cả quận/huyện</option>
                      {districtsInProvince.map(dist => (
                        <option key={dist} value={dist}>{dist}</option>
                      ))}
                    </select>
                    <div className="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-[#002094]">
                      <ChevronDown size={14} strokeWidth={1.5} />
                    </div>
                  </div>
                </div>

                <div className="text-[12px] font-medium text-[#002094] opacity-80">
                  {filteredStores.length} cửa hàng
                </div>

                {/* Store Cards */}
                <div className="space-y-0 border-t border-[#002094]/10">
                  {filteredStores.map(store => (
                    <div 
                      key={store.id} 
                      onClick={() => setSelectedStore(store)}
                      className={`py-4 border-b border-[#002094]/10 cursor-pointer transition-colors ${selectedStore?.id === store.id ? 'bg-[#002094]/5' : 'hover:bg-[#002094]/5'}`}
                    >
                      <h3 className="text-[15px] font-medium text-[#002094] mb-1 leading-snug">{store.name}</h3>
                      <p className="text-[13px] text-[#002094] opacity-80 leading-relaxed font-mono tracking-tight">
                        {store.address}{store.ward ? `, ${store.ward}` : ''}{store.district ? `, ${store.district}` : ''}, {store.province}
                      </p>
                    </div>
                  ))}
                </div>
              </div>
            )}
          </div>
        </div>
        
        {/* Floating Chat Button (UI matching the image) */}
        <button className="absolute bottom-6 right-6 z-[1000] w-12 h-12 bg-[#C1F1E8] border border-[#002094] rounded-full flex items-center justify-center shadow-lg hover:scale-105 transition-transform">
          <MapPin size={20} className="text-[#002094] fill-[#002094]" />
        </button>

      </div>
    </div>
  );
}
