<?php

use App\Http\Controllers\admin\DieuKhienQuanLyQuanTriVien;
use App\Http\Controllers\admin\DieuKhienQuanLyDatTour;
use App\Http\Controllers\admin\DieuKhienQuanLyLienHe;
use App\Http\Controllers\admin\DieuKhienBangDieuKhien;
use App\Http\Controllers\admin\DieuKhienPhanCongHuongDanVien;
use App\Http\Controllers\admin\DieuKhienDangNhapQuanTri;
use App\Http\Controllers\admin\DieuKhienQuanLyTour;
use App\Http\Controllers\admin\DieuKhienQuanLyNguoiDung;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\clients\DieuKhienTrangChu;
use App\Http\Controllers\clients\DieuKhienGioiThieu;
use App\Http\Controllers\clients\DieuKhienTroLyAI;
use App\Http\Controllers\clients\ServicesController;
use App\Http\Controllers\clients\DieuKhienDanhSachTour;
use App\Http\Controllers\clients\DieuKhienChiTietTour;
use App\Http\Controllers\clients\DieuKhienDiemDen;
use App\Http\Controllers\clients\DieuKhienDatTour;
use App\Http\Controllers\clients\DieuKhienHuongDanVien;
use App\Http\Controllers\clients\DieuKhienLienHe;
use App\Http\Controllers\clients\DieuKhienHoSoNguoiDung;
use App\Http\Controllers\clients\DieuKhienDangNhap;
use App\Http\Controllers\clients\DieuKhienDangNhapGoogle;
use App\Http\Controllers\clients\DieuKhienTourCuaToi;
use App\Http\Controllers\clients\DieuKhienThanhToanPayPal;
use App\Http\Controllers\clients\DieuKhienTimKiem;
use App\Http\Controllers\clients\DieuKhienTourDaDat;
use App\Http\Controllers\guide\DieuKhienBangDieuKhienHuongDanVien;
use App\Http\Controllers\guide\DieuKhienDangNhapHuongDanVien;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', [DieuKhienTrangChu::class, 'index'])->name('home');
Route::get('/about', [DieuKhienGioiThieu::class, 'index'])->name('about');
Route::get('/destination', [DieuKhienDiemDen::class, 'index'])->name('destination');
Route::get('/travel-guides', [DieuKhienHuongDanVien::class, 'index'])->name('team');

//Handle Login
Route::get('/login', [DieuKhienDangNhap::class, 'index'])->name('login');
Route::post('/register', [DieuKhienDangNhap::class, 'register'])->name('register');
Route::post('/login', [DieuKhienDangNhap::class, 'login'])->name('user-login');
Route::get('/logout', [DieuKhienDangNhap::class, 'logout'])->name('logout');
Route::get('activate-account/{token}', [DieuKhienDangNhap::class, 'activateAccount'])->name('activate.account');

//Login with google
Route::get('auth/google', [DieuKhienDangNhapGoogle::class, 'redirectToGoogle'])->name('login-google');
Route::get('auth/google/callback', [DieuKhienDangNhapGoogle::class, 'handleGoogleCallback']);

//Handle Get tours , filter Tours
Route::get('/tours', [DieuKhienDanhSachTour::class, 'index'])->name('tours');
Route::get('/filter-tours', [DieuKhienDanhSachTour::class, 'filterTours'])->name('filter-tours');

//Handle user profile
Route::get('/user-profile', [DieuKhienHoSoNguoiDung::class, 'index'])->name('user-profile')->middleware('checkLoginClient');
Route::post('/user-profile', [DieuKhienHoSoNguoiDung::class, 'update'])->name('update-user-profile');
Route::post('/change-password-profile', [DieuKhienHoSoNguoiDung::class, 'changePassword'])->name('change-password');
Route::post('/change-avatar-profile', [DieuKhienHoSoNguoiDung::class, 'changeAvatar'])->name('change-avatar');


