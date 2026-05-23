"use client";

import React, { createContext, useContext, useState, useEffect, useRef } from "react";
import { Product, ProductVariant } from "@/types";
import { catalogApi, voucherApi } from "@/lib/api";

interface CartItem {
  id: string; 
  product_id: number;
  variant_id: number;
  product: Product;
  variant: ProductVariant;
  quantity: number;
}

export interface MarketingReward {
  id?: number;
  rule_id: number;
  type: 'fixed_discount' | 'percentage_discount' | 'gift_product' | 'gift_product_choice' | 'fixed' | 'choice';
  name?: string;
  image?: string;
  quantity?: number;
  options?: any[];
  pick_count?: number;
  from_rule?: string;
  selected_id?: number;
  selected_option?: any;
  value?: any;
  title: string;
  description?: string;
}

export interface AppliedVoucher {
  id: number;
  code: string;
  name: string;
  type: 'percent' | 'fixed';
  discount_value: number;
  max_discount_amount?: number;
  discount_amount: number;
}

interface CartContextType {
  items: CartItem[];
  addToCart: (product: Product, variant: ProductVariant, quantity: number, startElement?: HTMLElement) => Promise<void>;
  updateQuantity: (variantId: number, quantity: number) => void;
  removeItem: (variantId: number) => void;
  selectReward: (ruleId: number, selectionIds: number[]) => void;
  totalItems: number;
  subtotal: number;
  totalBasePrice: number;
  totalProductDiscount: number;
  rewards: MarketingReward[];
  appliedRules: any[];
  rewardSelections: Record<number, number[]>;
  isLoading: boolean;
  isEvaluating: boolean;
  isSidebarOpen: boolean;
  setIsSidebarOpen: (open: boolean) => void;
  isVoucherSidebarOpen: boolean;
  setIsVoucherSidebarOpen: (open: boolean) => void;
  cartIconRef: React.RefObject<HTMLDivElement | null>;
  flyItem: { src: string; startPos: { x: number; y: number } } | null;
  // Voucher
  appliedVoucher: AppliedVoucher | null;
  voucherDiscount: number;
  applyVoucher: (code: string) => Promise<{ success: boolean; message: string }>;
  removeVoucher: () => void;
  isApplyingVoucher: boolean;
  appliedRedemptions: any[];
  applyRedemption: (redemption: any) => void;
  removeRedemption: (redemptionId: number) => void;
}

const CartContext = createContext<CartContextType | undefined>(undefined);

