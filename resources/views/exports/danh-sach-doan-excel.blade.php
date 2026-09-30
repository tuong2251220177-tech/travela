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
    <h2>Danh sách hành khách - {{ $tour->title }}</h2>
    <p>Điểm đến: {{ $tour->destination }}</p>
    <p>Ngày khởi hành: {{ $tour->departure_date ?? $tour->startDate ?? '' }}</p>
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
