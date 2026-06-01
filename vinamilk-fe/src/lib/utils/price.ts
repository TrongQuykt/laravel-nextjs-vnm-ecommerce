/**
 * Format price to VND currency
 */
export function formatVnd(price: number): string {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
  }).format(price);
}

/**
 * Format price to number with thousands separator
 */
export function formatPrice(price: number): string {
  return new Intl.NumberFormat('vi-VN').format(price);
}

/**
 * Parse price string to number
 */
export function parsePrice(priceStr: string): number {
  return parseFloat(priceStr.replace(/[.,]/g, ''));
}
