# Chat AI trên máy tính

Chat dùng Ollama trên máy chạy Laravel, với mô hình `qwen2.5:3b-instruct`.
Không cần API key trả phí. Máy chạy website cần mở Ollama.

## Cấu hình `.env`

```dotenv
OPENAI_API_ENDPOINT=http://127.0.0.1:11434/v1/chat/completions
OPENAI_MODEL=qwen2.5:3b-instruct
```

Sau khi đổi cấu hình, chạy `php artisan config:clear`.

## Chạy lại sau khi khởi động máy

Mở Ollama từ Start menu. Nếu dùng dòng lệnh, chạy `ollama serve`
và giữ tiến trình đó chạy. Không mở thêm nếu Ollama đã chạy nền.

Kiểm tra mô hình bằng `ollama list`. Khi chuyển sang máy mới, cài
[Ollama](https://docs.ollama.com/windows) rồi tải mô hình:

```powershell
ollama pull qwen2.5:3b-instruct
```

## Kiểm tra kết nối thật

Từ thư mục dự án:

```powershell
php storage/app/check-ai.php
php storage/app/check-ai.php "Tôi thanh toán bằng QR như thế nào?"
```

`mode: ai` là câu trả lời từ mô hình thật. `mode: fallback` là hỗ trợ tự động
khi AI không kết nối được; lệnh trả mã lỗi 1 để dễ phát hiện.
`mode: guide` là thông tin tour lấy trực tiếp từ dữ liệu hoặc hướng dẫn thanh toán
có sẵn của website; không gọi mô hình để tránh viết lại sai giá và quy trình.
Lệnh kiểm tra cũng trả mã 1 cho chế độ này vì nó không kiểm tra kết nối AI.
Script đi qua controller thật, không giả lập dịch vụ AI và không tạo đơn hàng.

## Dữ liệu và giới hạn

Chat cung cấp dữ liệu tour đang mở trong cơ sở dữ liệu cùng lịch sử hội thoại
ngắn cho mô hình. Khách cần kiểm tra giá và lịch trên trang chi tiết trước khi đặt.
Chat không xác nhận thanh toán; nhân viên kiểm tra chuyển khoản.
Lần hỏi đầu có thể chậm hơn vì mô hình phải nạp vào bộ nhớ.
Khi đưa website lên máy chủ khác, cần chạy Ollama trên máy chủ đó hoặc đổi
endpoint/model/key sang dịch vụ AI tương thích. `127.0.0.1` luôn là máy chạy Laravel.