//Hanlde checkout
Route::post('/booking/passenger-preview', [DieuKhienDatTour::class, 'previewPassengerFile'])->name('booking.passenger-preview')->middleware('checkLoginClient');
Route::get('/booking/passenger-template', [DieuKhienDatTour::class, 'downloadPassengerTemplate'])->name('booking.passenger-template')->middleware('checkLoginClient');
Route::post('/booking/coupon-preview', [DieuKhienDatTour::class, 'previewCoupon'])->name('booking.coupon-preview')->middleware('checkLoginClient');
Route::post('/booking/coupon-clear', [DieuKhienDatTour::class, 'clearCoupon'])->name('booking.coupon-clear')->middleware('checkLoginClient');
Route::get('/booking/{id?}', [DieuKhienDatTour::class, 'index'])->name('booking-page')->middleware('checkLoginClient');
Route::post('/booking/{id?}', [DieuKhienDatTour::class, 'index'])->name('booking')->middleware('checkLoginClient');
Route::post('/create-booking', [DieuKhienDatTour::class, 'createBooking'])->name('create-booking')->middleware('checkLoginClient');
Route::get('/booking', [DieuKhienDatTour::class, 'handlePaymentMomoCallback'])->name('handlePaymentMomoCallback');

//Payment with paypal
Route::get('create-transaction', [DieuKhienThanhToanPayPal::class, 'createTransaction'])->name('createTransaction');
Route::get('process-transaction', function () {
    return redirect()->route('my-tours')->with('error', 'PayPal đang tạm ngừng. Vui lòng đặt tour và chọn QR hoặc thanh toán tại văn phòng.');
})->name('processTransaction')->middleware('checkLoginClient');
Route::get('success-transaction', function () {
    return redirect()->route('my-tours')->with('error', 'Nếu đã phát sinh giao dịch PayPal, vui lòng liên hệ nhân viên để đối chiếu.');
})->name('successTransaction')->middleware('checkLoginClient');
Route::get('cancel-transaction', [DieuKhienThanhToanPayPal::class, 'cancelTransaction'])->name('cancelTransaction');

//Payment with Momo
Route::post('/create-momo-payment', [DieuKhienDatTour::class, 'createMomoPayment'])->name('createMomoPayment');


//Tour booked
Route::get('/tour-booked', [DieuKhienTourDaDat::class, 'index'])->name('tour-booked')->middleware('checkLoginClient');
Route::post('/cancel-booking', [DieuKhienTourDaDat::class, 'cancelBooking'])->name('cancel-booking')->middleware('checkLoginClient');
Route::post('/report-transfer', [DieuKhienTourDaDat::class, 'reportTransfer'])->name('report-transfer')->middleware('checkLoginClient');
Route::get('/transfer-proof/{id}', [DieuKhienTourDaDat::class, 'transferProof'])->whereNumber('id')->name('transfer-proof');

//My tour
Route::get('/my-tours', [DieuKhienTourCuaToi::class, 'index'])->name('my-tours')->middleware('checkLoginClient');

//get Tour detail and handle submit reviews
Route::get('/tour-detail/{id?}', [DieuKhienChiTietTour::class, 'index'])->name('tour-detail');
Route::post('/checkBooking', [DieuKhienDatTour::class, 'checkBooking'])->name('checkBooking')->middleware('checkLoginClient');
Route::post('/reviews', [DieuKhienChiTietTour::class, 'reviews'])->name('reviews')->middleware('checkLoginClient');

//Contact
Route::get('/contact', [DieuKhienLienHe::class, 'index'])->name('contact');
Route::post('/create-contact', [DieuKhienLienHe::class, 'createContact'])->name('create-contact');
Route::post('/ai-chat', [DieuKhienTroLyAI::class, 'reply'])->name('ai-chat');


//Search 
Route::get('/search', [DieuKhienTimKiem::class, 'index'])->name(name: 'search');
Route::get('/search-voice-text', [DieuKhienTimKiem::class, 'searchTours'])->name('search-voice-text');


// GUIDE
Route::prefix('private-tours')->middleware('checkLoginClient')->group(function () {
    Route::get('/', [\App\Http\Controllers\clients\DieuKhienTourRieng::class, 'index'])->name('private-tours.index');
    Route::post('/', [\App\Http\Controllers\clients\DieuKhienTourRieng::class, 'store'])->name('private-tours.store');
    Route::get('/{id}', [\App\Http\Controllers\clients\DieuKhienTourRieng::class, 'show'])->whereNumber('id')->name('private-tours.show');
    Route::post('/{id}/accept', [\App\Http\Controllers\clients\DieuKhienTourRieng::class, 'accept'])->whereNumber('id')->name('private-tours.accept');
    Route::post('/{id}/cancel', [\App\Http\Controllers\clients\DieuKhienTourRieng::class, 'cancel'])->whereNumber('id')->name('private-tours.cancel');
});

