# Yêu cầu tour riêng

## Khách hàng

1. Đăng nhập, vào **Yêu cầu tour riêng** trong menu tài khoản hoặc bấm **Yêu cầu đi riêng cho nhóm / gia đình** tại trang chi tiết tour.
2. Chọn lịch trình tham khảo, số người lớn/trẻ em, ngày mong muốn, thông tin liên hệ và yêu cầu riêng. Gửi yêu cầu chưa tạo đơn, chưa thu tiền.
3. Theo dõi yêu cầu tại `/private-tours`. Khi admin báo giá, xem tổng giá cả nhóm, lịch đi chính thức, hạn chấp nhận, dịch vụ và điều kiện.
4. Nhập tên tất cả hành khách, chọn QR hoặc thanh toán tại văn phòng, đồng ý báo giá để tạo đơn. Thông tin liên hệ dùng chung của người đại diện.
5. Đặt cọc 20% trong 48 giờ hoặc trước giờ khởi hành nếu sớm hơn. Thu tiền, hủy, đối soát hoàn tiền sử dụng quy trình đơn hiện có.

Khách có thể rút yêu cầu khi chưa chấp nhận. Sau khi có đơn, gửi yêu cầu hủy tại chi tiết đơn. Hủy trực tuyến cần trước ngày đi tối thiểu 7 ngày; gần ngày đi liên hệ nhân viên. Báo giá hết hạn cần admin báo lại.

## Admin

- Vào **Yêu cầu tour riêng**, lọc trạng thái và mở yêu cầu.
- Kiểm tra xe/hướng dẫn viên, chốt ngày bắt đầu/kết thúc, tổng giá, hạn chấp nhận. Ghi rõ phương án xe (nhóm lớn có thể cần nhiều xe), dịch vụ bao gồm/chưa bao gồm và điều kiện hủy/hoàn tiền.
- Có thể cập nhật báo giá khi khách chưa chấp nhận, hoặc từ chối kèm lý do. Khách phải tải lại báo giá nếu phiên bản đã thay đổi.
- Sau khi khách đồng ý, mở đơn để thu cọc, xác nhận. Phân công hướng dẫn viên cho lịch trình có nhãn **Riêng #mã yêu cầu**, đoàn 1.
- Lịch trình đã chốt không được sửa hoặc xóa trong màn hình quản lý tour ghép; không sửa báo giá sau khi đã tạo đơn.

## Cách lưu và bảo vệ dữ liệu

- Yêu cầu/báo giá nằm trong `tbl_private_tour_requests`; người dùng chỉ truy cập yêu cầu của mình.
- Khi chấp nhận, tạo một bản lịch trình độc lập trong `tbl_tours` với `privateRequestId`, giữ ảnh và lịch trình tại thời điểm báo giá; ngày đi và tổng giá theo báo giá. Không đổi lịch/giá tour ghép gốc.
- Lịch trình riêng không công khai (`availability = 0` sau khi tạo đơn), không nhận đặt qua luồng tour ghép, kể cả nếu bị mở lại. Chi tiết tour công khai từ chối truy cập lịch trình riêng.
- Mỗi yêu cầu chỉ tạo một đơn, nằm trong một đoàn riêng. Không ghép khách khác. Số khách tối thiểu/tối đa bằng quy mô nhóm đã báo giá; xác nhận đơn sau đặt cọc để đủ điều kiện khởi hành.
- Tổng tiền lấy từ báo giá máy chủ, không lấy từ trình duyệt, không áp dụng mã giảm giá tour ghép. Giá bình quân trên bản lịch trình riêng chỉ để tham khảo; tổng giá chốt là giá trên đơn.
- Tạo lịch trình, hành khách, đơn, thanh toán và cập nhật yêu cầu trong cùng giao dịch; lỗi sẽ hoàn tác. Khóa yêu cầu để chống chấp nhận trùng và xung đột với cập nhật báo giá.
- Giới hạn nhập: 1–100 người lớn, 0–100 trẻ em. Đây là giới hạn biểu mẫu, không phải số chỗ một xe; admin xác nhận phương án vận chuyển trước khi báo giá.

## Cài đặt và kiểm tra

Migration riêng (đã chạy trên cơ sở dữ liệu local):

```sh
php artisan migrate --path=database/migrations/2026_09_29_000001_create_private_tour_requests.php
```

Không chạy toàn bộ các migration cũ còn pending trên cơ sở dữ liệu đã được nhập sẵn.

```sh
php artisan test --filter="PrivateTourTest|BookingWorkflowTest|TourDurationTest|TourPriceValidationTest"
```

47 kiểm thử, 235 assertions đạt. Đã chạy thử tạo đơn với schema MySQL thực trong giao dịch hoàn tác, không giữ lại đơn mẫu. Đã kiểm tra 3 màn hình khách/admin tại 390px và 1440px.

Phần mở rộng `GuideAccessTest` vẫn có lỗi có sẵn `test_admin_can_update_guide_account` do trùng tên tài khoản. Báo giá hiện hiển thị trên website; chưa có email tự động, giữ xe tự động hoặc thanh toán/hoàn tiền tự động.

## File chính

- `app/Services/TourRieng.php`: trạng thái yêu cầu, báo giá, chấp nhận và tạo đơn riêng.
- `app/Services/QuyTrinhDatTour.php`: dùng tổng giá đã chốt và ngăn đặt ghép vào lịch trình riêng.
- `app/Http/Controllers/clients/DieuKhienTourRieng.php`: chức năng khách hàng.
- `app/Http/Controllers/admin/DieuKhienTourRieng.php`: chức năng admin.
- `resources/views/clients/tour-rieng.blade.php`, `chi-tiet-tour-rieng.blade.php`: giao diện khách.
- `resources/views/admin/tour-rieng.blade.php`, `chi-tiet-tour-rieng.blade.php`: giao diện admin.
- `public/clients/assets/css/tour-rieng.css`: giao diện tour riêng.
