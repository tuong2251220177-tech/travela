# 📱 HƯỚNG DẪN CHỨC NĂNG HƯỚNG DẪN VIÊN (GUIDE) - TRAVELA

## 🔑 Thông tin đăng nhập

**Tài khoản demo:**
- Username: `guide`
- Password: `guide123`
- URL: `http://localhost:8000/guide/login`

---

## ✨ Danh sách chức năng

### 1️⃣ **Đăng nhập hướng dẫn viên**
- **URL:** `GET /guide/login`
- **Chức năng:** Đăng nhập với tài khoản guide
- **Input:** Username, Password (md5 hashed)
- **Output:** Chuyển hướng đến dashboard nếu đăng nhập thành công
- **File:** `app/Http/Controllers/guide/DieuKhienDangNhapHuongDanVien.php`
- **Test:** ✅ PASS

---

### 2️⃣ **Xem Dashboard Hướng Dẫn Viên**
- **URL:** `GET /guide/dashboard`
- **Yêu cầu:** Phải đăng nhập (middleware 'guide')
- **Hiển thị:**
  - Số tour được phân công
  - Số tour đang diễn ra
  - Số khách đã điểm danh
  - Số báo cáo đã gửi
  - Danh sách tour (tên, địa điểm, ngày khởi hành, điểm tập trung, trạng thái)
- **File:** `resources/views/guide/bang-dieu-khien.blade.php`
- **Test:** ✅ PASS (1 tour được phân công)

---

### 3️⃣ **Xem Chi Tiết Tour**
- **URL:** `GET /guide/tour-detail/{tourId}`
- **Hiển thị:**
  - Tên tour
  - Địa điểm
  - Mô tả chi tiết (HTML)
  - Thời gian tour (số ngày/đêm)
  - Giá người lớn & giá trẻ em
  - Điểm tập trung
  - Ngày khởi hành
  - Trạng thái tour
- **File:** `resources/views/guide/chi-tiet-tour.blade.php`
- **Test:** ✅ PASS (Tour ID 8: Cần Thơ - Chợ Nổi Cái Răng)

---

### 4️⃣ **Xem Danh Sách Khách Trong Tour**
- **URL:** `GET /guide/tour-detail/{tourId}` (phần bảng khách)
- **Hiển thị theo bảng:**
  - **Họ tên:** Tên khách đặt tour
  - **SĐT:** Số điện thoại liên lạc
  - **Số người:** Tổng số người lớn + trẻ em
  - **Yêu cầu đặc biệt:** Ghi chú từ khách
  - **Trạng thái:** confirmed, present, absent, completed
  - **Điểm danh:** Form cập nhật trạng thái
- **Dữ liệu từ:** `tbl_booking`, `tbl_users`, `tbl_guide_booking_status`
- **Test:** ✅ PASS (1 khách: Test Booking Person, 0987654321, 2 người lớn + 1 trẻ em)

---

### 5️⃣ **Xem Đánh Giá Từ Khách**
- **URL:** `GET /guide/tour-detail/{tourId}` (phần review)
- **Hiển thị:**
  - **Tên khách:** Từ tbl_users
  - **Rating:** 1-5 sao
  - **Bình luận:** Comment từ khách
  - **Ngày giờ:** Timestamp đánh giá
- **Dữ liệu từ:** `tbl_reviews`
- **Test:** ✅ PASS (1 review: 4.5 sao, "Tour rất hay! Hướng dẫn viên chuyên nghiệp...")

---

### 6️⃣ **Cập Nhật Trạng Thái Tour**
- **URL:** `POST /guide/tour-status/{tourId}`
- **Input fields:**
  - **Trạng thái tour:** assigned, in_progress, completed, cancelled
  - **Điểm tập trung:** Text (ví dụ: Bến xe Mỹ Đình - Sáng 7h)
  - **Ngày khởi hành:** Date (YYYY-MM-DD)
  - **Ghi chú sự cố:** Textarea (ví dụ: Khách vui, thời tiết tốt)
  - **Báo cáo sau chuyến đi:** Textarea (ví dụ: Tất cả khách hài lòng, không sự cố)
- **Lưu vào:** `tbl_guide_assignments`
- **Test:** ✅ PASS (Cập nhật tour 8 status = in_progress)

---

### 7️⃣ **Điểm Danh & Cập Nhật Trạng Thái Khách**
- **URL:** `POST /guide/guest-status/{tourId}`
- **Input fields:**
  - **Trạng thái khách:** 
    - `confirmed` = Đã xác nhận
    - `present` = Đã có mặt
    - `absent` = Vắng mặt
    - `completed` = Đã hoàn thành tour
  - **Ghi chú:** Text (ví dụ: Khách có mặt, khỏe mạnh)
- **Lưu vào:** `tbl_guide_booking_status`
- **Test:** ✅ PASS (Cập nhật khách status = present)

---

### 8️⃣ **Quản Lý Tài Khoản Cá Nhân**
- **URL:** `GET /guide/profile` và `POST /guide/profile`
- **Hiển thị & chỉnh sửa:**
  - Username (readonly)
  - Email
  - Điện thoại
  - Địa chỉ
  - Avatar (nếu có)
- **Lưu vào:** `tbl_admin`
- **File:** `resources/views/guide/ho-so.blade.php`
- **Test:** ✅ PASS (Admin có email, địa chỉ)

---

