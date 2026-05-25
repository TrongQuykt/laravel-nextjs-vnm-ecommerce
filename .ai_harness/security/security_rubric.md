# Security Rubric for Vinamilk Website

Source: derived from ECC security guidance and verification loop practices.

## Objective
Đây là bộ quy tắc kiểm tra an toàn bảo mật trước khi bất kỳ thay đổi nào được chấp nhận hoặc commit vào dự án.

## Kiểm tra cơ bản
1. Secret scan
   - Không có giá trị bí mật trong mã nguồn: API keys, tokens, database credentials.
   - Không đẩy file `.env` và không để cấu hình nhạy cảm trong git.
   - Kiểm tra các file cấu hình và môi trường (ví dụ: `.env`, `next.config.js`, `package.json`, `vercel.json`).

2. Dependency audit
   - Chạy audit dependency: `npm audit`, `yarn audit`, hoặc công cụ tương đương.
   - Không có vulnerability nghiêm trọng (critical/high) chưa được xử lý.
   - Nếu dependency không thể cập nhật ngay, phải có ghi chú rõ ràng và biện pháp giảm thiểu.

3. OWASP Top 10 / Web security
   - Xác thực và lọc input đầu vào ở cả client và server.
   - Mitigation cho XSS: escape output, dùng `next/image`, `dangerouslySetInnerHTML` phải được kiểm soát, Content Security Policy (CSP) khi khả thi.
   - Kiểm tra SQL injection / NoSQL injection: không dùng string concatenation cho queries.
   - Kiểm tra authentication/authorization: không có quyền truy cập chéo hoặc privilege escalation.
   - Bảo vệ CSRF nếu có forms stateful hoặc endpoints thay đổi trạng thái.
   - Chống SSRF/SSJI nếu có yêu cầu URL bên ngoài hoặc template rendering.

4. Configuration security
   - CORS chỉ mở với các origin tin cậy hoặc cấu hình hợp lý.
   - Cookie `Secure`, `HttpOnly`, `SameSite` được thiết lập khi phù hợp.
   - Không xuất hiện `eval()` hoặc mã dynamic không kiểm soát.

5. Audit và logging
   - Có logs hợp lý cho các lỗi bảo mật và các exception đáng chú ý.
   - Không ghi ra secrets trong logs.

## Rubric đánh giá Pass/Fail
- Pass
  - Tất cả kiểm tra trên được thực hiện và không có issue nghiêm trọng.
  - Dependency audit không còn vulnerabilities critical/high hoặc đã có kế hoạch giảm thiểu.
  - Không phát hiện secret leak trong repository.

- Fail
  - Phát hiện credentials, token hoặc file `.env` bị lộ trong mã nguồn.
  - Có issue OWASP nghiêm trọng chưa được giải quyết (XSS, SQLi, SSRF, auth bypass).
  - Dependency audit trả về critical/high vulnerability chưa được xử lý.

## Cách sử dụng
- Trước khi commit/change: đọc file này và áp dụng checklist từng mục.
- Khi tôi (Agent) viết mã mới hoặc chỉnh sửa tính năng, tôi sẽ tự động đối chiếu với rubric này.
- Nếu có mục không thể kiểm tra bằng code review đơn thuần, phải chú ý đến môi trường build/deploy và báo rõ.
