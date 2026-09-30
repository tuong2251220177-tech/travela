# Luồng đặt tour và đối soát

## Khách hàng

1. Đăng nhập, chọn tour đang mở đăng ký và chưa khởi hành. Mỗi đoàn có sức chứa riêng; đoàn đầy thì xếp sang đoàn tiếp theo.
2. Nhập hành khách, chọn QR hoặc thanh toán tại văn phòng, xác nhận điều khoản.
3. Xác nhận đặt tour. Máy chủ xếp cả đơn vào một đoàn phù hợp (hoặc mở đoàn mới), tính giá và mã giảm giá; gửi lại cùng biểu mẫu sẽ trở về cùng đơn.
4. Với QR, mở chi tiết đơn để quét đúng số tiền và nội dung `TRAVELA B{mã đơn}`.
5. Sau khi chuyển, bấm **Tôi đã chuyển khoản**. Đây là thông báo cho nhân viên, chưa phải xác nhận đã nhận tiền.
6. Theo dõi đơn tại **Lịch sử đặt tour → Chi tiết**. Nút cập nhật trạng thái tải lại thông tin mới nhất.
7. Yêu cầu hủy trực tuyến trước ngày đi ít nhất 7 ngày. Gần ngày đi cần liên hệ nhân viên. Hủy chỉ có hiệu lực sau khi duyệt.

## Nhân viên

- **Quản lý đặt tour → Chi tiết**: xem tổng tiền và thông báo chuyển khoản.
- Đối chiếu tiền thực tế trước khi bấm **Xác nhận đã nhận đủ tiền**, sau đó **Xác nhận** đơn.
- Duyệt hoặc từ chối yêu cầu hủy, nhập phản hồi cho khách. Duyệt hủy trả chỗ đúng một lần.
- Đơn đã thanh toán hoặc đã báo chuyển khoản khi hủy được gắn cờ cần đối soát/hoàn tiền.
- Thực hiện đối soát/hoàn tiền bên ngoài website, sau đó ghi kết quả bằng **Ghi nhận đã hoàn tất đối soát**. Nút này không chuyển tiền.
- Chỉ đánh dấu hoàn thành khi tour đã kết thúc, đơn đã xác nhận và đã thanh toán.

## Hết hạn

Lệnh `php artisan bookings:expire-deposits` được khai báo chạy mỗi phút trong Laravel Scheduler.
Khi chạy website trên máy tính, mở một terminal riêng và chạy `php artisan schedule:work`.
Không chạy scheduler thì tác vụ vẫn được kiểm tra khi admin mở danh sách đơn, nhưng không tự chạy theo phút.
Đơn chưa thanh toán quá hạn được hủy và trả chỗ đúng một lần. Đơn đã báo chuyển khoản được giữ để nhân viên đối chiếu.

## Cơ sở dữ liệu và kiểm tra

Migration mới chỉ thêm các cột trạng thái và mã chống gửi trùng:
`php artisan migrate --path=database/migrations/2026_09_16_000001_add_booking_workflow_fields.php`

`php artisan test --filter=BookingWorkflowTest` dùng SQLite trong bộ nhớ, không tạo đơn hay chuyển tiền trên dữ liệu thật.
Kiểm thử bao gồm quyền sở hữu, tính tiền, hoàn tác khi lưu lỗi, gửi trùng, hết chỗ, hết hạn, duyệt hủy và trạng thái thanh toán.
Khóa hàng và giao dịch trong luồng giữ chỗ yêu cầu bảng MySQL dùng InnoDB.

## Phạm vi thanh toán

QR và thanh toán tại văn phòng được đối soát thủ công. PayPal/MoMo tạm ngừng trong luồng đặt tour
vì tích hợp cũ chưa xác minh giao dịch đầy đủ ở máy chủ. Không dùng mã giao dịch do trình duyệt gửi lên
để tự đánh dấu đã thanh toán. Không có chuyển tiền/hoàn tiền tự động trong bản cập nhật này.