Route::prefix('admin/private-tours')->middleware('admin')->group(function () {
    Route::get('/', [\App\Http\Controllers\admin\DieuKhienTourRieng::class, 'index'])->name('admin.private-tours.index');
    Route::get('/{id}', [\App\Http\Controllers\admin\DieuKhienTourRieng::class, 'show'])->whereNumber('id')->name('admin.private-tours.show');
    Route::post('/{id}/quote', [\App\Http\Controllers\admin\DieuKhienTourRieng::class, 'quote'])->whereNumber('id')->name('admin.private-tours.quote');
    Route::post('/{id}/decline', [\App\Http\Controllers\admin\DieuKhienTourRieng::class, 'decline'])->whereNumber('id')->name('admin.private-tours.decline');
});

Route::prefix('guide')->group(function () {
    Route::get('/login', [DieuKhienDangNhapHuongDanVien::class, 'index'])->name('guide.login');
    Route::post('/login-account', [DieuKhienDangNhapHuongDanVien::class, 'loginGuide'])->name('guide.login-account');
    Route::get('/logout', [DieuKhienDangNhapHuongDanVien::class, 'logout'])->name('guide.logout');
});

Route::prefix('guide')->middleware('guide')->group(function () {
    Route::get('/dashboard', [DieuKhienBangDieuKhienHuongDanVien::class, 'index'])->name('guide.dashboard');
    Route::get('/tour-detail/{tourId}', [DieuKhienBangDieuKhienHuongDanVien::class, 'detail'])->name('guide.tour-detail');
    Route::get('/tour-detail/{tourId}/manifest/{format}', [DieuKhienBangDieuKhienHuongDanVien::class, 'downloadManifest'])->name('guide.manifest');
    Route::post('/tour-status/{tourId}', [DieuKhienBangDieuKhienHuongDanVien::class, 'updateTourStatus'])->name('guide.update-tour-status');
    Route::post('/guest-status/{tourId}', [DieuKhienBangDieuKhienHuongDanVien::class, 'updateGuestStatus'])->name('guide.update-guest-status');
    Route::get('/profile', [DieuKhienBangDieuKhienHuongDanVien::class, 'profile'])->name('guide.profile');
    Route::post('/profile', [DieuKhienBangDieuKhienHuongDanVien::class, 'updateProfile'])->name('guide.update-profile');
});

//ADMIN
// Routes without middleware
Route::prefix('admin')->group(function () {
    Route::get('/login', [DieuKhienDangNhapQuanTri::class, 'index'])->name('admin.login');
    Route::post('/login-account', [DieuKhienDangNhapQuanTri::class, 'loginAdmin'])->name('admin.login-account');
    Route::get('/logout', [DieuKhienDangNhapQuanTri::class, 'logout'])->name('admin.logout');

});

