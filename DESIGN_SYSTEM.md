# Giao diện Travela

## Cập nhật ngày 25/09/2026

- Nền trắng và xám sáng, tím `#6545d6` làm điểm nhấn; thanh bên quản trị xanh đậm `#20253b`.
- Trang chủ dùng tiêu đề lệch trái trên ảnh Hạ Long, thanh tìm kiếm rõ ràng, thẻ tour và điểm đến thống nhất. Giao diện khách hàng và quản trị có chữ lớn hơn, nút bo góc vừa phải và khoảng cách thoáng hơn.
- Dashboard dùng bốn ô thống kê từ dữ liệu hiện có và lối tắt thêm tour, quản lý đơn, phân công hướng dẫn viên. Khắc phục phần điều hướng nổi làm tiêu đề dashboard lệch ngang.
- CSS mới: `public/clients/assets/css/giao-dien-moi.css`, nạp cuối ở trang chủ, hai đầu trang khách hàng, đầu trang quản trị và trang đăng nhập quản trị. Chỉnh màu sắc và khoảng cách của đợt cập nhật này tại file đó. Các file export/email không nạp stylesheet này.
- Kiểm tra: biên dịch Blade, phân tích cú pháp CSS, HTTP 200 cho trang chủ, tour, điểm đến, giới thiệu, liên hệ, đăng nhập khách và đăng nhập quản trị. Render dashboard bằng controller với dữ liệu hiện có; bản xem thử được chuyển trực tiếp vào trình duyệt kiểm tra, không thêm route truy cập quản trị.
- Đã xem ảnh Chrome tại 390px, 768px và 1440px cho trang chủ; 390px và 1440px cho dashboard; 390px cho danh sách tour. Đã kiểm tra menu di động, đóng menu bằng Escape, lọc tour theo vùng và thu gọn menu quản trị. Bảng rộng trong dashboard cuộn trong vùng bảng, không kéo rộng toàn trang.
- Ảnh xem trước: `storage/app/trang-chu-desktop.png`, `storage/app/trang-chu-mobile.png`, `storage/app/quan-tri-desktop.png`, `storage/app/quan-tri-mobile.png`.
- Bản sao các view trước đợt chỉnh: `storage/app/design-backup/20260925-refinement/`.

Các phần bên dưới ghi lại hệ thống giao diện trước đợt cập nhật này.

## Phong cách

- Màu chính: tím `#613de0`; nền khách hàng `#faf8ff`.
- Font chữ: Plus Jakarta Sans, dự phòng sans-serif.
- Thẻ nội dung bo góc, đường viền tím nhạt, nút chính hình viên thuốc.
- Admin dùng thanh điều hướng tím đậm; hướng dẫn viên dùng thanh điều hướng sáng.
- Các bảng có trạng thái được phân biệt bằng cả chữ và màu.

## Các tệp dùng chung

| Phần giao diện | Tệp |
| --- | --- |
| Trang chủ, thành phần nền tảng | `public/clients/assets/css/home-stitch.css` |
| Trang khách hàng còn lại | `public/clients/assets/css/site-stitch.css` |
| Chi tiết tour, đặt tour | `public/clients/assets/css/tour-stitch.css` |
| Admin và hướng dẫn viên | `public/clients/assets/css/workspace-stitch.css` |
| Menu khách hàng | `resources/views/clients/blocks/dieu-huong-giao-dien.blade.php` |
| Footer khách hàng | `resources/views/clients/blocks/chan-trang-giao-dien.blade.php` |
| Tiêu đề trang khách hàng | `resources/views/clients/blocks/gioi-thieu-trang.blade.php` |
| Thẻ tour dùng cho danh sách và tìm kiếm | `resources/views/clients/partials/the-tour-trang-chu.blade.php` |
| Tiêu đề trang admin | `resources/views/admin/blocks/tieu-de-khu-vuc.blade.php` |
| Điều hướng hướng dẫn viên | `resources/views/guide/blocks/dieu-huong.blade.php` |

Các trang giữ các trường biểu mẫu và điểm gắn JavaScript phục vụ xử lý nghiệp vụ. Phân trang và lọc tour trả về cùng thẻ tour dùng trên trang chủ. Thống kê số người dùng ở dashboard lấy dữ liệu thật.

## Kiểm tra

Đã kiểm tra biên dịch Blade, cú pháp CSS/JavaScript/PHP, tải 12 trang công khai, render 17 trang nội bộ/trạng thái lỗi, phản hồi lọc và phân trang AJAX. Ba test về truy cập hướng dẫn viên và kiểm tra số tiền MoMo đã qua.

Chưa kiểm tra trực quan bằng trình duyệt hoặc thực hiện giao dịch thanh toán. Khi xem giao diện, kiểm tra thêm ở chiều rộng 390px, 768px và 1440px, bao gồm menu, bảng nhiều cột, biểu mẫu dài và modal quản trị.

## Bản sao trước thay đổi

Các view trước đợt đồng bộ toàn bộ website nằm tại `storage/app/design-backup/before-full-site/`. Những bản sao trang chủ, chi tiết tour và đặt tour trước đó cũng nằm trong `storage/app/design-backup/`.
