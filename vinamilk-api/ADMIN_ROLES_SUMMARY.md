# Tổng hợp phân quyền Admin - Vinamilk Core Ecommerce

## Hệ thống phân quyền Admin (Sắp xếp từ quyền cao nhất đến thấp nhất)

---

### 1. Super Admin (Siêu quản trị viên / Owner)
**Mô tả:** Tài khoản có quyền lực tối cao, chỉ cấp cho chủ sở hữu hệ thống

**Quyền hạn:**
- ✅ Toàn quyền xem, thêm, sửa, xóa mọi dữ liệu trên hệ thống
- ✅ Quản lý, tạo mới và phân quyền cho các tài khoản Admin khác
- ✅ Thay đổi cấu hình hệ thống, cài đặt thanh toán, bảo mật
- ✅ Xem các báo cáo doanh thu tối mật
- ✅ Truy cập tất cả các resources trong hệ thống

**Resources được phép truy cập:**
- Tất cả resources trong hệ thống

**Đối tượng:** Chủ doanh nghiệp, CTO/Lead Developer

---

### 2. System Admin (Quản trị viên hệ thống)
**Mô tả:** Cấp bậc ngay dưới Super Admin, chịu trách nhiệm vận hành hệ thống hàng ngày

**Quyền hạn:**
- ✅ Quản lý toàn bộ dữ liệu nghiệp vụ (Sản phẩm, Đơn hàng, Khách hàng, Bài viết)
- ✅ Có thể tạo hoặc khóa tài khoản của các nhân viên cấp dưới
- ✅ Quản lý ActivityLogResource (nhật ký hoạt động)
- ✅ Quản lý RolePermissionResource (phân quyền admin)
- ✅ Quản lý AdminUserResource (tài khoản admin)
- ✅ Quản lý ChatSettingResource, ChatKnowledgeResource (cấu hình chat)

**Hạn chế:**
- ❌ Không thể xóa tài khoản Super Admin
- ❌ Không can thiệp vào cấu hình cốt lõi của mã nguồn

**Resources được phép truy cập:**
- ActivityLogResource, AdminUserResource, RolePermissionResource
- ChatSettingResource, ChatKnowledgeResource
- Tất cả resources nghiệp vụ (Product, Order, User, Blog, etc.)

---

### 3. Shop Manager (Quản lý vận hành / Quản lý cửa hàng)
**Mô tả:** Người quản lý trực tiếp các hoạt động kinh doanh

**Quyền hạn:**
- ✅ Quản lý danh mục sản phẩm, kho hàng, giá cả
- ✅ Quản lý các chương trình khuyến mãi (Promotion, Coupon, Voucher)
- ✅ Xem báo cáo doanh thu, thống kê đơn hàng
- ✅ Xử lý các khiếu nại hoặc hủy đơn hàng lớn
- ✅ Quản lý StoreResource (cửa hàng)
- ✅ Quản lý OrderResource (đơn hàng)

**Hạn chế:**
- ❌ Không có quyền quản lý nhân sự
- ❌ Không can thiệp vào kỹ thuật phần mềm

**Resources được phép truy cập:**
- ProductResource, CategoryResource, BrandResource
- PromotionCampaignResource, PromotionFlashSaleResource
- VoucherResource, CouponResource, MarketingRuleResource
- OrderResource, StoreResource
- StatsOverview, RevenueChartWidget (xem báo cáo)

---

### 4. Logistics Manager (Quản lý vận chuyển)
**Mô tả:** Quản lý vận hành vận chuyển và đóng gói

**Quyền hạn:**
- ✅ Quản lý LogisticsResource (đối tác vận chuyển)
- ✅ Quản lý ShippingMethodResource (phương thức vận chuyển)
- ✅ Quản lý PackingResource (quy cách đóng gói)
- ✅ Quản lý PackagingTypeResource (loại bao bì)
- ✅ Xem/điều phối đơn hàng liên quan đến vận chuyển
- ✅ Quản lý CareDeliveryOptionResource (tùy chọn giao hàng)

**Hạn chế:**
- ❌ Không xem doanh thu
- ❌ Không quản lý nhân sự
- ❌ Không quản lý sản phẩm

**Resources được phép truy cập:**
- LogisticsResource, ShippingMethodResource
- PackingResource, PackagingTypeResource
- CareDeliveryOptionResource
- OrderResource (chỉ xem trạng thái vận chuyển)

---

### 5. Product Manager (Quản lý sản phẩm)
**Mô tả:** Quản lý danh mục sản phẩm và thông tin sản phẩm