export function CartProvider({ children }: { children: React.ReactNode }) {
  const [items, setItems] = useState<CartItem[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [isEvaluating, setIsEvaluating] = useState(false);
  const [isSidebarOpen, setIsSidebarOpen] = useState(false);
  const [isVoucherSidebarOpen, setIsVoucherSidebarOpen] = useState(false);
  const [flyItem, setFlyItem] = useState<{ src: string; startPos: { x: number; y: number } } | null>(null);
  const [rewards, setRewards] = useState<MarketingReward[]>([]);
  const [appliedRules, setAppliedRules] = useState<any[]>([]);
  const [rewardSelections, setRewardSelections] = useState<Record<number, number[]>>({});
  const [appliedVoucher, setAppliedVoucher] = useState<AppliedVoucher | null>(null);
  const [isApplyingVoucher, setIsApplyingVoucher] = useState(false);
  const [appliedRedemptions, setAppliedRedemptions] = useState<any[]>([]);
  const cartIconRef = useRef<HTMLDivElement | null>(null);

  // Load redemptions from localStorage on mount
  useEffect(() => {
    const savedRedemptions = localStorage.getItem("vinamilk_cart_redemptions");
    if (savedRedemptions) {
      try { setAppliedRedemptions(JSON.parse(savedRedemptions)); } catch (e) {}
    }
  }, []);

  // Save redemptions when changed
  useEffect(() => {
    localStorage.setItem("vinamilk_cart_redemptions", JSON.stringify(appliedRedemptions));
  }, [appliedRedemptions]);

  // Load from localStorage on mount
  useEffect(() => {
    const savedCart = localStorage.getItem("vinamilk_cart");
    const savedSelections = localStorage.getItem("vinamilk_cart_selections");
    if (savedCart) {
      try { setItems(JSON.parse(savedCart)); } catch (e) { console.error("Failed to parse cart", e); }
    }
    if (savedSelections) {
      try { setRewardSelections(JSON.parse(savedSelections)); } catch (e) {}
    }
  }, []);

  // Save and evaluate
  useEffect(() => {
    localStorage.setItem("vinamilk_cart", JSON.stringify(items));
    localStorage.setItem("vinamilk_cart_selections", JSON.stringify(rewardSelections));
    if (items.length > 0) {
      evaluateCart(items, rewardSelections);
    } else {
      setRewards([]);
      setAppliedRules([]);
    }
    // Auto-close voucher sidebar so it reloads fresh when reopened
    setIsVoucherSidebarOpen(false);
  }, [items, rewardSelections]);

  // Re-validate applied voucher when cart changes
  useEffect(() => {
    if (!appliedVoucher) return;
    if (items.length === 0) {
      setAppliedVoucher(null);
      return;
    }
    const cartItems = items.map(item => ({
      product_id: item.product_id,
      price: item.variant.price,
      quantity: item.quantity,
    }));
    const cartTotal = items.reduce((s, i) => s + i.variant.price * i.quantity, 0);
    voucherApi.validateCode(appliedVoucher.code, cartTotal, cartItems)
      .then(res => {
        if (res.success) {
          setAppliedVoucher(prev => prev ? { ...prev, discount_amount: res.data.discount_amount } : null);
        } else {
          setAppliedVoucher(null);
        }
      })
      .catch(() => {});
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [items]);

  const evaluateCart = async (currentItems: CartItem[], currentSelections: Record<number, number[]>) => {
    setIsEvaluating(true);
    try {
      const payload = {
        items: currentItems.map(item => ({
          product_id: item.product_id,
          variant_id: item.variant_id,
          quantity: item.quantity,
          price: item.variant.price,
          category_id: item.product.category_id
        })),
        reward_selections: currentSelections
      };
      const response = await catalogApi.evaluateCart(payload);
      if (response.success) {
        setRewards(response.data.gifts || []);
        setAppliedRules(response.data.applied_rules || []);
      }
    } catch (error) {
      console.error("Cart evaluation failed:", error);
    } finally {
      setIsEvaluating(false);
    }
  };

  const selectReward = (ruleId: number, selectionIds: number[]) => {
    setRewardSelections(prev => ({ ...prev, [ruleId]: selectionIds }));
  };

  const applyVoucher = async (code: string): Promise<{ success: boolean; message: string }> => {
    setIsApplyingVoucher(true);
    try {
      const cartItems = items.map(item => ({
        product_id: item.product_id,
        price: item.variant.price,
        quantity: item.quantity,
      }));
      const cartTotal = items.reduce((s, i) => s + i.variant.price * i.quantity, 0);

      const token = typeof window !== 'undefined' ? localStorage.getItem('auth_token') : null;
      const apiCall = token
        ? voucherApi.applyVoucher(code, cartTotal, cartItems)
        : voucherApi.validateCode(code, cartTotal, cartItems);

      const res = await apiCall;
      if (res.success) {
        const newVoucher: AppliedVoucher = {
          ...res.data.voucher,
          discount_amount: res.data.discount_amount,
        };
        setAppliedVoucher(newVoucher);
        return { success: true, message: res.message || 'Áp dụng thành công!' };
      }
      return { success: false, message: res.message || 'Lỗi áp dụng voucher' };
    } catch (err: any) {
      return { success: false, message: 'Không tìm thấy hoặc voucher không hợp lệ.' };
    } finally {
      setIsApplyingVoucher(false);
    }
  };

  const removeVoucher = () => {
    setAppliedVoucher(null);
  };

  const addToCart = async (product: Product, variant: ProductVariant, quantity: number, startElement?: HTMLElement) => {
    setIsLoading(true);
    if (startElement) {
      const rect = startElement.getBoundingClientRect();
      setFlyItem({
        src: variant.main_image || product.main_image || "",
        startPos: { x: rect.left + rect.width / 2, y: rect.top + rect.height / 2 },
      });
      setTimeout(() => setFlyItem(null), 1000);
    }
    await new Promise((resolve) => setTimeout(resolve, 600));
    setItems((prev) => {
      const existing = prev.find((item) => item.variant_id === variant.id);
      if (existing) {
        return prev.map((item) =>
          item.variant_id === variant.id ? { ...item, quantity: item.quantity + quantity } : item
        );
      }
      return [...prev, { id: `${variant.id}`, product_id: product.id, variant_id: variant.id, product, variant, quantity }];
    });
    setIsLoading(false);
    setTimeout(() => setIsSidebarOpen(true), 500);
  };

  const updateQuantity = (variantId: number, quantity: number) => {
    if (quantity <= 0) { removeItem(variantId); return; }
    setItems((prev) => prev.map((item) => (item.variant_id === variantId ? { ...item, quantity } : item)));
  };

  const removeItem = (variantId: number) => {
    setItems((prev) => prev.filter((item) => item.variant_id !== variantId));
  };

  const totalItems = items.reduce((sum, item) => sum + item.quantity, 0);
  
  // Giá trong DB là đơn vị nghìn đồng (295.99 = 295,990đ). Cần nhân 1000 khi price < 10000
  const toVND = (price: number) => (price > 0 && price < 10000) ? Math.round(price * 1000) : Math.round(price);

  const totalBasePrice = items.reduce((sum, item) => {
    return sum + toVND(item.variant.base_price || item.variant.price) * item.quantity;
  }, 0);

  const totalProductDiscount = items.reduce((sum, item) => {
    const base = toVND(item.variant.base_price || item.variant.price);
    const current = toVND(item.variant.price);
    return sum + (base - current) * item.quantity;
  }, 0);

  const personalVoucherDiscount = appliedRedemptions.reduce((sum, ar) => {
    if (ar.reward && ar.reward.type === 'voucher') {
      const matches = ar.reward.name.match(/(\d+)K.*?(\d+)K/i);
      if (matches) {
        return sum + parseInt(matches[1]) * 1000;
      }
    }
    return sum;
  }, 0);

  const voucherDiscount = Math.round((appliedVoucher?.discount_amount ?? 0) + personalVoucherDiscount);
  // Clamp to 0 to prevent negative total when voucher > subtotal
  const subtotal = Math.max(0, Math.round(totalBasePrice - totalProductDiscount - voucherDiscount));

  const applyRedemption = (redemption: any) => {
    setAppliedRedemptions(prev => {
      if (redemption.reward && redemption.reward.type === 'voucher') {
        const filtered = prev.filter(r => r.reward.type !== 'voucher');
        return [...filtered, redemption];
      }
      if (prev.some(r => r.id === redemption.id)) return prev;
      return [...prev, redemption];
    });
  };

  const removeRedemption = (redemptionId: number) => {
    setAppliedRedemptions(prev => prev.filter(r => r.id !== redemptionId));
  };

  return (
    <CartContext.Provider
      value={{
        items, addToCart, updateQuantity, removeItem, selectReward,
        totalItems, subtotal, totalBasePrice, totalProductDiscount,
        rewards, appliedRules, rewardSelections,
        isLoading, isEvaluating,
        isSidebarOpen, setIsSidebarOpen,
        isVoucherSidebarOpen, setIsVoucherSidebarOpen,
        cartIconRef, flyItem,
        appliedVoucher, voucherDiscount, applyVoucher, removeVoucher, isApplyingVoucher,
        appliedRedemptions, applyRedemption, removeRedemption
      }}
    >
      {children}
    </CartContext.Provider>
  );
}

export function useCart() {
  const context = useContext(CartContext);
  if (context === undefined) {
    throw new Error("useCart must be used within a CartProvider");
  }
  return context;
}