### 9️⃣ **Admin Phân Công Tour Cho Guide**
- **URL:** 
  - `GET /admin/guide-assignment` (xem form)
  - `POST /admin/guide-assignment` (lưu phân công)
- **Input fields:**
  - **Hướng dẫn viên:** Dropdown (tất cả admin)
  - **Tour:** Dropdown (tất cả tours)
  - **Điểm tập trung:** Text (nullable)
  - **Ngày khởi hành:** Date (nullable)
  - **Trạng thái tour:** assigned, in_progress, completed, cancelled
  - **Ghi chú sự cố:** Textarea (nullable)
- **Lưu vào:** `tbl_guide_assignments`
- **Bảng hiển thị:** Danh sách phân công hiện tại
- **File:** `resources/views/admin/phan-cong-huong-dan-vien.blade.php`
- **Menu:** Sidebar admin > "Phân công hướng dẫn viên"
- **Test:** ✅ PASS (1 phân công: guide ID 1, tour ID 8)

---

## 🗄️ Cấu Trúc Database

### Bảng: `tbl_guide_assignments`
```
- assignment_id (PRIMARY KEY)
- guide_id (FK → tbl_admin.adminId)
- tour_id (FK → tbl_tours.tourId)
- meeting_point (VARCHAR)
- departure_date (DATE)
- tour_status (VARCHAR: assigned, in_progress, completed, cancelled)
- incident_note (TEXT)
- summary_report (TEXT)
- created_at, updated_at
- UNIQUE(guide_id, tour_id)
```

### Bảng: `tbl_guide_booking_status`
```
- status_id (PRIMARY KEY)
- guide_id (FK → tbl_admin.adminId)
- tour_id (FK → tbl_tours.tourId)
- booking_id (FK → tbl_booking.bookingId)
- guest_status (VARCHAR: confirmed, present, absent, completed)
- note (TEXT)
- created_at, updated_at
- UNIQUE(guide_id, tour_id, booking_id)
```

---

## 🔒 Quyền Hạn Guide

### ✅ **Được phép làm:**
- Xem danh sách tour được phân công
- Xem chi tiết tour (mô tả, giá, lịch trình)
- Xem danh sách khách trong tour
- Xem thông tin booking (họ tên, SĐT, số người, yêu cầu đặc biệt)
- Cập nhật trạng thái tour (assigned → in_progress → completed)
- Ghi chú sự cố và báo cáo tour
- Điểm danh khách (confirmed, present, absent, completed)
- Xem đánh giá từ khách
- Cập nhật thông tin cá nhân (email, điện thoại, địa chỉ)
- Đăng xuất

### ❌ **Không được phép:**
- Thêm, sửa, xóa tour
- Quản lý người dùng khách
- Xem doanh thu / quản lý tài chính
- Xác nhận thanh toán
- Phân công hướng dẫn viên khác
- Xem thông tin khác của guides
- Truy cập phần admin

---

## 📊 Tóm Tắt Test Results

| # | Chức Năng | Status | File |
|---|-----------|--------|------|
| 1 | Đăng nhập guide | ✅ PASS | DieuKhienDangNhapHuongDanVien |
| 2 | Dashboard | ✅ PASS | DieuKhienBangDieuKhienHuongDanVien |
| 3 | Chi tiết tour | ✅ PASS | DuLieuPhanCongHuongDanVien |
| 4 | Danh sách khách | ✅ PASS | DuLieuPhanCongHuongDanVien |
| 5 | Đánh giá khách | ✅ PASS | tbl_reviews |
| 6 | Cập nhật trạng thái tour | ✅ PASS | saveTourStatus() |
| 7 | Điểm danh khách | ✅ PASS | saveGuestStatus() |
| 8 | Quản lý tài khoản | ✅ PASS | updateProfile() |
| 9 | Admin phân công tour | ✅ PASS | DieuKhienPhanCongHuongDanVien |
| 10 | Bảng phân công | ✅ PASS | tbl_guide_assignments |

---

## 🧪 Cách test trên browser

### Test đăng nhập:
```
1. Truy cập: http://localhost:8000/guide/login
2. Username: guide
3. Password: guide123
4. Click: Đăng nhập
5. Kết quả: Chuyển hướng đến dashboard
```

### Test phân công (Admin):
```
1. Truy cập: http://localhost:8000/admin/login
2. Đăng nhập admin
3. Menu: Phân công hướng dẫn viên
4. Chọn guide + tour + ngày khởi hành
5. Click: Phân công
6. Kết quả: Thêm vào danh sách phân công
```

### Test guide xem tour:
```
1. Đăng nhập guide (guide/guide123)
2. Dashboard: Thấy tour được phân công
3. Click: Xem chi tiết
4. Kết quả: Xem khách, đánh giá, cập nhật trạng thái
```

---

## 📝 Notes

- **Demo account:** `guide / guide123` (fallback hardcode khi không tìm account trong DB)
- **Middleware:** KiemTraHuongDanVien - kiểm tra session('guide') trước khi vào protected routes
- **Quyền hạn:** Chỉ xem tour được phân công cho guide đó (WHERE guide_id = ?)
- **Đánh giá:** Hiển thị từ tbl_reviews khi khách rating tour
- **Thông báo:** Chưa implement (có thể thêm sau)
- **Email notification:** Chưa implement (có thể thêm sau)

---

**✅ HOÀN THÀNH:** Tất cả 10 chức năng được test và hoạt động đúng!
