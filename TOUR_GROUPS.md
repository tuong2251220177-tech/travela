# Quản lý đoàn

## Quy tắc đang áp dụng

- Tour là lịch trình/sản phẩm du lịch; đoàn là từng nhóm khách/xe của cùng tour và ngày khởi hành. Mở đoàn 2 không nhân bản tour hay tự tạo ngày khởi hành mới.
- Không dùng lượng tồn kho để khóa, ẩn hoặc gắn nhãn hết chỗ. Tour vẫn có thể ngừng nhận đăng ký khi admin ẩn tour hoặc đã đến giờ khởi hành.
- Ví dụ Min = 5, Max = 30: các đơn 28, 5, 25, 7 khách được xếp lần lượt vào đoàn 1, 2, 2, 3 (tổng 65 khách). Cả nhóm trong một đơn luôn đi chung đoàn.
- Đơn lớn hơn Max cần chia thành các đơn riêng hoặc liên hệ nhân viên; không tự tách hành khách trong cùng một đơn. Đây là giới hạn sức chứa một đoàn, không phải giới hạn tổng khách của tour.
- Dashboard hiển thị tổng khách đăng ký còn hiệu lực và số đoàn, không hiển thị số chỗ tồn kho. Thẻ tour không đọc `quantity` để báo hết chỗ.
- Đã kiểm tra 38 bài trong `BookingWorkflowTest`, gồm mở nhiều đoàn khi `quantity = 0`, đoàn chưa đủ Min, chống gửi trùng và hiển thị thẻ tour.

- Admin nhập Min và Max trong thêm/sửa tour. Min là số khách đã xác nhận cần có để khởi hành; Max là sức chứa một đoàn.
- Tour không còn trừ cột quantity khi đặt/hủy. Cột cũ chỉ được giữ để tương thích dữ liệu.
- Mỗi đơn thuộc một groupNumber của tour, cùng lịch khởi hành. Giữ nguyên cả đơn; tìm đoàn đang còn đủ chỗ, nếu không có thì mở số đoàn tiếp theo. Đơn vượt Max cần chia thành nhiều đơn hoặc liên hệ nhân viên.
- Đơn chờ thanh toán vẫn chiếm chỗ trong đoàn; đơn hủy/hết hạn không chiếm chỗ. Không tự chuyển khách đã xếp đoàn.
- Đoàn chưa đủ Min vẫn nhận khách nhưng chưa được chuyển sang trạng thái đang diễn ra. Xác nhận đơn và đủ điều kiện khởi hành là hai việc khác nhau.
- Phân công hướng dẫn viên và xuất danh sách khách có số đoàn. Đoàn đủ khách không đồng nghĩa đã bố trí xe/hướng dẫn viên; admin vẫn phải phân công.
- Migration giữ đơn cũ trong đoàn 1; Max ban đầu phục hồi từ số chỗ cũ cộng khách còn hiệu lực (ít nhất 5). Admin cần rà soát lại theo xe thực tế. Không giảm Max thấp hơn số khách đã xếp trong một đoàn.
- Ghi nhận tiền xóa cờ báo chuyển khoản đang chờ, không xóa ảnh/phiếu thu. Gửi biên lai mới không làm mất trạng thái đơn đã xác nhận.

Kiểm tra: `php artisan test --filter="BookingWorkflowTest|TourDurationTest|TourPriceValidationTest"`.
