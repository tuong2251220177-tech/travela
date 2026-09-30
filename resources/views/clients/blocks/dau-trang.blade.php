<!DOCTYPE html>
<html lang="vi">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="description" content="">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Title -->
    <title>Travela - {{ $title }}</title>
    <!-- Favicon Icon -->
    <link rel="shortcut icon" href="{{ asset('clients/assets/images/logos/favicon.png') }}" type="image/x-icon">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&amp;display=swap"
        rel="stylesheet">

    <!-- Flaticon -->
    <link rel="stylesheet" href="{{ asset('clients/assets/css/flaticon.min.css') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('clients/assets/css/fontawesome-5.14.0.min.css') }}">
    <!-- Bootstrap -->
    <link rel="stylesheet" href="{{ asset('clients/assets/css/bootstrap.min.css') }}">
    <!-- Magnific Popup -->
    <link rel="stylesheet" href="{{ asset('clients/assets/css/magnific-popup.min.css') }}">
    <!-- Nice Select -->
    <link rel="stylesheet" href="{{ asset('clients/assets/css/nice-select.min.css') }}">
    <!-- jQuery UI -->
    <link rel="stylesheet" href="{{ asset('clients/assets/css/jquery-ui.min.css') }}">
    <!-- Animate -->
    <link rel="stylesheet" href="{{ asset('clients/assets/css/aos.css') }}">
    <!-- Slick -->
    <link rel="stylesheet" href="{{ asset('clients/assets/css/slick.min.css') }}">
    <!-- Main Style -->
    <link rel="stylesheet" href="{{ asset('clients/assets/css/style.css') }}">

    {{-- boxicons --}}
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    {{-- Login  --}}
    <!-- Font Icon -->
    <link rel="stylesheet"
        href="{{ asset('clients/assets/css/css-login/fonts/material-icon/css/material-design-iconic-font.min.css') }}">
    <!-- Main css -->
    <link rel="stylesheet" href="{{ asset('clients/assets/css/css-login/style.css') }}">
    {{-- custom css by Dien --}}
    <link rel="stylesheet" href="{{ asset('clients/assets/css/custom-css.css') }}" />

    {{-- User Profile  --}}
    <link rel="stylesheet" href="{{ asset('clients/assets/css/user-profile.css') }}" />

    <!-- Import CSS for Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />


    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('clients/assets/css/home-stitch.css') }}">
    <link rel="stylesheet" href="{{ asset('clients/assets/css/site-stitch.css') }}">
<link rel="stylesheet" href="{{ asset('clients/assets/css/readability.css?v=20260923') }}">
<link rel="stylesheet" href="{{ asset('clients/assets/css/giao-dien-moi.css?v=20260925') }}">
</head>
<body class="client-theme">
<a class="skip-link" href="#main">Đến nội dung chính</a>
<div class="page-wrapper">
@include('clients.blocks.dieu-huong-giao-dien')
<main id="main">
