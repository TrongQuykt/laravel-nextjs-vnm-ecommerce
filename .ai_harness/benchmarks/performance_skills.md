# Performance Skills for Vinamilk Website

Source: dựa trên ECC benchmark concepts, Web Vitals, và API performance monitoring.

## Mục tiêu hiệu suất
1. Web Vitals
   - LCP (Largest Contentful Paint)
     - Mục tiêu: <= 2.5s trên page chính.
   - CLS (Cumulative Layout Shift)
     - Mục tiêu: < 0.1.
   - INP (Interaction to Next Paint) hoặc TTI tương đương
     - Mục tiêu: <= 200ms cho interaction quan trọng.

2. API latency
   - API responses phải đạt p95 <= 200ms cho các endpoint quan trọng như product, promotion, cart.
   - p99 <= 500ms cho các endpoint quan trọng.
   - Phải cân nhắc caching, batching, và giảm payload khi cần.

3. Asset optimization
   - Ảnh sản phẩm, banner phải tối ưu kích thước, dùng lazy loading nếu phù hợp.
   - JavaScript/CSS bundles phải được giảm thiểu và chỉ tải những mã cần thiết cho trang hiện tại.
   - Kiểm tra tree-shaking, code-splitting, và `font-display: swap`.
   - Xem xét dùng `next/image`, `responsive images`, `modern formats` (WebP/AVIF) cho hình ảnh sữa và banner.

## Kiểm tra chi tiết
- Gợi ý công cụ:
  - Lighthouse, WebPageTest, Chrome DevTools Web Vitals.
  - `next build` / `next lint` / `next export` nếu dùng Next.js.
  - Audit bundle size bằng `source-map-explorer`, `webpack-bundle-analyzer`, hoặc `next build && next export`.

- Kiểm tra API:
  - Không trả về payload dư thừa.
  - Các endpoint promotions / product / cart cần trả dữ liệu chỉ cần thiết.
  - Áp dụng caching ở edge hoặc CDN khi phù hợp.

- Kiểm tra asset:
  - Hình ảnh sản phẩm và banner không quá lớn; kích thước thực tế phù hợp viewport.
  - CSS/JS critical được inline hoặc tải theo priority hợp lý.
  - Asset static phải có caching headers hợp lý.

## Rubric đánh giá Pass/Fail
- Pass
  - Các chỉ số Web Vitals chính đạt mục tiêu.
  - Bundle và asset tối ưu, không có tăng trưởng kích thước đáng kể đối với tính năng tương tự.
  - API latency p95/p99 ở mức chấp nhận được với logic hiện tại.

- Fail
  - LCP > 2.5s, CLS >= 0.1 hoặc INP > 200ms trên page chính.
  - Hình ảnh/JS/CSS không tối ưu gây tăng dung lượng lớn.
  - API endpoint quan trọng chịu p95/p99 quá cao mà không có kế hoạch tối ưu.
