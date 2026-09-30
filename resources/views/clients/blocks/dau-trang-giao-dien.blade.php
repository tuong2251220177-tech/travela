<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — Travela</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('clients/assets/css/fontawesome-5.14.0.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="{{ asset('clients/assets/css/home-stitch.css') }}">
    <link rel="stylesheet" href="{{ asset('clients/assets/css/tour-stitch.css') }}">
    @if(($pageClass ?? '') === 'tour-detail-page pastel-detail')
    <link rel="stylesheet" href="{{ asset('clients/assets/css/detail-pastel.css?v=20260929-private-tour') }}">
    @endif
    @if(($pageClass ?? '') === 'booking-page pastel-booking')
    <link rel="stylesheet" href="{{ asset('clients/assets/css/booking-pastel.css?v=20260930-payment-cards') }}">
    @endif
    @if(($pageClass ?? '') === 'order-page pastel-order')
    <link rel="stylesheet" href="{{ asset('clients/assets/css/order-pastel.css?v=20260930-trip-card') }}">
    @endif
<link rel="stylesheet" href="{{ asset('clients/assets/css/readability.css?v=20260923') }}">
<link rel="stylesheet" href="{{ asset('clients/assets/css/giao-dien-moi.css?v=20260925') }}">
</head>
<body class="{{ $pageClass ?? 'tour-detail-page' }}">
<a class="skip-link" href="#main">Đến nội dung chính</a>
@include('clients.blocks.dieu-huong-giao-dien')
@if(session('error'))<div class="shell"><p class="page-notice" role="alert">{{ session('error') }}</p></div>@endif
@if($errors->any())<div class="shell"><div class="page-notice" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div></div>@endif
