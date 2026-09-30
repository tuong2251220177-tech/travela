<!DOCTYPE html>
<html lang="vi">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Đăng nhập</title>

    <!-- Bootstrap -->
    <link href="{{ asset('admin/vendors/bootstrap/dist/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="{{ asset('admin/vendors/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
    <!-- NProgress -->
    <link href="{{ asset('admin/vendors/nprogress/nprogress.css') }}" rel="stylesheet">
    <!-- Animate.css -->
    <link href="{{ asset('admin/vendors/animate.css/animate.min.css') }}" rel="stylesheet">

    <!-- Custom Theme Style -->
    <link href="{{ asset('admin/build/css/custom.min.css') }}" rel="stylesheet">
        <!-- Import CSS for Toastr -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    {{-- Custom css by DevDien  --}}
    <link href="{{ asset('admin/assets/css/custom-css.css') }}" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('clients/assets/css/workspace-stitch.css') }}">
<link rel="stylesheet" href="{{ asset('clients/assets/css/readability.css?v=20260923') }}">
<link rel="stylesheet" href="{{ asset('clients/assets/css/giao-dien-moi.css?v=20260930-login-center') }}">
</head>

<body class="login admin-theme staff-login">
    <div>

        <div class="login_wrapper">
            <div class="animate form login_form">
                <section class="login_content">
                    <form action="{{ route('admin.login-account') }}" method="POST" id="formLoginAdmin">
                        <a class="staff-brand" href="{{ route('home') }}">✦ Travela<span>.</span></a><p class="staff-kicker">KHÔNG GIAN QUẢN TRỊ</p><h1>Chào mừng trở lại</h1><p class="staff-subtitle">Quản lý hành trình và đồng hành cùng khách hàng.</p>
                        @csrf
                        <div>
                            <input type="text" class="form-control" name="username" id="username"
                                placeholder="Tài khoản" aria-label="Tên đăng nhập" autocomplete="username" required />
                        </div>
                        <div>
                            <input type="password" class="form-control" name="password" id="password"
                                placeholder="Mật khẩu" aria-label="Mật khẩu" autocomplete="current-password" required />
                        </div>
                        <div>
                            <button class="btn btn-default submit" type="submit">Đăng nhập</button>
                        </div>
                        <div class="clearfix"></div>
                    </form>

                </section>
            </div>
        </div>
    </div>
    @include('admin.blocks.chan-trang')
