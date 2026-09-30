<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            max-width: 420px;
            width: 100%;
            border-radius: 16px;
            box-shadow: 0 16px 40px rgba(0,0,0,0.25);
            background: #fff;
        }
    </style>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('clients/assets/css/workspace-stitch.css') }}">
<link rel="stylesheet" href="{{ asset('clients/assets/css/readability.css?v=20260923') }}">
</head>
<body class="guide-theme staff-login">
    <div class="login-card p-4">
        <a class="staff-brand" href="{{ route('home') }}">✦ Travela<span>.</span></a><p class="staff-kicker">KHÔNG GIAN HƯỚNG DẪN VIÊN</p><h3 class="text-center mb-3">Sẵn sàng cho hành trình mới?</h3>
        <p class="text-muted text-center mb-4">Đăng nhập để xem lịch dẫn tour và đồng hành cùng đoàn.</p>
        <form action="{{ route('guide.login-account') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="username">Tên đăng nhập</label>
                <input type="text" name="username" id="username" class="form-control" required placeholder="Nhập tài khoản hướng dẫn viên">
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" name="password" id="password" class="form-control" required placeholder="Nhập mật khẩu">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Đăng nhập</button>
        </form>
        <div class="mt-3 text-center small"><a href="{{ route('home') }}">← Về trang chủ</a></div>
    </div>

    <script src="{{ asset('clients/assets/js/jquery-3.6.0.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</body>
</html>