Route::prefix('admin')->middleware('admin')->group(function () {
    Route::get('/coupons', [\App\Http\Controllers\admin\DieuKhienMaGiamGia::class, 'index'])->name('admin.coupons');
    Route::post('/coupons/{id}/settings', [\App\Http\Controllers\admin\DieuKhienMaGiamGia::class, 'settings'])->name('admin.coupons.settings');
    Route::post('/coupons', [\App\Http\Controllers\admin\DieuKhienMaGiamGia::class, 'store'])->name('admin.coupons.store');
    Route::post('/coupons/{id}/status', [\App\Http\Controllers\admin\DieuKhienMaGiamGia::class, 'toggle'])->name('admin.coupons.status');
    Route::get('/dashboard', [DieuKhienBangDieuKhien::class, 'index'])->name('admin.dashboard');

    //Management admin
    Route::get('/admin', [DieuKhienQuanLyQuanTriVien::class, 'index'])->name('admin.admin');
    Route::post('/update-admin', [DieuKhienQuanLyQuanTriVien::class, 'updateAdmin'])->name('admin.update-admin');
    Route::post('/update-avatar', [DieuKhienQuanLyQuanTriVien::class, 'updateAvatar'])->name('admin.update-avatar');

    //Handler management user
    Route::get('/users', [DieuKhienQuanLyNguoiDung::class, 'index'])->name('admin.users');
    Route::post('/active-user', [DieuKhienQuanLyNguoiDung::class, 'activeUser'])->name('admin.active-user');
    Route::post('/status-user', [DieuKhienQuanLyNguoiDung::class, 'changeStatus'])->name('admin.status-user');

    //Management Tours
    Route::get('/tours', [DieuKhienQuanLyTour::class, 'index'])->name('admin.tours');
    Route::get('/tours/{tourId}/transport', [\App\Http\Controllers\admin\DieuKhienPhuongTienTour::class, 'index'])->whereNumber('tourId')->name('admin.transport.index');
    Route::post('/tours/{tourId}/transport', [\App\Http\Controllers\admin\DieuKhienPhuongTienTour::class, 'store'])->whereNumber('tourId')->name('admin.transport.store');
    Route::post('/tours/{tourId}/transport/{optionId}', [\App\Http\Controllers\admin\DieuKhienPhuongTienTour::class, 'update'])->whereNumber('tourId')->whereNumber('optionId')->name('admin.transport.update');

    Route::get('/page-add-tours', [DieuKhienQuanLyTour::class, 'pageAddTours'])->name('admin.page-add-tours');
    Route::post('/add-tours', [DieuKhienQuanLyTour::class, 'addTours'])->name('admin.add-tours');
    Route::post('/add-images-tours', [DieuKhienQuanLyTour::class, 'addImagesTours'])->name('admin.add-images-tours');
    Route::post('/add-timeline', [DieuKhienQuanLyTour::class, 'addTimeline'])->name('admin.add-timeline');

    Route::post('/delete-tour', [DieuKhienQuanLyTour::class, 'deleteTour'])->name('admin.delete-tour');

    Route::get('/tour-edit', [DieuKhienQuanLyTour::class, 'getTourEdit'])->name('admin.tour-edit');
    Route::post('/edit-tour', [DieuKhienQuanLyTour::class, 'updateTour'])->name('admin.edit-tour');
    Route::post('/add-temp-images', [DieuKhienQuanLyTour::class, 'uploadTempImagesTours'])->name('admin.add-temp-images');
    Route::post('/itinerary-image', [DieuKhienQuanLyTour::class, 'uploadItineraryImage'])->name('admin.itinerary-image');

    //Management Booking
    Route::get('/booking', [DieuKhienQuanLyDatTour::class, 'index'])->name('admin.booking');
    Route::post('/confirm-booking', [DieuKhienQuanLyDatTour::class, 'confirmBooking'])->name('admin.confirm-booking');
    Route::get('/booking-detail/{id?}', [DieuKhienQuanLyDatTour::class, 'showDetail'])->name('admin.booking-detail');
    Route::get('/booking-detail/{bookingId}/passengers.xls', [DieuKhienQuanLyDatTour::class, 'downloadPassengerExcel'])->name('admin.booking-passengers-excel');
    Route::post('/finish-booking', [DieuKhienQuanLyDatTour::class, 'finishBooking'])->name('admin.finish-booking');
    Route::post('/received-money', [DieuKhienQuanLyDatTour::class, 'receiviedMoney'])->name('admin.received');
    Route::post('/review-cancellation', [DieuKhienQuanLyDatTour::class, 'reviewCancellation'])->name('admin.review-cancellation');
    Route::post('/resolve-refund', [DieuKhienQuanLyDatTour::class, 'resolveRefund'])->name('admin.resolve-refund');

    // Guide assignment
    Route::get('/guides', [\App\Http\Controllers\admin\DieuKhienQuanLyHuongDanVien::class, 'index'])->name('admin.guides');
    Route::post('/guides/{id}/status', [\App\Http\Controllers\admin\DieuKhienQuanLyHuongDanVien::class, 'status'])->whereNumber('id')->name('admin.guides.status');
    Route::get('/guide-assignment', [DieuKhienPhanCongHuongDanVien::class, 'index'])->name('admin.guide-assignment');
    Route::post('/guide-assignment', [DieuKhienPhanCongHuongDanVien::class, 'assign'])->name('admin.guide-assignment.store');
    Route::post('/guide-assignment/send-manifest', [DieuKhienPhanCongHuongDanVien::class, 'sendManifest'])->name('admin.guide-assignment.send-manifest');
    Route::post('/add-guide', [DieuKhienPhanCongHuongDanVien::class, 'addGuide'])->name('admin.add-guide');
    Route::delete('/guide-assignment/{id}', [DieuKhienPhanCongHuongDanVien::class, 'deleteGuide'])->name('admin.delete-guide');
    Route::put('/guide-assignment/{id}', [DieuKhienPhanCongHuongDanVien::class, 'updateGuide'])->name('admin.update-guide');

    //Send mail pdf
    Route::post('/admin/send-pdf', [DieuKhienQuanLyDatTour::class, 'sendPdf'])->name('admin.send.pdf');

    //Contact management
    Route::get('/contact', [DieuKhienQuanLyLienHe::class, 'index'])->name('admin.contact');
    Route::post('/reply-contact', [DieuKhienQuanLyLienHe::class, 'replyContact'])->name('admin.reply-contact');

});
