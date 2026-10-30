# Văn Trường Luxury Mobile - Thế Giới Flagship Chính Hãng

Hệ thống Website bán điện thoại & Smartphone Flagship cao cấp (iPhone, Like New, Phụ kiện) với **Frontend 100% HTML, CSS (Tailwind) & JavaScript thuần**, tích hợp hệ thống quản trị admin backend PHP & MySQL.

> **Đồ án chuyên ngành Web Năm 3**  
> **Sinh viên thực hiện**: Lê Văn Trường  
> **Tông màu nhận diện**: Đỏ Ruby & Đen Titan (Crimson & Obsidian Luxury) - Khác biệt hoàn toàn so với phong cách Apple xanh cổ điển.

---

## Cấu trúc Frontend (100% HTML / CSS / JS)

Toàn bộ giao diện khách hàng (Storefront) đã được chuẩn hóa **100% HTML** với thiết kế hiện đại, tương thích tốt trên mọi thiết bị và không yêu cầu máy chủ PHP để hiển thị:

- [index.html](file:///index.html): Trang chủ Flagship Storefront cao cấp (Hero Billboard, iPhone 17/16, khuyến mãi giao 2h, ưu đãi thu cũ đổi mới 4 triệu).
- [products.html](file:///products.html), [products-2.html](file:///products-2.html), [products-3.html](file:///products-3.html): Danh sách sản phẩm (iPhone mới VN/A, máy Like New 99%, phụ kiện cao cấp).
- [product-detail.html](file:///product-detail.html): Trang chi tiết sản phẩm đầy đủ cấu hình, chọn màu, chọn dung lượng, nút "Mua ngay" và "Thêm vào giỏ".
- [categories.html](file:///categories.html): Phân loại danh mục theo từng series iPhone và hệ sinh thái Flagship.
- [cart.html](file:///cart.html): Giỏ hàng tương tác 100% HTML/JS (tăng giảm số lượng, xóa sản phẩm, áp dụng voucher `TRUONGVIP`, `VTLUXURY10`, `WELCOMEVT`).
- [checkout.html](file:///checkout.html): Thanh toán đơn hàng với 4 phương thức (COD, VietQR tự động, Thẻ tín dụng, Trả góp 0%), lưu đơn hàng vào `localStorage`.
- [login.html](file:///login.html): Đăng nhập tài khoản khách hàng (hỗ trợ nút 1-chạm đăng nhập tài khoản mẫu của Văn Trường).
- [register.html](file:///register.html): Đăng ký tài khoản thành viên mới.
- [profile.html](file:///profile.html): Quản lý hồ sơ cá nhân, đổi mật khẩu, xem thống kê chi tiêu và lịch sử đơn hàng.
- [logout.html](file:///logout.html): Đăng xuất tài khoản an toàn và chuyển về trang chủ.
- [my-orders.html](file:///my-orders.html): Quản lý danh sách đơn hàng đã đặt, trạng thái giao vận.
- [order-detail.html](file:///order-detail.html): Chi tiết từng đơn hàng, hành trình vận chuyển hỏa tốc.
- [compare.html](file:///compare.html): Công cụ so sánh thông số kỹ thuật các đời smartphone.
- [search.html](file:///search.html): Tìm kiếm sản phẩm theo tên và phân loại.
- [about.html](file:///about.html): Giới thiệu hệ thống showroom Văn Trường Luxury Mobile.
- [contact.html](file:///contact.html): Thông tin hỗ trợ và liên hệ CSKH.
- [warranty.html](file:///warranty.html): Chính sách bảo hành VIP 12 - 24 tháng 1-đổi-1.
- [returns.html](file:///returns.html): Chính sách đổi trả 30 ngày linh hoạt.

---

## Quản lý trạng thái Frontend (Client Store)

- File lưu trữ: `assets/js/store.js`
- Quản lý đồng bộ:
  - Giỏ hàng (`localStorage: vantruong_cart`)
  - Tài khoản đăng nhập (`localStorage: vantruong_user`)
  - Danh sách đơn hàng (`localStorage: vantruong_orders`)
  - Mã giảm giá (`localStorage: vantruong_coupon`)
  - Badge số lượng giỏ hàng tự động cập nhật trên toàn bộ các trang.

---

## Cơ sở dữ liệu (Database MySQL)

- Tên CSDL mới: **`vantruong_mobile`**
- File SQL khởi tạo:
  - `database/schema.sql` (hoặc `database/schema_vantruong.sql`)
- Cấu hình kết nối: `config/database.php`
- Bảng dữ liệu: `users`, `admins`, `categories`, `products`, `product_variants`, `orders`, `order_details`, `payments`, `coupons`, `reviews`, `inventory_logs`, `order_status_logs`.

---

## Cách chạy dự án

### 1. Chạy Frontend (100% HTML)
- Mở trực tiếp file `index.html` trên trình duyệt bằng cách nhấp đúp chuột hoặc dùng Live Server trong VSCode.
- Hoặc mở nhanh qua PowerShell:
  ```powershell
  Start-Process "index.html"
  ```

### 2. Chạy Backend Quản trị (Admin PHP + MySQL)
1. Mở XAMPP Control Panel và bật **Apache + MySQL**.
2. Mở phpMyAdmin (`http://localhost/phpmyadmin/`), tạo cơ sở dữ liệu `vantruong_mobile`.
3. Import file `database/schema.sql` (hoặc `database/schema_vantruong.sql`).
4. Khởi động server PHP:
   ```powershell
   php -S 127.0.0.1:8000
   ```
5. Đăng nhập trang quản trị Admin: `http://127.0.0.1:8000/admin/login.php`

---

## Tài khoản mẫu thử nghiệm

- **Khách hàng**: `truong@example.com` / `admin123` (Họ tên: Lê Văn Trường, có nút 1-chạm tại `login.html`)
- **Admin**: `admin` / `admin123` (Họ tên: Quản trị viên Văn Trường)
- **Mã voucher ưu đãi**:
  - `TRUONGVIP`: Giảm 500.000₫ cho đơn từ 15.000.000₫
  - `VTLUXURY10`: Giảm 10% tối đa 1.500.000₫ cho đơn từ 2.000.000₫
  - `WELCOMEVT`: Giảm 200.000₫ cho đơn từ 5.000.000₫
