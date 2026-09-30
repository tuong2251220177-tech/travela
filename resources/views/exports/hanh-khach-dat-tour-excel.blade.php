<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 6px; }
        th { background: #e8f1e8; }
    </style>
</head>
<body>
    <h2>Thông tin hành khách - {{ $booking->title }}</h2>
    <p>Mã booking: {{ $booking->bookingId }}</p>
    @if ($passengers->count() < (int) $booking->numAdults + (int) $booking->numChildren)
        <p><strong>Lưu ý:</strong> Booking này được tạo trước khi hệ thống lưu thông tin từng hành khách. File chỉ có thông tin người liên hệ chính.</p>
    @endif
    <table>
        <thead>
            <tr>
                <th>STT</th>
                <th>Loại khách</th>
                <th>Họ và tên</th>
                <th>Email</th>
                <th>Số điện thoại</th>
                <th>Địa chỉ</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($passengers as $passenger)
                <tr>
                    <td>{{ $passenger->passengerNumber }}</td>
                    <td>{{ $passenger->passengerType === 'child' ? 'Trẻ em' : 'Người lớn' }}</td>
                    <td>{{ $passenger->fullName }}</td>
                    <td>{{ $passenger->email }}</td>
                    <td>{{ $passenger->phoneNumber }}</td>
                    <td>{{ $passenger->address }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
