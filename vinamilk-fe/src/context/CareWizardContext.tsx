"use client";

import React, { createContext, useContext, useEffect, useState, useCallback } from "react";
import { CareDraft, CareProduct, CarePricing } from "@/types/care";

const STORAGE_KEY = "vinamilk_care_draft";

const defaultDraft: CareDraft = {
  step: 1,
  careProductId: null,
  product: null,
  variantId: null,
  quantity: 1,
  deliveryCount: 3,
  includeGreetingCard: true,
  greetingCardId: null,
  firstDeliveryDate: "",
  pricing: null,
};

type CareWizardContextType = {
  draft: CareDraft;
  setStep: (step: 1 | 2 | 3) => void;
  selectProduct: (product: CareProduct) => void;
  updateDraft: (partial: Partial<CareDraft>) => void;
  setPricing: (pricing: CarePricing | null) => void;
  reset: () => void;
};

const CareWizardContext = createContext<CareWizardContextType | null>(null);

export function CareWizardProvider({ children }: { children: React.ReactNode }) {
  const [draft, setDraft] = useState<CareDraft>(defaultDraft);
  const [hydrated, setHydrated] = useState(false);

  useEffect(() => {
    try {
      const raw = sessionStorage.getItem(STORAGE_KEY);
      if (raw) setDraft({ ...defaultDraft, ...JSON.parse(raw) });
    } catch { /* ignore */ }
    setHydrated(true);
  }, []);

  useEffect(() => {
    if (!hydrated) return;
    sessionStorage.setItem(STORAGE_KEY, JSON.stringify(draft));
  }, [draft, hydrated]);

  const setStep = useCallback((step: 1 | 2 | 3) => setDraft((d) => ({ ...d, step })), []);
  const selectProduct = useCallback((product: CareProduct) => {
    setDraft((d) => ({
      ...d,
      careProductId: product.id,
      product,
      variantId: null,
      quantity: 1,
      pricing: null,
      step: 2,
    }));
  }, []);
  const updateDraft = useCallback((partial: Partial<CareDraft>) => {
    setDraft((d) => ({ ...d, ...partial }));
  }, []);
  const setPricing = useCallback((pricing: CarePricing | null) => {
    setDraft((d) => ({ ...d, pricing }));
  }, []);
  const reset = useCallback(() => {
    sessionStorage.removeItem(STORAGE_KEY);
    setDraft(defaultDraft);
  }, []);

  return (
    <CareWizardContext.Provider value={{ draft, setStep, selectProduct, updateDraft, setPricing, reset }}>
      {children}
    </CareWizardContext.Provider>
  );
}

export function useCareWizard() {
  const ctx = useContext(CareWizardContext);
  if (!ctx) throw new Error("useCareWizard must be used within CareWizardProvider");
  return ctx;
}

export function formatVnd(n: number) {
  return n.toLocaleString("vi-VN") + "đ";
}
