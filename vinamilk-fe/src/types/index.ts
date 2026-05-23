// src/types/index.ts

export interface Brand {
  id: number;
  name: string;
  slug: string;
  logo: string | null;
}

export interface Category {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  product_lines?: ProductLine[];
}

export interface ProductLine {
  id: number;
  name: string;
  slug: string;
  count?: number;
}

export interface Attribute {
  id: number;
  name: string;
  slug: string;
}

export interface ProductImage {
  id: number;
  path: string;
  type: 'main' | 'detail' | 'preview';
}

export interface ProductVariant {
  id: number;
  sku: string;
  name: string | null;
  base_price: number;
  price: number;
  discount_percentage: number;
  stock_quantity: number;
  is_active: boolean;
  flavor: string | null;
  flavor_slug: string | null;
  volume: string | null;
  volume_slug: string | null;
  packaging_type: string | null;
  packaging_type_slug: string | null;
  main_image?: string | null;
  images?: string[] | null;
  position: number;
}

export interface Product {
  id: number;
  name: string;
  slug: string;
  status: string;
  short_description: string | null;
  description: string | null;
  ingredients: string | null;
  usage_instructions: string | null;
  storage_instructions: string | null;
  nutrition_facts: Array<{ key: string; value: string; unit: string }> | null;
  
  category_id: number;
  product_line_id: number | null;
  category?: Category;
  product_line?: ProductLine;
  brand?: Brand;
  sugar_level?: Attribute;
  nutritional_needs?: Attribute[];
  
  main_image: string | null;
  images: ProductImage[];
  home_featured_variant_id: number | null;
  home_featured_variant?: {
    id: number;
    price: number;
    base_price: number;
    discount_percentage: number;
    main_image: string | null;
    images?: string[] | null;
    flavor?: string | null;
    flavor_slug?: string | null;
    volume?: string | null;
    volume_slug?: string | null;
    packaging_type?: string | null;
    packaging_type_slug?: string | null;
  } | null;
  variants: ProductVariant[];
  
  features_title: string | null;
  description_title: string | null;
  description_image: string | null;
  description_images: string[] | null;
  comparison_title: string | null;
  features_main_image: string | null;
  special_highlights: {
    id: number;
    name: string;
    icon: string;
  }[] | null;
  certificates: {
    id: number;
    name: string;
    icon: string;
  }[] | null;
  card_tag?: {
    id: number;
    name: string;
    icon: string;
  } | null;
  
  features: Array<{ title: string; content: string }> | null;
  comparison_table_headers?: Record<string, string>;
  comparison_table_rows?: Array<{
    attribute: string;
    v1?: string;
    v2?: string;
    v3?: string;
    v4?: string;
    v5?: string;
  }>;
  
  created_at: string;
}

export interface PaginatedResponse<T> {
  data: T[];
  links: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };
  meta: {
    current_page: number;
    from: number;
    last_page: number;
    path: string;
    per_page: number;
    to: number;
    total: number;
  };
  category?: Category;
  product_lines?: ProductLine[];
}

export interface HomeData {
  hero_banners: Array<{ 
    id: number; 
    title: string; 
    subtitle: string | null; 
    show_text: boolean; 
    image: string; 
    link: string | null 
  }>;
  promo_split_left: { id: number; title: string; image: string; link: string | null } | null;
  promo_split_right: { 
    id: number; 
    title: string; 
    image: string; 
    link: string | null;
    box_text: string | null;
    box_subtitle: string | null;
    product_slug: string | null;
  } | null;
  certificates: Array<{ id: number; name: string; icon: string | null }>;
  featured_products: Product[];
}

export interface PromotionBanner {
  id: number;
  title: string;
  subtitle: string | null;
  start_date: string | null;
  end_date: string | null;
  image_path: string;
  type: 'link' | 'modal';
  link_url: string | null;
  modal_title: string | null;
  modal_content: string | null;
  modal_table_data: { table_id: string; rows: { col1?: string; col2?: string; col3?: string; col4?: string; col5?: string }[] }[] | null;
  modal_products_limit: number;
  modal_image_path: string | null;
  col_span: number;
  row_span: number;
  button_text: string | null;
  sort_order: number;
  is_active: boolean;
}

export interface PromotionPageSetting {
  id: number;
  hero_image_path: string | null;
  hero_link_url: string | null;
  hero_title: string | null;
  hero_subtitle: string | null;
}

export interface FlashSale {
  id: number;
  title: string;
  content: string | null;
  start_time: string;
  end_time: string;
  is_active: boolean;
}

export interface PromotionTerm {
  id: number;
  title: string;
  content: string;
  table_data?: { table_id: string; rows: { col1?: string; col2?: string; col3?: string; col4?: string; col5?: string }[] }[] | null;
  sort_order: number;
}

export interface PromotionPageData {
  settings: PromotionPageSetting | null;
  banners: PromotionBanner[];
  flash_sale: FlashSale | null;
  flash_sale_products: Product[];
  modal_products: Product[];
  terms: PromotionTerm[];
}

export interface BlogCategory {
  id: number;
  name: string;
  slug: string;
  sort_order: number;
  posts?: BlogPost[];
}

export interface BlogPost {
  id: number;
  category_id: number;
  title: string;
  slug: string;
  excerpt: string | null;
  banner_image: string | null;
  content: string | null;
  published_at: string;
  category?: BlogCategory;
  products?: Product[];
  created_at: string;
}
