<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$db = app('db');

echo "=== 1. KIỂM TRA BẢNG TBL_ADMIN (Guides) ===\n";
$admins = $db->table('tbl_admin')->select('adminId', 'username', 'email')->get();
echo "Số admin: " . count($admins) . "\n";
if (count($admins) > 0) {
    echo "Sample: " . json_encode($admins[0]) . "\n";
}

echo "\n=== 2. KIỂM TRA BẢNG TBL_TOURS ===\n";
$tours = $db->table('tbl_tours')->select('tourId', 'title', 'destination')->limit(3)->get();
echo "Số tour: " . count($tours) . "\n";
if (count($tours) > 0) {
    echo "Sample: " . json_encode($tours[0]) . "\n";
}

echo "\n=== 3. KIỂM TRA BẢNG TBL_GUIDE_ASSIGNMENTS ===\n";
$assignments = $db->table('tbl_guide_assignments')->get();
echo "Số phân công: " . count($assignments) . "\n";
if (count($assignments) > 0) {
    echo "Sample: " . json_encode($assignments[0]) . "\n";
}

echo "\n=== 4. KIỂM TRA BẢNG TBL_GUIDE_BOOKING_STATUS ===\n";
$bookingStatus = $db->table('tbl_guide_booking_status')->get();
echo "Số bản ghi booking status: " . count($bookingStatus) . "\n";

echo "\n=== 5. KIỂM TRA BẢNG TBL_BOOKING ===\n";
$bookings = $db->table('tbl_booking')->select('bookingId', 'fullName', 'tourId')->limit(3)->get();
echo "Số booking: " . count($bookings) . "\n";
if (count($bookings) > 0) {
    echo "Sample: " . json_encode($bookings[0]) . "\n";
}

echo "\n=== 6. KIỂM TRA BẢNG TBL_REVIEWS (Đánh giá) ===\n";
$reviews = $db->table('tbl_reviews')->select('reviewId', 'tourId', 'rating', 'comment')->limit(3)->get();
echo "Số đánh giá: " . count($reviews) . "\n";
if (count($reviews) > 0) {
    echo "Sample: " . json_encode($reviews[0]) . "\n";
} else {
    echo "Không có review nào.\n";
}

echo "\n=== 7. TEST: Lấy dữ liệu từ controller ===\n";
// Test getGuides() từ model
$guideModel = new \App\Models\admin\DuLieuPhanCongHuongDanVien();
$guides = $guideModel->getGuides();
echo "Số guides từ model: " . count($guides) . "\n";
if (count($guides) > 0) {
    echo "Sample guide: " . json_encode($guides[0]) . "\n";
}

// Test getTours() từ model
$tours = $guideModel->getTours();
echo "Số tours từ model: " . count($tours) . "\n";

?>
