<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách hành khách</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #777; padding: 5px; }
        th { background: #e8f1e8; }
    </style>
</head>
<body>
    <h2>Danh sách hành khách</h2>
    <p><strong>Tour:</strong> {{ $tour->title }}</p>
    <p><strong>Điểm đến:</strong> {{ $tour->destination }}</p>
    <p><strong>Ngày khởi hành:</strong> {{ $tour->departure_date ?? $tour->startDate ?? '' }}</p>
    <table>
        <thead>
            <tr>
                <th>Mã booking</th>
                <th>STT</th>
                <th>Loại khách</th>
                <th>Họ và tên</th>
                <th>Email</th>
                <th>Số điện thoại</th>
                <th>Địa chỉ</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row->bookingId }}</td>
                    <td>{{ $row->passengerNumber }}</td>
                    <td>{{ $row->passengerType === 'child' ? 'Trẻ em' : 'Người lớn' }}</td>
                    <td>{{ $row->fullName }}</td>
                    <td>{{ $row->email }}</td>
                    <td>{{ $row->phoneNumber }}</td>
                    <td>{{ $row->address }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
