<!-- BEGIN:nextjs-agent-rules -->
# This is NOT the Next.js you know

This version has breaking changes — APIs, conventions, and file structure may all differ from your training data. Read the relevant guide in `node_modules/next/dist/docs/` before writing any code. Heed deprecation notices.
<!-- END:nextjs-agent-rules -->
# 🎯 FE UI/UX Design Rules – Vinamilk Style (Strict Replication)

## 🚨 Core Principle

Tất cả thiết kế FE UI/UX **PHẢI tái hiện 100% phong cách của website Vinamilk**, không được sáng tạo lệch khỏi hệ thống design gốc. Mọi thành phần đều phải đảm bảo **giống về cảm nhận (look & feel), bố cục (layout), và trải nghiệm (UX)**.

---

## 🎨 1. Color System (Màu sắc)

* Sử dụng **tone trắng + xanh navy + xanh dương nhạt** làm chủ đạo
* Background:

  * Chủ yếu: `#fefef0` (màu cream)
  * Section phụ: #d3e1ff;
* Primary color:

  * Xanh Vinamilk (navy / #0213b0)
* Không sử dụng màu quá nổi, neon hoặc lệch tone brand
* Border:

  * #d3e1ff

---

## 🔤 2. Typography (Font chữ)

* Font:

  * Sans-serif hiện đại (giống Vinamilk)
  * Ưu tiên: **tròn chữ, chuẩn font tiếng việt, font mượt, dễ đọc, spacing thoáng**
* Heading:

  * Đậm, rõ ràng, tracking nhẹ
* Body text:

  * #0213b0 cho tiêu đề, nội dung, số,...
* Line-height:

  * Thoáng, dễ đọc (1.5–1.8)
* Không dùng font fancy / decorative

---

## 📐 3. Layout & Spacing

* Layout:

  * Rộng, thoáng, nhiều khoảng trắng
  * Grid rõ ràng (max-width center)
* Section:

  * Padding lớn (80px – 120px)
* Không nhồi nhét nội dung
* Căn chỉnh:

  * Chuẩn pixel, thẳng hàng tuyệt đối
* Responsive:

  * Mobile-first nhưng vẫn giữ bố cục Vinamilk

---

## 🔘 4. Buttons

* Shape:

  * Bo góc vừa khoảng 6-8%
* Style:

  * Primary: nền xanh, chữ trắng
  * Secondary: nền xanh nhạt, chữ navy
* Hover:

  * Smooth transition (0.3–0.5s)
  * Đổi màu nhẹ, không giật
* Không dùng shadow quá nặng

---

## 📋 5. Form Elements (Input, Select, Option)

* Input:

  * Viền mảnh, bo góc nhẹ
  * Focus: highlight xanh nhẹ
* Select:

  * Clean, tối giản
  * Dropdown mượt
* Option:

  * Hover highlight nhẹ
* Không dùng style mặc định xấu của browser

---

## 🔢 6. Pagination

* Thiết kế:

  * Gọn, rõ ràng
  * Số trang dạng button bo góc
* Active:

  * Highlight xanh
* Hover:

  * Transition nhẹ
* Không dùng pagination kiểu cũ (text link thô)

---

## 🧩 7. Components

* Card:

  * Background trong suốt 
  * Bo góc nhẹ
  * Tag: góc trên bên trái, nền xanh nhạt (#d3e1ff), chữ navy (#0213b0)
* Image:

  * Full width, crop đẹp
  * Không méo
* Icon:

  * Line icon / minimal

---

## 🎞️ 8. Animation & Interaction

* Animation:

  * Mượt, tinh tế
  * Duration: 300–600ms
* Không dùng animation phức tạp, flashy
* Scroll:

  * Fade-in, slide nhẹ
* Hover:

  * subtle, không quá mạnh

---

## 🧠 9. UX Principles

* Trực quan, dễ hiểu ngay lập tức
* Không làm user suy nghĩ nhiều
* Navigation rõ ràng
* Ưu tiên trải nghiệm "premium – clean – trust"

---

## ❌ 10. Những điều KHÔNG được làm

* Không sáng tạo layout khác Vinamilk
* Không dùng màu lạ
* Không dùng font khác style
* Không dùng UI kiểu startup/tech (dark mode, neon, glassmorphism…)
* Không làm rối UI

---

## ✅ 11. Checklist trước khi hoàn thành

* [ ] Giống Vinamilk về màu sắc
* [ ] Giống layout & spacing
* [ ] Button đúng style
* [ ] Font đúng cảm giác brand
* [ ] UI sạch, thoáng, cao cấp
* [ ] Animation mượt, không lố

---

## 🏁 Kết luận

Mục tiêu không phải “lấy cảm hứng”, mà là:

> **Clone lại trải nghiệm Vinamilk ở mức gần như tuyệt đối.**

## Tôi đã lấy được file css của website vinamilk rồi, phân tích màu sắc, font chữ, layout, spacing, button, form elements, pagination, components, animation & interaction, ux principles, những điều không được làm, checklist trước khi hoàn thành: [text](vnm.css)
