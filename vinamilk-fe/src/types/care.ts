export interface CareBenefit {
  title: string;
  description: string;
}

export interface CarePageSettings {
  tagline: string | null;
  intro_text: string | null;
  hero_image_path: string | null;
  benefits: CareBenefit[];
  premium_coming_soon: boolean;
}

export interface CareDeliveryOption {
  id: number;
  delivery_count: number;
  discount_percent: number;
}

/** Sản phẩm trong chương trình Care (từ catalog) */
export interface CareProduct {
  id: number;
  product_id: number;
  slug: string;
  name: string;
  category_name: string | null;
  short_description: string | null;
  image: string | null;
  base_price: number;
  care_price: number;
  discount_percent: number;
}

export interface CareGreetingCard {
  id: number;
  title: string;
  message: string;
  preview_image_path: string | null;
}

export interface CarePricing {
  care_product_id: number;
  product_id: number;
  variant_id: number;
  quantity: number;
  delivery_count: number;
  discount_percent: number;
  unit_price: number;
  package_subtotal: number;
  discount_amount: number;
  total_amount: number;
  delivery_schedule: string[];
}

export interface CareDraft {
  step: 1 | 2 | 3;
  careProductId: number | null;
  product: CareProduct | null;
  variantId: number | null;
  quantity: number;
  deliveryCount: number;
  includeGreetingCard: boolean;
  greetingCardId: number | null;
  firstDeliveryDate: string;
  pricing: CarePricing | null;
}
