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
    <h2>Thông tin hành khách Travela</h2>
    <p>Không đổi tên cột. Vui lòng nhập đủ thông tin ở các dòng bên dưới.</p>
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
            @for ($index = 0; $index < $adultCount + $childCount; $index++)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $index < $adultCount ? 'Người lớn' : 'Trẻ em' }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
        </tbody>
    </table>
</body>
</html>