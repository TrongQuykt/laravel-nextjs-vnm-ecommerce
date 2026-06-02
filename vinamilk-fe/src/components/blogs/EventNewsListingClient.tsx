'use client';

import React from 'react';
import Link from 'next/link';
import { Home, ChevronRight, Calendar } from 'lucide-react';
import { motion } from 'framer-motion';
import { format } from 'date-fns';
import { vi } from 'date-fns/locale';

interface EventNews {
  id: number;
  title: string;
  slug: string;
  excerpt: string;
  banner_image: string;
  published_at: string;
}

interface EventNewsListingClientProps {
  events: EventNews[];
}

const EventNewsListingClient: React.FC<EventNewsListingClientProps> = ({ events }) => {
  return (
    <main className="min-h-screen bg-[#FDFCF0] pb-20 pt-30 md:pt-30">
      {/* Breadcrumbs - Centered */}
      <div className="max-w-[1440px] mx-auto px-10 md:px-20 mb-8">
        <nav className="flex items-center justify-center space-x-3 text-[11px] font-bold text-[#002094] opacity-60">
          <Link href="/" className="hover:opacity-100 transition-opacity">
            <Home className="w-3.5 h-3.5" />
          </Link>
          <ChevronRight className="w-3 h-3 opacity-30" />
          <span className="text-[#002094]">Tin tức sự kiện</span>
        </nav>
      </div>

      {/* Hero Title */}
      <div className="pb-15 text-center">
        <h1 className="text-4xl md:text-5xl font-sans font-black text-[#002094] mb-8 tracking-tight">
          Tin tức sự kiện
        </h1>
      </div>

      {/* Main Content Area */}
      <div className="max-w-[1440px] mx-auto px-10 md:px-20">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          {events.map((event, index) => (
            <motion.div
              key={event.id}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.3, delay: index * 0.1 }}
            >
              <Link href={`/events-news/${event.slug}`}>
                <div className="overflow-hidden">
                  {/* Banner Image */}
                  <div className="aspect-[16/9] overflow-hidden">
                    {event.banner_image ? (
                      <img
                        src={event.banner_image}
                        alt={event.title}
                        className="w-full h-full object-cover"
                      />
                    ) : (
                      <div className="w-full h-full bg-gradient-to-br from-[#002094] to-[#2b59ff] flex items-center justify-center">
                        <span className="text-white text-4xl font-bold">Vinamilk</span>
                      </div>
                    )}
                  </div>

                  {/* Content */}
                  <div className="p-6">
                    {/* Date */}
                    <div className="flex items-center text-[11px] text-v-navy mb-3">
                      <Calendar className="w-3.5 h-3.5 mr-2" />
                      {format(new Date(event.published_at), 'dd/MM/yyyy', { locale: vi })}
                    </div>

                    {/* Title */}
                    <h3 className="text-sm font-semibold text-[#002094] mb-3 line-clamp-2">
                      {event.title}
                    </h3>

                    {/* Excerpt */}
                    <p className="text-sm text-[#002094]/70 line-clamp-3">
                      {event.excerpt}
                    </p>
                  </div>
                </div>
              </Link>
            </motion.div>
          ))}
        </div>

        {events.length === 0 && (
          <div className="text-center py-20">
            <p className="text-[#002094]/60 text-lg">Chưa có sự kiện nào được đăng tải.</p>
          </div>
        )}
      </div>
    </main>
  );
};

export default EventNewsListingClient;
