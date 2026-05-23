const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000/api/v1';
const STORAGE_URL = process.env.NEXT_PUBLIC_STORAGE_URL || 'http://127.0.0.1:8000/storage';

export function getImageUrl(path: string | null | undefined): string | null {
  if (!path) return null;
  if (path.startsWith('http')) return path;
  return `${STORAGE_URL}/${path.replace(/^\/+/, '')}`;
}

export async function fetchApi<T>(endpoint: string, options: RequestInit = {}): Promise<T> {
  const res = await fetch(`${API_URL}${endpoint}`, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      ...options.headers,
    },
  });

  if (!res.ok) {
    throw new Error(`API Error: ${res.statusText}`);
  }

  const json = await res.json();
  return json;
}

/** Fetch with Bearer token from localStorage */
export async function authFetchApi<T>(endpoint: string, options: RequestInit = {}): Promise<T> {
  const token = typeof window !== 'undefined' ? localStorage.getItem('auth_token') : null;
  return fetchApi<T>(endpoint, {
    ...options,
    headers: {
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...options.headers,
    },
  });
}

export const catalogApi = {
  getInitData: () => fetchApi<{ categories: any[] }>('/catalog'),
  getFilters: () => fetchApi<any>('/catalog/filters'),
  getCollection: (slug: string, params: string = '') => 
    fetchApi<any>(`/collections/${slug}?${params}`),
  getLineProducts: (categorySlug: string, lineSlug: string) => 
    fetchApi<any>(`/collections/${categorySlug}?product_line=${lineSlug}`),
  getProduct: (slug: string) => 
    fetchApi<any>(`/products/${slug}`),
  search: (params: string = '') =>
    fetchApi<any>(`/search?${params}`),
  getSearchSuggestions: (q: string = '') =>
    fetchApi<any>(`/search/suggestions?q=${q}`),
  getHomeData: () => 
    fetchApi<import('../types').HomeData>('/home', { next: { revalidate: 60 } }),
  getPromotions: () =>
    fetchApi<import('../types').PromotionPageData>('/promotions', { next: { revalidate: 60 } }),
  getPromotionsPageBanners: () =>
    fetchApi<{ banners: import('../types').PromotionBanner[] }>('/promotions-page-banners', { next: { revalidate: 60 } }),
  evaluateCart: (payload: { items: any[], coupon_code?: string, payment_method?: string }) =>
    fetchApi<any>('/cart/evaluate', {
      method: 'POST',
      body: JSON.stringify(payload),
    }),
  getStores: () => fetchApi<any>('/stores'),
  getShippingMethods: () => fetchApi<any>('/shipping-methods'),
  calculateShippingFee: (payload: { province?: string, district?: string, ward?: string, provider: string }) =>
    fetchApi<any>('/shipping/calculate-fee', {
      method: 'POST',
      body: JSON.stringify(payload),
    }),
};

export const checkoutApi = {
  getAddresses: () => authFetchApi<any>('/user/addresses'),
  addAddress: (data: any) => authFetchApi<any>('/user/addresses', {
    method: 'POST',
    body: JSON.stringify(data),
  }),
  updateAddress: (id: number, data: any) => authFetchApi<any>(`/user/addresses/${id}`, {
    method: 'PUT',
    body: JSON.stringify(data),
  }),
  checkout: (data: any) => authFetchApi<any>('/orders/checkout', {
    method: 'POST',
    body: JSON.stringify(data),
  }),
  getOrders: (page: number = 1) => authFetchApi<any>(`/orders?page=${page}`),
  getOrderDetail: (number: string) => authFetchApi<any>(`/orders/${number}`),
};

export const voucherApi = {
  /** Lấy danh sách voucher kèm trạng thái eligible (requires auth token) */
  getVouchers: (cartTotal: number, cartItems: any[]) =>
    authFetchApi<any>(`/vouchers?cart_total=${cartTotal}&cart_items=${encodeURIComponent(JSON.stringify(cartItems))}`),

  /** Áp dụng voucher (requires auth token) */
  applyVoucher: (code: string, cartTotal: number, cartItems: any[]) =>
    authFetchApi<any>('/vouchers/apply', {
      method: 'POST',
      body: JSON.stringify({ code, cart_total: cartTotal, cart_items: cartItems }),
    }),

  /** Validate mã nhập tay (guest OK) */
  validateCode: (code: string, cartTotal: number, cartItems: any[]) =>
    fetchApi<any>('/vouchers/validate-code', {
      method: 'POST',
      body: JSON.stringify({ code, cart_total: cartTotal, cart_items: cartItems }),
    }),
};
