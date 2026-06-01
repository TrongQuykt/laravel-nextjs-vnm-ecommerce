"use client";

import React, { createContext, useContext, useEffect, useState, useCallback } from "react";
import { CareProduct, CarePricing, CareGreetingCard } from "@/types/care";
import { Product, ProductVariant } from "@/types";

const STORAGE_KEY = "vinamilk_care_state";

export interface CareCartLine {
  careProductId: number;
  product: CareProduct;
  variant: ProductVariant;
  quantity: number;
}

export type CareView = "main" | "package" | "checkout";

interface CareState {
  cartLine: CareCartLine | null;
  deliveryCount: number;
  includeGreetingCard: boolean;
  greetingCardId: number | null;
  firstDeliveryDate: string;
  pricing: CarePricing | null;
  view: CareView;
  sidebarSlug: string | null;
  selectedGifts: Record<string, any>;
}

const defaultState: CareState = {
  cartLine: null,
  deliveryCount: 3,
  includeGreetingCard: true,
  greetingCardId: null,
  firstDeliveryDate: "",
  pricing: null,
  view: "main",
  sidebarSlug: null,
  selectedGifts: {},
};

type CareCartContextType = CareState & {
  setCartLine: (line: CareCartLine | null) => void;
  addToCareCart: (careProduct: CareProduct, variant: ProductVariant, quantity: number) => void;
  updateCartQuantity: (quantity: number) => void;
  clearCart: () => void;
  updateDraft: (partial: Partial<CareState>) => void;
  setPricing: (pricing: CarePricing | null) => void;
  openSidebar: (slug: string) => void;
  closeSidebar: () => void;
  setView: (view: CareView) => void;
  reset: () => void;
};

const CareCartContext = createContext<CareCartContextType | null>(null);

export function CareCartProvider({ children }: { children: React.ReactNode }) {
  const [state, setState] = useState<CareState>(defaultState);
  const [hydrated, setHydrated] = useState(false);

  useEffect(() => {
    try {
      const raw = sessionStorage.getItem(STORAGE_KEY);
      if (raw) {
        const parsed = JSON.parse(raw);
        setState((s) => ({
          ...s,
          ...parsed,
          view: "main",
          sidebarSlug: null,
        }));
      }
    } catch { /* ignore */ }
    setHydrated(true);
  }, []);

  useEffect(() => {
    if (!hydrated) return;
    const { view, sidebarSlug, ...persist } = state;
    sessionStorage.setItem(STORAGE_KEY, JSON.stringify(persist));
  }, [state, hydrated]);

  const setCartLine = useCallback((line: CareCartLine | null) => {
    setState((s) => ({ ...s, cartLine: line }));
  }, []);

  const addToCareCart = useCallback(
    (careProduct: CareProduct, variant: ProductVariant, quantity: number) => {
      setState((s) => ({
        ...s,
        cartLine: { careProductId: careProduct.id, product: careProduct, variant, quantity },
        sidebarSlug: null,
        pricing: null,
      }));
    },
    []
  );

  const updateCartQuantity = useCallback((quantity: number) => {
    setState((s) =>
      s.cartLine ? { ...s, cartLine: { ...s.cartLine, quantity: Math.max(1, Math.min(99, quantity)) }, pricing: null } : s
    );
  }, []);

  const clearCart = useCallback(() => {
    setState((s) => ({ ...s, cartLine: null, pricing: null }));
  }, []);

  const updateDraft = useCallback((partial: Partial<CareState>) => {
    setState((s) => ({ ...s, ...partial }));
  }, []);

  const setPricing = useCallback((pricing: CarePricing | null) => {
    setState((s) => ({ ...s, pricing }));
  }, []);

  const openSidebar = useCallback((slug: string) => {
    setState((s) => ({ ...s, sidebarSlug: slug }));
  }, []);

  const closeSidebar = useCallback(() => {
    setState((s) => ({ ...s, sidebarSlug: null }));
  }, []);

  const setView = useCallback((view: CareView) => {
    setState((s) => ({ ...s, view }));
  }, []);

  const reset = useCallback(() => {
    setState(defaultState);
    sessionStorage.removeItem(STORAGE_KEY);
  }, []);

  return (
    <CareCartContext.Provider
      value={{
        ...state,
        setCartLine,
        addToCareCart,
        updateCartQuantity,
        clearCart,
        updateDraft,
        setPricing,
        openSidebar,
        closeSidebar,
        setView,
        reset,
      }}
    >
      {children}
    </CareCartContext.Provider>
  );
}

export function useCareCart() {
  const ctx = useContext(CareCartContext);
  if (!ctx) throw new Error("useCareCart must be used within CareCartProvider");
  return ctx;
}

export function formatVnd(n: number) {
  return n.toLocaleString("vi-VN") + "đ";
}

export function variantUnitPrice(v: ProductVariant): number {
  return v.price > 0 && v.price < 10000 ? Math.round(v.price * 1000) : Math.round(v.price);
}

export function variantBasePrice(v: ProductVariant): number {
  const base = v.base_price || v.price;
  return base > 0 && base < 10000 ? Math.round(base * 1000) : Math.round(base);
}