**Quyền hạn:**
- ✅ Quản lý ProductResource (sản phẩm)
- ✅ Quản lý CategoryResource (danh mục)
- ✅ Quản lý BrandResource (thương hiệu)
- ✅ Quản lý ProductLineResource (dòng sản phẩm)
- ✅ Quản lý FlavorResource (hương vị)
- ✅ Quản lý VolumeResource (dung tích)
- ✅ Quản lý SugarLevelResource (độ đường)
- ✅ Quản lý AgeGroupResource (nhóm tuổi)
- ✅ Quản lý NutritionalNeedResource (nhu cầu dinh dưỡng)
- ✅ Quản lý CertificateResource (chứng chỉ)
- ✅ Quản lý CareProductResource (sản phẩm chăm sóc)

**Hạn chế:**
- ❌ Không xem doanh thu
- ❌ Không quản lý đơn hàng
- ❌ Không quản lý giá (nếu cần tách riêng)

**Resources được phép truy cập:**
- ProductResource, CategoryResource, BrandResource
- ProductLineResource, FlavorResource, VolumeResource
- SugarLevelResource, AgeGroupResource, NutritionalNeedResource
- CertificateResource, CareProductResource

---

### 6. Marketing Manager (Quản lý Marketing)
**Mô tả:** Quản lý các chiến dịch marketing và khuyến mãi

**Quyền hạn:**
- ✅ Quản lý PromotionCampaignResource (chiến dịch khuyến mãi)
- ✅ Quản lý PromotionFlashSaleResource (Flash Sale)
- ✅ Quản lý VoucherResource (Voucher)
- ✅ Quản lý CouponResource (Coupon)
- ✅ Quản lý MarketingRuleResource (quy tắc marketing)
- ✅ Quản lý MarketingGiftResource (quà tặng)
- ✅ Quản lý PromotionBannerResource, PromotionsPageBannerResource
- ✅ Quản lý SpecialHighlightResource (nổi bật)
- ✅ Quản lý TrendingSearchResource (tìm kiếm xu hướng)
- ✅ Quản lý PromotionPageSettingResource, PromotionTermResource

**Hạn chế:**
- ❌ Không xem doanh thu thực tế
- ❌ Không quản lý sản phẩm
- ❌ Không quản lý đơn hàng

**Resources được phép truy cập:**
- PromotionCampaignResource, PromotionFlashSaleResource
- VoucherResource, CouponResource, MarketingRuleResource
- MarketingGiftResource, PromotionBannerResource
- PromotionsPageBannerResource, SpecialHighlightResource
- TrendingSearchResource, PromotionPageSettingResource, PromotionTermResource

---

### 7. Content Manager (Quản lý nội dung)
**Mô tả:** Quản lý nội dung website, blog, banner

**Quyền hạn:**
- ✅ Quản lý BlogPostResource, BlogCategoryResource
- ✅ Quản lý BannerResource
- ✅ Quản lý MegaMenuResource (menu)
- ✅ Quản lý SupportPageResource (trang hỗ trợ)
- ✅ Quản lý CarePageSettingResource (cài đặt trang chăm sóc)
- ✅ Quản lý PromotionsPageBannerResource

**Hạn chế:**
- ❌ Không xem dữ liệu kinh doanh
- ❌ Không quản lý sản phẩm
- ❌ Không quản lý đơn hàng

**Resources được phép truy cập:**
- BlogPostResource, BlogCategoryResource
- BannerResource, MegaMenuResource
- SupportPageResource, CarePageSettingResource
- PromotionsPageBannerResource

---

### 8. Order Processor (Nhân viên xử lý đơn hàng)
**Mô tả:** Nhân viên trực hotline, trực chat hoặc nhân viên đóng gói

**Quyền hạn:**
- ✅ Xem danh sách đơn hàng mới
- ✅ Chuyển trạng thái đơn hàng (Chờ tiếp nhận → Chờ đóng gói → Đang giao)
- ✅ Xem thông tin khách hàng để gọi điện xác nhận hoặc ship hàng
- ✅ Xem UserResource (khách hàng)

**Hạn chế:**
- ❌ Không được sửa giá sản phẩm
- ❌ Không được xem tổng doanh thu tháng/năm
- ❌ Không có quyền xóa dữ liệu
- ❌ Không quản lý nhân sự

**Resources được phép truy cập:**
- OrderResource (chỉ xem và cập nhật trạng thái)
- UserResource (chỉ xem thông tin cơ bản)

---

### 9. Customer Support Manager (Quản lý CSKH)
**Mô tả:** Quản lý đội ngũ hỗ trợ khách hàng

**Quyền hạn:**
- ✅ Quản lý ChatSettingResource, ChatKnowledgeResource
- ✅ Xem ChatMessageResource
- ✅ Xem UserResource (khách hàng)
- ✅ Xem OrderResource (để hỗ trợ khách)
- ✅ Quản lý RewardResource, RewardRedemptionResource (điểm thưởng)

