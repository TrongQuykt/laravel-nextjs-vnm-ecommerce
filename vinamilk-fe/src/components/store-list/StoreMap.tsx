'use client';

import React, { useEffect, useState, useMemo } from 'react';
import { MapContainer, TileLayer, Marker, Popup, useMap } from 'react-leaflet';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Fix for default marker icons in Leaflet with Next.js
const customIcon = new L.DivIcon({
  className: 'custom-div-icon',
  html: `<div style="background-color: #002094; width: 24px; height: 24px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); display: flex; items-center; justify-center; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);">
           <div style="background-color: white; width: 8px; height: 8px; border-radius: 50%; transform: rotate(45deg);"></div>
         </div>`,
  iconSize: [24, 24],
  iconAnchor: [12, 24],
  popupAnchor: [0, -24]
});

export interface StoreLocation {
  id: number;
  name: string;
  phone?: string;
  address: string;
  ward?: string;
  district?: string;
  province?: string;
  latitude: number | null;
  longitude: number | null;
}

interface StoreMapProps {
  stores: StoreLocation[];
  selectedStore: StoreLocation | null;
  selectedProvince: string | null;
}

const VIETNAM_CENTER: [number, number] = [16.047079, 108.206230];

// Component to handle map view updates
const MapUpdater: React.FC<{ 
  selectedStore: StoreLocation | null; 
  selectedProvince: string | null;
  stores: StoreLocation[];
}> = ({ selectedStore, selectedProvince, stores }) => {
  const map = useMap();

  useEffect(() => {
    const zoomIn = () => map.zoomIn();
    const zoomOut = () => map.zoomOut();
    window.addEventListener('map-zoom-in', zoomIn);
    window.addEventListener('map-zoom-out', zoomOut);
    return () => {
      window.removeEventListener('map-zoom-in', zoomIn);
      window.removeEventListener('map-zoom-out', zoomOut);
    };
  }, [map]);

  useEffect(() => {
    if (selectedStore && selectedStore.latitude && selectedStore.longitude) {
      map.setView([Number(selectedStore.latitude), Number(selectedStore.longitude)], 16, {
        animate: true,
      });
    } else if (selectedProvince) {
      const provinceStore = stores.find(s => s.province === selectedProvince && s.latitude && s.longitude);
      if (provinceStore && provinceStore.latitude && provinceStore.longitude) {
        map.setView([Number(provinceStore.latitude), Number(provinceStore.longitude)], 11, {
          animate: true,
        });
      }
    } else {
      map.setView(VIETNAM_CENTER, 6, {
        animate: true,
      });
    }
  }, [selectedStore, selectedProvince, stores, map]);

  return null;
};

const StoreMap: React.FC<StoreMapProps> = ({ stores, selectedStore, selectedProvince }) => {
  // Use useMemo for markers to avoid re-rendering issues
  const markers = useMemo(() => {
    return stores.map((store) => {
      if (!store.latitude || !store.longitude) return null;
      return (
        <Marker 
          key={store.id} 
          position={[Number(store.latitude), Number(store.longitude)]}
          icon={customIcon}
        >
          <Popup>
            <div className="text-[#002094] p-1 min-w-[150px]">
              <h4 className="font-bold text-[14px] mb-1">{store.name}</h4>
              <p className="text-[12px] mb-2">
                {store.address}{store.ward ? `, ${store.ward}` : ''}{store.district ? `, ${store.district}` : ''}{store.province ? `, ${store.province}` : ''}
              </p>
              <a 
                href={`https://www.google.com/maps/dir/?api=1&destination=${store.latitude},${store.longitude}`} 
                target="_blank" 
                rel="noopener noreferrer"
                className="inline-flex items-center text-[10px] font-bold border border-[#002094] px-2 py-1 hover:bg-[#002094] hover:text-white transition-colors uppercase"
              >
                Chỉ đường 
                <svg className="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
              </a>
            </div>
          </Popup>
        </Marker>
      );
    });
  }, [stores]);

  return (
    <div className="w-full h-full relative">
      <MapContainer 
        center={VIETNAM_CENTER} 
        zoom={6} 
        style={{ width: '100%', height: '100%' }}
        scrollWheelZoom={true}
        zoomControl={false} // We'll add it manually to custom position if needed
      >
        <TileLayer
          attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>'
          url="https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png"
        />
        {markers}
        <MapUpdater 
          selectedStore={selectedStore} 
          selectedProvince={selectedProvince} 
          stores={stores} 
        />
      </MapContainer>
      
      {/* Custom Zoom Control to match design */}
      <div className="absolute bottom-10 right-5 z-[1000] flex flex-col gap-2">
        <button 
          onClick={() => window.dispatchEvent(new CustomEvent('map-zoom-in'))}
          className="w-10 h-10 bg-white rounded-full shadow-lg flex items-center justify-center text-[#002094] font-bold text-xl hover:bg-gray-50"
        >
          +
        </button>
        <button 
          onClick={() => window.dispatchEvent(new CustomEvent('map-zoom-out'))}
          className="w-10 h-10 bg-white rounded-full shadow-lg flex items-center justify-center text-[#002094] font-bold text-xl hover:bg-gray-50"
        >
          -
        </button>
      </div>
    </div>
  );
};

export default StoreMap;
