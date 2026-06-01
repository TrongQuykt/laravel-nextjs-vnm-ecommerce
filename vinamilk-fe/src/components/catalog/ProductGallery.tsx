// src/components/catalog/ProductGallery.tsx
"use client";

import { useState, useEffect, useRef } from "react";
import { ProductImage } from "@/types";
import { motion, AnimatePresence } from "framer-motion";
import { getImageUrl } from "@/lib/api";
import { ChevronLeft, ChevronRight, X } from "lucide-react";

export function ProductGallery({ mainImage, images = [], hideThumbnails = false }: { mainImage: string | null; images?: ProductImage[], hideThumbnails?: boolean }) {
  const allImages = [
    { id: -1, path: mainImage, type: 'main' as const },
    ...(images || []).filter(img => img.path !== mainImage)
  ].filter(img => img.path);

  const [currentIndex, setCurrentIndex] = useState(0);

  // Reset index when main image changes (e.g. variant change)
  useEffect(() => {
    setCurrentIndex(0);
  }, [mainImage]);
  const [direction, setDirection] = useState(0); // 1 for right, -1 for left
  const [isLightboxOpen, setIsLightboxOpen] = useState(false);
  const lightboxRef = useRef<HTMLDivElement>(null);

  // Handle body scroll lock
  useEffect(() => {
    if (isLightboxOpen) {
      document.body.style.overflow = "hidden";
      // Auto-request fullscreen
      if (lightboxRef.current?.requestFullscreen) {
        lightboxRef.current.requestFullscreen().catch(err => {
          console.error(`Error attempting to enable full-screen mode: ${err.message}`);
        });
      }
    } else {
      document.body.style.overflow = "unset";
      if (document.fullscreenElement) {
        document.exitFullscreen().catch(() => { });
      }
    }

    const handleFullscreenChange = () => {
      if (!document.fullscreenElement) {
        setIsLightboxOpen(false);
      }
    };

    document.addEventListener('fullscreenchange', handleFullscreenChange);

    return () => {
      document.body.style.overflow = "unset";
      document.removeEventListener('fullscreenchange', handleFullscreenChange);
    };
  }, [isLightboxOpen]);

  const activeImage = allImages[currentIndex]?.path;

  const paginate = (newDirection: number) => {
    setDirection(newDirection);
    const nextIndex = (currentIndex + newDirection + allImages.length) % allImages.length;
    setCurrentIndex(nextIndex);
  };

  const goToIndex = (index: number) => {
    setDirection(index > currentIndex ? 1 : -1);
    setCurrentIndex(index);
  };

  // Slider variants for directional movement
  const variants = {
    enter: (direction: number) => ({
      x: direction > 0 ? 500 : -500,
      opacity: 0,
      scale: 0.9,
    }),
    center: {
      zIndex: 1,
      x: 0,
      opacity: 1,
      scale: 1,
    },
    exit: (direction: number) => ({
      zIndex: 0,
      x: direction < 0 ? 500 : -500,
      opacity: 0,
      scale: 0.9,
    }),
  };

  return (
    <div className="flex flex-col gap-5 w-full max-w-2xl mx-auto h-full min-h-0 overflow-visible">
      {/* Main Image Container */}
      <div
        onClick={() => setIsLightboxOpen(true)}
        className="relative aspect-square w-full rounded-[3.5rem] bg-cream/30 overflow-hidden group flex-shrink-0 cursor-zoom-in"
      >
        {/* Navigation Buttons */}
        <div className="absolute inset-x-6 top-1/2 -translate-y-1/2 flex justify-between z-20 pointer-events-none">
          <button
            onClick={(e) => {
              e.stopPropagation();
              paginate(-1);
            }}
            className="w-12 h-12 flex items-center justify-center rounded-2xl bg-white/40 backdrop-blur-md border border-v-navy text-v-navy hover:bg-v-navy/20 hover:text-v-navy transition-all duration-300 pointer-events-auto opacity-0 group-hover:opacity-100 -translate-x-4 group-hover:translate-x-0"
          >
            <ChevronLeft size={24} />
          </button>

          <button
            onClick={(e) => {
              e.stopPropagation();
              paginate(1);
            }}
            className="w-12 h-12 flex items-center justify-center rounded-2xl bg-white/40 backdrop-blur-md border border-v-navy text-v-navy hover:bg-v-navy/20 hover:text-v-navy transition-all duration-300 pointer-events-auto opacity-0 group-hover:opacity-100 translate-x-4 group-hover:translate-x-0"
          >
            <ChevronRight size={24} />
          </button>
        </div>

        {/* Sliding Image Container */}
        <div className="w-full h-full relative overflow-hidden">
          <AnimatePresence initial={false} custom={direction} mode="popLayout">
            <motion.div
              key={currentIndex}
              custom={direction}
              variants={variants}
              initial="enter"
              animate="center"
              exit="exit"
              transition={{
                x: { type: "spring", stiffness: 400, damping: 35 },
                opacity: { duration: 0.3 },
              }}
              className="absolute inset-0 w-full h-full p-8 sm:p-12 flex items-center justify-center"
            >
              {activeImage && (
                <img
                  src={getImageUrl(activeImage) || ''}
                  alt="Product"
                  className="w-full h-full object-contain"
                />
              )}
            </motion.div>
          </AnimatePresence>
        </div>

        {/* Pagination Dots (Optional, for extra UI candy) */}
        <div className="absolute bottom-10 left-1/2 -translate-x-1/2 flex gap-2 z-20">
          {allImages.map((_, i) => (
            <div
              key={i}
              className={`h-1.5 rounded-full transition-all duration-500 ${currentIndex === i ? "w-6 bg-v-navy" : "w-1.5 bg-v-navy/20"
                }`}
            />
          ))}
        </div>
      </div>

      {/* Thumbnails */}
      {!hideThumbnails && (
        <div className="flex gap-1 overflow-x-auto pb-4 pt-2 scrollbar-hide px-2 items-center">
        {allImages.map((img, i) => (
          <button
            key={i}
            onClick={() => goToIndex(i)}
            className={`relative w-20 h-20 rounded-2xl flex-shrink-0 overflow-hidden border-2 p-2 box-border transition-all duration-300 ${currentIndex === i
              ? "border-v-navy bg-transparent"
              : "border-transparent bg-transparent hover:border-v-navy/40"
              }`}
          >
            <img
              src={getImageUrl(img.path) || ""}
              alt="Thumbnail"
              className="w-full h-full object-contain"
            />
          </button>
        ))}
      </div>
      )}

      {/* Lightbox Modal */}
      <AnimatePresence>
        {isLightboxOpen && (
          <motion.div
            ref={lightboxRef}
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 z-[1000] bg-[#fefef0] flex flex-col items-stretch"
          >
            {/* Lightbox Header */}
            <div className="p-6 md:p-10 flex justify-end">
              <button
                onClick={(e) => {
                  e.stopPropagation();
                  setIsLightboxOpen(false);
                }}
                className="w-12 h-12 flex items-center justify-center rounded-2xl bg-v-navy/5 text-v-navy hover:bg-v-navy/10 transition-all duration-300"
              >
                <X size={24} />
              </button>
            </div>

            {/* Lightbox Body - Centered Image */}
            <div className="flex-1 relative flex items-center justify-center overflow-hidden">
              {/* Navigation Left */}
              <div className="absolute left-4 md:left-10 z-20">
                <button
                  onClick={(e) => {
                    e.stopPropagation();
                    paginate(-1);
                  }}
                  className="w-14 h-14 md:w-20 md:h-20 flex items-center justify-center rounded-3xl bg-white border border-v-navy/10 text-v-navy hover:bg-v-navy/10 hover:text-v-navy transition-all duration-500"
                >
                  <ChevronLeft size={32} />
                </button>
              </div>

              {/* Main Image Container */}
              <div className="w-full h-full flex items-center justify-center relative p-10 md:p-20">
                <AnimatePresence initial={false} custom={direction} mode="popLayout">
                  <motion.div
                    key={currentIndex}
                    custom={direction}
                    variants={variants}
                    initial="enter"
                    animate="center"
                    exit="exit"
                    transition={{
                      x: { type: "spring", stiffness: 350, damping: 30 },
                      opacity: { duration: 0.4 },
                    }}
                    className="w-full h-full flex items-center justify-center"
                  >
                    <img
                      src={getImageUrl(activeImage) || ''}
                      alt="Product Zoomed View"
                      className="max-w-full max-h-full object-contain"
                    />
                  </motion.div>
                </AnimatePresence>
              </div>

              {/* Navigation Right */}
              <div className="absolute right-4 md:right-10 z-20">
                <button
                  onClick={(e) => {
                    e.stopPropagation();
                    paginate(1);
                  }}
                  className="w-14 h-14 md:w-20 md:h-20 flex items-center justify-center rounded-3xl bg-white border border-v-navy/10 text-v-navy hover:bg-v-navy/10 hover:text-v-navy transition-all duration-500"
                >
                  <ChevronRight size={32} />
                </button>
              </div>
            </div>

            {/* Lightbox Footer (Thumbnails) */}
            <div className="p-10 flex flex-col items-center gap-8">
              <div className="flex gap-2 min-w-0 max-w-full overflow-x-auto pb-4 px-4 scrollbar-hide">
                {allImages.map((img, i) => (
                  <button
                    key={i}
                    onClick={() => goToIndex(i)}
                    className={`relative w-16 h-16 md:w-20 md:h-20 rounded-2xl flex-shrink-0 overflow-hidden border-2 p-2 box-border transition-all duration-300 ${currentIndex === i
                      ? "border-v-navy"
                      : "border-transparent hover:border-v-navy/40"
                      }`}
                  >
                    <img
                      src={getImageUrl(img.path) || ""}
                      alt="Thumbnail"
                      className="w-full h-full object-contain"
                    />
                  </button>
                ))}
              </div>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}
