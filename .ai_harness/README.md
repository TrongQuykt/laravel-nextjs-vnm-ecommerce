# AI Harness for Vinamilk Website

Đây là thư mục cấu hình AI harness cho dự án Vinamilk.

## Mục đích
- Giữ một bộ quy tắc kiểm tra an toàn và hiệu suất rõ ràng.
- Cho phép Agent đọc và đánh giá code trước khi viết/chỉnh sửa tính năng.

## Các file chính
- `.ai_harness/security/security_rubric.md`
  - Rubric an ninh bảo mật: kiểm tra secret, OWASP, SQL injection, XSS, dependency audit.
- `.ai_harness/benchmarks/performance_skills.md`
  - Rubric hiệu suất: Web Vitals, API latency, asset optimization.

## Cách sử dụng
1. Trước khi viết hoặc chỉnh sửa mã, Agent sẽ mở và đọc hai file rubric này.
2. Agent sẽ tự động đánh giá code dựa trên các tiêu chí Pass/Fail của từng file.
3. Mọi thay đổi phải được đối chiếu với rubric để đảm bảo không nới lỏng bảo mật hoặc hiệu suất.

## Triển khai theo mô hình ECC
- Nội dung được xây dựng dựa trên các chỉ dẫn kiểm định của ECC, bao gồm:
  - `the-security-guide.md`
  - `EVALUATION.md`
  - `RULES.md`
- Agent sẽ dùng các file này như bước đầu trong verification loop / Santa-loop để đánh giá nhanh code trước khi hoàn thành tác vụ.

## Ghi chú
- Đây là cấu trúc cơ bản, có thể mở rộng thêm:
  - `.ai_harness/security/` cho các cheat sheet, policy cụ thể.
  - `.ai_harness/benchmarks/` cho scripts hoặc metrics config nếu cần.
- Trong mọi nhiệm vụ tiếp theo, tôi sẽ luôn đọc hai file rubric này trước khi đưa ra giải pháp hoặc chỉnh sửa code.
