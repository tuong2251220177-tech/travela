# Quản lý tài khoản hướng dẫn viên

Vào **Admin → Quản lý hướng dẫn viên** (`/admin/guides`).

- Tìm theo họ tên, tên đăng nhập hoặc email; danh sách chia trang 15 tài khoản.
- Lọc **Chưa được phân công** để xem các tài khoản có thể dọn. Có thể tìm `guide_test_`, `guide_profile_`, `guide_update_` để rà soát tài khoản thử nghiệm cũ. Không tự động xóa theo tên.
- **Khóa** chặn đăng nhập, phiên đang đăng nhập và phân công mới. Giữ nguyên lịch sử; admin cần xem xét đổi người phụ trách nếu tài khoản còn tour đang phụ trách.
- **Mở khóa** cho phép hoạt động trở lại.
- **Xóa** chỉ áp dụng khi không có lịch sử phân công hoặc ghi nhận khách. Tài khoản admin chính được bảo vệ.
- Ô chọn hướng dẫn viên trong trang phân công có tìm kiếm, chỉ liệt kê tài khoản đang hoạt động và hiện cả tên đăng nhập/mã tài khoản.

Database cũ không có cột `role` tiếp tục dùng quy ước hiện tại: tài khoản khác `admin` được quản lý như hướng dẫn viên. Khi có cột `role`, chỉ tài khoản `guide` được liệt kê. Không tự chuyển vai trò hoặc xóa tài khoản cũ.

Đã xác định `GuideAccessTest` trước đây ghi vào database thật, gây tích lũy tài khoản thử. Bộ test này hiện tạo schema SQLite trong bộ nhớ, tách khỏi database đang sử dụng. Tài khoản đăng nhập trong test được tạo trong dữ liệu thử; đã bỏ đăng nhập hướng dẫn viên giả không có bản ghi database để việc khóa/xóa có hiệu lực.

Migration (đã chạy trên local):

```sh
php artisan migrate --path=database/migrations/2026_09_30_000001_add_guide_enabled.php
```

Kiểm tra: `php vendor/bin/phpunit --filter=GuideAccessTest`.

Các file chính: `QuanLyHuongDanVien.php`, `DieuKhienQuanLyHuongDanVien.php`, `resources/views/admin/quan-ly-huong-dan-vien.blade.php`.
