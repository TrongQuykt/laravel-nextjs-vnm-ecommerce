'use client';

import React, { useState, useEffect, useRef } from 'react';
import { HomeData } from '@/types';
import Link from 'next/link';
import { motion, AnimatePresence } from 'framer-motion';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { getImageUrl } from '@/lib/api';

export default function HomeClient({ data }: { data: HomeData }) {
  // Hero Slider State
  const [currentSlide, setCurrentSlide] = useState(0);
  const [direction, setDirection] = useState(0);
  const containerRef = useRef<HTMLDivElement>(null);
  const contentRef = useRef<HTMLDivElement>(null);
  const [isDragging, setIsDragging] = useState(false);
  const heroBanners = data.hero_banners || [];
  const [windowWidth, setWindowWidth] = useState(1200);

  useEffect(() => {
    setWindowWidth(window.innerWidth);
    const handleResize = () => setWindowWidth(window.innerWidth);
    window.addEventListener('resize', handleResize);
    return () => window.removeEventListener('resize', handleResize);
  }, []);

  const slideVariants = {
    enter: (direction: number) => ({
      x: direction > 0 ? '100%' : '-100%',
      opacity: 1
    }),
    center: {
      x: 0,
      opacity: 1
    },
    exit: (direction: number) => ({
      x: direction < 0 ? '100%' : '-100%',
      opacity: 1
    })
  };

  const paginate = (newDirection: number) => {
    setDirection(newDirection);
    setCurrentSlide((prev) => (prev + newDirection + heroBanners.length) % heroBanners.length);
  };

  return (
    <div className="w-full">
      {/* 1. HERO BANNER WITH ARROWS */}
      <section className="relative w-full h-[60vh] md:h-[100vh] overflow-hidden bg-cream">
        <div className="absolute inset-0">
          <AnimatePresence initial={false} custom={direction} mode="popLayout">
            {heroBanners.length > 0 && (
              <motion.div
                key={currentSlide}
                custom={direction}
                variants={slideVariants}
                initial="enter"
                animate="center"
                exit="exit"
                transition={{
                  x: { type: "tween", duration: 0.5, ease: "easeInOut" },
                  opacity: { duration: 0 }
                }}
                drag="x"
                dragConstraints={{ left: 0, right: 0 }}
                dragElastic={0.2}
                onDragEnd={(e, { offset, velocity }) => {
                  const swipe = offset.x;
                  if (swipe < -100) {
                    paginate(1);
                  } else if (swipe > 100) {
                    paginate(-1);
                  }
                }}
                className="absolute inset-0 w-full h-full"
              >
                <div className="w-full h-full relative">
                  {/* Dark Overlays for Text Legibility */}
                  <div className="absolute top-0 left-0 w-full h-40 bg-gradient-to-b from-black/60 to-transparent z-10 pointer-events-none" />
                  <div className="absolute bottom-0 left-0 w-full h-1/2 bg-gradient-to-t from-black/60 to-transparent z-10 pointer-events-none" />

                  {/* Banner Content (Conditional) */}
                  {heroBanners[currentSlide].show_text && (
                    <div className="absolute inset-0 flex flex-col justify-end items-center pb-20 z-20 pointer-events-none text-white px-6">
                      <h2 className="text-4xl md:text-4xl font-sans text-center !text-cream drop-shadow-lg mb-2 tracking-tight">
                        {heroBanners[currentSlide].title}
                      </h2>

                      {heroBanners[currentSlide].subtitle && (
                        <p className="text-xl md:text-xl font-sans text-center text-white/90 drop-shadow-md max-w-4xl">
                          {heroBanners[currentSlide].subtitle}
                        </p>
                      )}
                    </div>
                  )}

                  {heroBanners[currentSlide].link ? (
                    <Link href={heroBanners[currentSlide].link} className="w-full h-full block">
                      <img
                        src={heroBanners[currentSlide].image}
                        alt={heroBanners[currentSlide].title}
                        className="w-full h-full object-cover pointer-events-none"
                        loading="eager"
                      />
                    </Link>
                  ) : (
                    <img
                      src={heroBanners[currentSlide].image}
                      alt={heroBanners[currentSlide].title}
                      className="w-full h-full object-cover"
                      loading="eager"
                    />
                  )}
                </div>
              </motion.div>
            )}
          </AnimatePresence>
        </div>

        {heroBanners.length > 1 && (
          <>
            {/* Navigation Arrows */}
            <button
              onClick={() => paginate(-1)}
              className="absolute left-6 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/90 hover:bg-[#EAF1F8] border border-v-navy/20 flex items-center justify-center text-v-navy transition-all z-20 shadow-sm"
            >
              <ChevronLeft size={24} />
            </button>
            <button
              onClick={() => paginate(1)}
              className="absolute right-6 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/90 hover:bg-[#EAF1F8] border border-v-navy/20 flex items-center justify-center text-v-navy transition-all z-20 shadow-sm"
            >
              <ChevronRight size={24} />
            </button>

            {/* Pagination Bars (Clickable) */}
            <div className="absolute bottom-10 left-1/2 -translate-x-1/2 flex items-center space-x-3 z-30">
              {heroBanners.map((_, idx) => (
                <button
                  key={idx}
                  onClick={() => {
                    setDirection(idx > currentSlide ? 1 : -1);
                    setCurrentSlide(idx);
                  }}
                  className="group py-4 px-1 focus:outline-none"
                >
                  <div
                    className={`h-[2px] transition-all duration-300 rounded-full ${idx === currentSlide
                      ? 'w-16 bg-white'
                      : 'w-8 bg-white/30 group-hover:bg-white/60'
                      }`}
                  />
                </button>
              ))}
            </div>
          </>
        )}
      </section>

      {/* 2. CERTIFICATE STRIPE (Navy Background) */}
      <section className="w-full bg-v-navy py-2 min-h-[100px]">
        <div className="container mx-auto px-4 overflow-hidden">
          {data.certificates && data.certificates.length > 0 ? (
            <div className="flex items-center justify-center gap-14 flex-wrap">
              {data.certificates.map(cert => (
                <div key={cert.id} className="flex flex-col items-center flex-shrink-0">
                  {cert.icon && (
                    <img src={cert.icon} alt={cert.name} className="w-40 h-40 md:w-40 md:h-40 object-contain brightness-0 invert transition-opacity" />
                  )}
                </div>
              ))}
            </div>
          ) : (
            <div className="text-center text-white/50 italic text-sm">
              * Chưa có chứng nhận nào được thêm. Vui lòng vào Admin -&gt; Certificates để thêm.
            </div>
          )}
        </div>
      </section>

      {/* 3. PROMO SPLIT SECTION */}
      {(data.promo_split_left || data.promo_split_right) && (
        <section className="w-full py-0">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-0 h-auto md:h-[900px]">
            {/* Promo Left */}
            <div className="relative overflow-hidden group h-full">
              {data.promo_split_left && (
                <div className="w-full h-full">
                  {data.promo_split_left.link ? (
                    <Link href={data.promo_split_left.link} className="w-full h-full block">
                      <img src={data.promo_split_left.image} alt={data.promo_split_left.title} className="w-full h-full object-cover" />
                    </Link>
                  ) : (
                    <img src={data.promo_split_left.image} alt={data.promo_split_left.title} className="w-full h-full object-cover" />
                  )}
                </div>
              )}
            </div>

            {/* Promo Right (With Specialized Box) */}
            <div className="relative overflow-hidden group h-full">
              {data.promo_split_right && (
                <div className="w-full h-full relative">
                  <img src={data.promo_split_right.image} alt={data.promo_split_right.title} className="w-full h-full object-cover" />

                  {/* Specialized Promo Box (Lime Green) */}
                  <div className="absolute inset-0 flex items-center justify-center p-4">
                    {data.promo_split_right.product_slug ? (
                      <Link
                        href={`/products/${data.promo_split_right.product_slug}`}
                        className="bg-[#e0fb9b] w-[300px] md:w-[400px] p-10 md:p-14 pb-16 md:pb-20 flex-col justify-between items-center text-[#0213b0] text-center relative"
                        style={{
                          minHeight: '40%',
                          maskImage: 'radial-gradient(circle at 20px 100%, transparent 15px, black 16px)',
                          maskSize: '40px 100%',
                          WebkitMaskImage: 'radial-gradient(circle at 20px 100%, transparent 15px, black 16px)',
                          WebkitMaskSize: '40px 100%'
                        }}
                      >
                        <div className="flex-1 flex flex-col justify-center">
                          <h2 className="text-6xl md:text-8xl leading-[1.2] mb-2 whitespace-pre-line">
                            {data.promo_split_right.box_text || 'Mới!\nMới!\nMới!'}
                          </h2>
                        </div>

                        <div className="w-full mt-6">
                          <div className="flex justify-between items-center mb-4 group/btn">
                            <span className="text-base font-bold italic">
                              {data.promo_split_right.box_subtitle || 'Khám phá ngay'}
                            </span>
                            <ChevronRight className="w-5 h-5 group-hover/btn:translate-x-2 transition-transform" />
                          </div>
                          <div className="border-t border-[#0213b0]" />
                        </div>
                      </Link>
                    ) : (
                      <div className="absolute inset-0 flex items-center justify-center p-8 bg-black/10">
                        <h2 className="text-4xl md:text-5xl font-serif font-black text-white text-center drop-shadow-lg">{data.promo_split_right.title}</h2>
                      </div>
                    )}
                  </div>
                </div>
              )}
            </div>
          </div>
        </section>
      )}

      {/* 4. DRAGGABLE PRODUCT SHELF (Mời bạn sắm sửa) */}
      {data.featured_products && data.featured_products.length > 0 && (
        <section className="py-24 bg-transparent overflow-hidden">
          <div className="container mx-auto px-4 mb-20">
            <h2 className="text-4xl md:text-6xl font-sans font-extrabold text-v-navy text-center tracking-tight">Mời bạn sắm sửa</h2>
          </div>

          <div className="relative min-h-[500px]">
            {/* The Horizontal Shelf Line */}
            <div className="absolute top-[320px] left-0 w-full h-1 bg-v-navy z-0" />

            <div className="px-4 md:px-20 overflow-visible" ref={containerRef}>
              <motion.div
                ref={contentRef}
                className="flex gap-2 cursor-grab active:cursor-grabbing items-end w-max h-[320px]"
                drag="x"
                dragConstraints={containerRef}
                dragElastic={0.1}
                dragMomentum={true}
                dragTransition={{ bounceStiffness: 400, bounceDamping: 30 }}
                onDragStart={() => setIsDragging(true)}
                onDragEnd={() => {
                  setTimeout(() => setIsDragging(false), 150);
                }}
              >
                {data.featured_products.map((product) => (
                  <ProductShelfItem key={product.id} product={product} isDragging={isDragging} />
                ))}
              </motion.div>
            </div>
          </div>
        </section>
      )}
    </div>
  );
}