**Hạn chế:**
- ❌ Không sửa giá
- ❌ Không xem doanh thu

**Resources được phép truy cập:**
- ChatSettingResource, ChatKnowledgeResource, ChatMessageResource
- UserResource, OrderResource (chỉ xem)
- RewardResource, RewardRedemptionResource

---

### 10. Finance Manager (Quản lý tài chính)
**Mô tả:** Quản lý tài chính, thanh toán, thuế

**Quyền hạn:**
- ✅ Quản lý PaymentResource, PaymentLogResource
- ✅ Quản lý VatOrderResource (đơn hàng VAT)
- ✅ Xem báo cáo doanh thu, thống kê tài chính
- ✅ Xem StatsOverview, RevenueChartWidget

**Hạn chế:**
- ❌ Không quản lý sản phẩm
- ❌ Không quản lý vận hành
- ❌ Không quản lý nhân sự

**Resources được phép truy cập:**
- PaymentResource, PaymentLogResource
- VatOrderResource
- StatsOverview, RevenueChartWidget (xem báo cáo tài chính)

---

### 11. Store Manager (Quản lý cửa hàng/điểm bán)
**Mô tả:** Quản lý từng cửa hàng riêng (nếu là multi-store)

**Quyền hạn:**
- ✅ Quản lý StoreResource (cửa hàng)
- ✅ Quản lý TenantResource (tenant - nếu là multi-tenant)
- ✅ Xem đơn hàng theo cửa hàng
- ✅ Quản lý kho hàng của cửa hàng

**Hạn chế:**
- ❌ Chỉ xem dữ liệu cửa hàng của mình
- ❌ Không xem dữ liệu toàn hệ thống
- ❌ Không quản lý cửa hàng khác

**Resources được phép truy cập:**
- StoreResource (chỉ cửa hàng của mình)
- TenantResource (chỉ tenant của mình)
- OrderResource (chỉ đơn hàng của cửa hàng)
- ProductResource (chỉ xem kho của cửa hàng)

---

### 12. Care Manager (Quản lý gói chăm sóc)
**Mô tả:** Quản lý mảng sản phẩm chăm sóc khách hàng

**Quyền hạn:**
- ✅ Quản lý CareProductResource (sản phẩm chăm sóc)
- ✅ Quản lý CareSubscriptionResource (gói đăng ký)
- ✅ Quản lý CareDeliveryOptionResource (tùy chọn giao hàng)
- ✅ Quản lý CareGreetingCardResource (thiệp chúc)
- ✅ Quản lý CarePageSettingResource (cài đặt trang)

**Hạn chế:**
- ❌ Chỉ quản lý mảng Care
- ❌ Không quản lý sản phẩm thường
- ❌ Không xem doanh thu toàn hệ thống

**Resources được phép truy cập:**
- CareProductResource, CareSubscriptionResource
- CareDeliveryOptionResource, CareGreetingCardResource
- CarePageSettingResource

---

## Bảng tóm tắt quyền hạn theo Resource

| Resource | Super Admin | System Admin | Shop Manager | Logistics | Product | Marketing | Content | Order Processor | CSKH | Finance | Store | Care |
|----------|-------------|--------------|--------------|-----------|---------|-----------|---------|-----------------|------|---------|-------|------|
| ActivityLogResource | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| AdminUserResource | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| RolePermissionResource | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| ProductResource | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅* | ❌ |
| CategoryResource | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅* | ❌ |
| OrderResource | ✅ | ✅ | ✅ | ✅* | ❌ | ❌ | ❌ | ✅* | ✅* | ❌ | ✅* | ❌ |
| UserResource | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ✅* | ✅ | ❌ | ✅* | ❌ |
| LogisticsResource | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| ShippingMethodResource | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| PromotionCampaignResource | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| VoucherResource | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| BlogPostResource | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| BannerResource | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| PaymentResource | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ |
| StoreResource | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅* | ❌ |
| CareProductResource | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |

*Chỉ xem hoặc chỉ quản lý dữ liệu của cửa hàng/tài khoản của mình

---

## Lưu ý triển khai

1. **Sử dụng Spatie Laravel Permission** để quản lý roles và permissions
2. **Sử dụng trait HasRolePermissions** đã tạo để kiểm tra quyền truy cập
3. **Mỗi role cần được gán permissions tương ứng cho từng resource**
4. **Super Admin bypass tất cả các kiểm tra quyền**
5. **Activity logging đã được tích hợp cho RolePermissionResource**

---

## Các bước tiếp theo

1. Tạo các roles trong database thông qua seeder hoặc admin panel
2. Gán permissions cho từng role dựa trên bảng tóm tắt ở trên
3. Áp dụng trait HasRolePermissions cho tất cả resources
4. Test quyền truy cập cho từng role
