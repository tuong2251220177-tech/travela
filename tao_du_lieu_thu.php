<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$db = app('db');

echo "=== TẠO TEST DATA ===\n";

// 1. Tạo user test
echo "\n1. Tạo user test...\n";
$userId = $db->table('tbl_users')->insertGetId([
    'userName' => 'testuser',
    'email' => 'testuser@gmail.com',
    'passWord' => '123456',
    'phoneNumber' => '0123456789',
    'address' => 'Test Address',
    'isActive' => 'y',
    'createdDate' => now(),
    'updatedDate' => now(),
]);
echo "User ID: $userId\n";

// 2. Tạo booking cho tour ID 8 (tour được guide phân công)
echo "\n2. Tạo booking cho tour 8...\n";
$bookingId = $db->table('tbl_booking')->insertGetId([
    'userId' => $userId,
    'tourId' => 8,
    'fullName' => 'Test Booking Person',
    'phoneNumber' => '0987654321',
    'numAdults' => 2,
    'numChildren' => 1,
    'totalPrice' => 5000000,
    'bookingStatus' => 'pending',
    'bookingDate' => now(),
    'specialRequestes' => 'Yêu cầu đặc biệt: khỏe mạnh',
]);
echo "Booking ID: $bookingId\n";

// 3. Tạo review cho tour 8
echo "\n3. Tạo review cho tour 8...\n";
$reviewId = $db->table('tbl_reviews')->insertGetId([
    'tourId' => 8,
    'userId' => $userId,
    'rating' => 4.5,
    'comment' => 'Tour rất hay! Hướng dẫn viên chuyên nghiệp và thân thiện.',
    'timestamp' => now(),
]);
echo "Review ID: $reviewId\n";

// 4. Kiểm tra lại khách trong tour
echo "\n4. Kiểm tra khách trong tour 8...\n";
$guests = $db->table('tbl_booking as b')
    ->join('tbl_users as u', 'u.userId', '=', 'b.userId')
    ->where('b.tourId', 8)
    ->select(
        'b.bookingId',
        'b.fullName as booking_name',
        'u.username as user_name',
        'u.email',
        'b.numAdults',
        'b.numChildren',
        'b.bookingStatus',
        'b.phoneNumber'
    )
    ->get();

echo "Số khách: " . count($guests) . "\n";
if (count($guests) > 0) {
    echo "Khách 1: " . json_encode($guests[0]) . "\n";
}

// 5. Kiểm tra review
echo "\n5. Kiểm tra review cho tour 8...\n";
$reviews = $db->table('tbl_reviews as r')
    ->join('tbl_users as u', 'u.userId', '=', 'r.userId')
    ->where('r.tourId', 8)
    ->select('r.reviewsId', 'r.rating', 'r.comment', 'u.username', 'r.timestamp')
    ->get();

echo "Số review: " . count($reviews) . "\n";
if (count($reviews) > 0) {
    foreach ($reviews as $review) {
        echo json_encode($review) . "\n";
    }
}

// 6. Test lấy khách qua model
echo "\n6. Test lấy khách qua DuLieuPhanCongHuongDanVien...\n";
$assignmentModel = new \App\Models\guide\DuLieuPhanCongHuongDanVien();
$guests = $assignmentModel->getGuestsForTour(8, 1);
echo "Số khách (qua model): " . count($guests) . "\n";
if (count($guests) > 0) {
    echo "Khách: " . json_encode($guests[0]) . "\n";
}

echo "\n✅ TEST DATA CREATED!\n";
?>