function ProductShelfItem({ product, isDragging }: { product: any, isDragging: boolean }) {
  const [isHovered, setIsHovered] = useState(false);

  useEffect(() => {
    if (isDragging) setIsHovered(false);
  }, [isDragging]);

  // We use the images from the home_featured_variant gallery
  const featured = product.home_featured_variant;
  const gallery = featured?.images || [];

  const img0 = gallery.length > 0
    ? getImageUrl(gallery[0])
    : (product.main_image ? getImageUrl(product.main_image) : '/placeholder.png');

  const img1 = gallery.length > 1
    ? getImageUrl(gallery[1])
    : img0;

  // Logic: Combined Brand + Detail (Sugar Level or Flavor)
  // We prioritize Sugar Level if available, otherwise Flavor
  const detail = product.sugar_level?.name || product.variants?.[0]?.flavor || '';
  const brandName = product.brand?.name || 'Vinamilk';
  // If no sugar level or flavor, show the product name instead of just brand
  const brandWithDetail = detail ? `${brandName} • ${detail}` : product.name;

  return (
    <div
      className={`min-w-[130px] flex flex-col items-center relative group ${isDragging ? 'pointer-events-none' : ''}`}
      onMouseEnter={() => !isDragging && setIsHovered(true)}
      onMouseLeave={() => setIsHovered(false)}
    >
      <div className="relative w-full h-[320px] flex items-end justify-center pb-0 overflow-visible">
        {/* Shift image down slightly to make the bottle base touch the blue shelf line */}
        <motion.img
          src={(isHovered ? img1 : img0) || undefined}
          alt={product.name}
          initial={{ scale: 2.1, y: 30 }}
          animate={{
            scale: 2.1,
            y: isHovered ? 25 : 30 // Higher value pushes it further down to touch the line
          }}
          className="w-full h-full object-contain pointer-events-none transition-all duration-300 -mx-16"
        />
      </div>

      {/* Hover Info Box (Lime Green) */}
      <AnimatePresence>
        {isHovered && (
          <motion.div
            initial={{ opacity: 0, y: -10 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -10 }}
            className="absolute top-[324px] left-1/2 -translate-x-1/2 w-[250px] md:w-[300px] max-w-[85vw] bg-[#e0fb9b] z-20 shadow-xl"
          >
            <Link href={`/products/${product.slug}`} className="block p-5">
              <div className="flex flex-col text-[#0213b0] font-sans">
                {/* Line 1: Product Line Name */}
                <span className="text-[10px] md:text-[11px] tracking-[0.1em] font-semibold opacity-70 mb-1 leading-tight">
                  {product.product_line?.name || product.category?.name || 'Vinamilk'}
                </span>

                {/* Line 2: Brand + Detail (Sugar or Flavor) */}
                <div className="flex items-center justify-between group/box">
                  <span className="font-extrabold text-xs md:text-sm leading-none">
                    {brandWithDetail}
                  </span>
                  <ChevronRight className="w-5 h-5 ml-1 flex-shrink-0" />
                </div>
              </div>
            </Link>

            {/* Ticket Corner/Ticked effect at the bottom */}
            <div
              className="absolute bottom-0 left-0 w-full h-[8px] pointer-events-none"
              style={{
                backgroundImage: 'radial-gradient(circle at 8px 100%, transparent 4px, #e0fb9b 4.5px)',
                backgroundSize: '16px 100%'
              }}
            />
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}
