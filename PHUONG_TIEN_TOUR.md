# Chọn phương tiện khi đặt tour

- Trang đặt tour có mục **Phương tiện di chuyển**, áp dụng cho toàn bộ hành khách trong đơn.
- Phương tiện tiêu chuẩn nằm trong giá tour. Các tour hiện có được thêm lựa chọn **Xe limousine: 200.000đ/khách** khi chạy migration. Admin cần điều chỉnh giá và tắt lựa chọn không phù hợp với từng tour.
- Trong **Admin → Quản lý tour → Phương tiện**, thêm loại phương tiện, chỉnh phụ thu, chọn tính theo khách hoặc theo đơn, bật/tắt lựa chọn. Với tour mới, admin thêm các lựa chọn nâng cấp tại đây.
- Mặc định nên dùng phụ thu theo khách cho tour ghép. Phụ thu theo khách tính cả người lớn và trẻ em.
- Tổng tiền = giá tour của người lớn và trẻ em − giảm giá + phụ thu phương tiện. Mã giảm giá và mức đơn tối thiểu chỉ tính trên phần giá tour. Cọc bằng 20% tổng cuối cùng.
- Khi đã áp dụng mã, hủy mã trước khi đổi phương tiện hoặc số khách.
- Máy chủ tự đọc giá phương tiện theo tour; không tin giá gửi từ trình duyệt. Phương tiện không tồn tại, thuộc tour khác hoặc bị tắt sẽ không đặt được.
- Loại phương tiện và tổng phụ thu được lưu vào đơn. Thay đổi cấu hình sau này không thay đổi giá đơn đã đặt.
- Mỗi đoàn chỉ nhận cùng lựa chọn phương tiện; vẫn áp dụng số khách tối thiểu/tối đa của tour. Chưa quản lý số xe thực tế hoặc số ghế riêng của từng loại xe; admin chỉ mở phương tiện phù hợp với quy mô đoàn.
- Tour riêng vẫn theo báo giá đã chốt, không tự cộng phụ thu.

Migration (đã chạy trên database local):

```sh
php artisan migrate --path=database/migrations/2026_09_29_000002_add_booking_transport_options.php
```

Không chạy toàn bộ migration cũ trên database đã nhập sẵn. Migration mới tương thích với cột `transportFee` có sẵn trong database nhập từ trước.

File chính:

- `app/Services/PhuongTienTour.php`: xác thực lựa chọn và tính phụ thu.
- `app/Services/QuyTrinhDatTour.php`: lưu đơn, tính tổng/cọc.
- `app/Services/NhomTour.php`: xếp đoàn theo lựa chọn phương tiện.
- `app/Http/Controllers/admin/DieuKhienPhuongTienTour.php`: cấu hình của admin.
- `resources/views/admin/phuong-tien-tour.blade.php`: giao diện cấu hình.
- `resources/views/clients/dat-tour.blade.php`: lựa chọn của khách.

Kiểm tra:

```sh
php vendor/bin/phpunit --filter="BookingWorkflowTest|PrivateTourTest|PassengerSpreadsheetTest"
node tests/coupon-selection-regression.cjs
```

Đã thử tạo đơn với schema MySQL local trong giao dịch hoàn tác, render trang đặt tour, cấu hình admin và chi tiết đơn cho cả khách/admin.
