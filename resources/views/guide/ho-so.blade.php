<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông tin cá nhân hướng dẫn viên</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('clients/assets/css/workspace-stitch.css') }}">
<link rel="stylesheet" href="{{ asset('clients/assets/css/readability.css?v=20260923') }}">
</head>
<body class="bg-light guide-theme">
@include('guide.blocks.dieu-huong')
    <div class="container py-5">
        <div class="card shadow-sm mx-auto" style="max-width: 700px;">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Thông tin cá nhân</h5>
                <a href="{{ route('guide.dashboard') }}" class="btn btn-light btn-sm">Dashboard</a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('guide.update-profile') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Họ và tên</label>
                            <input type="text" name="full_name" class="form-control" value="{{ $guide->full_name ?? $guideName }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Tên đăng nhập</label>
                            <input type="text" class="form-control" value="{{ $guide->username ?? $guideName }}" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="{{ $guide->email ?? '' }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Số điện thoại</label>
                            <input type="text" name="phone" class="form-control" value="{{ $guide->phone ?? '' }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Địa chỉ</label>
                        <textarea name="address" class="form-control" rows="3">{{ $guide->address ?? '' }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</body>
</html>
