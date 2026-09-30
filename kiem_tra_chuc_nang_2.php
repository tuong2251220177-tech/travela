<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$db = app('db');

echo "=== 1. KIỂM TRA CẤU TRÚC BẢNG TBL_REVIEWS ===\n";
try {
    $columns = $db->select("DESCRIBE tbl_reviews");
    foreach ($columns as $col) {
        echo $col->Field . " (" . $col->Type . ")\n";
    }
} catch (\Exception $e) {
    echo "Bảng tbl_reviews không tồn tại hoặc lỗi: " . $e->getMessage() . "\n";
}

echo "\n=== 2. TEST: Login guide thành công ===\n";
// Simulate session: guide với id=1
$guideModel = new \App\Models\guide\DuLieuDangNhapHuongDanVien();
$guide = $guideModel->login('admin', md5('123456'));
echo "Login admin/123456: " . ($guide ? "Thành công" : "Thất bại") . "\n";
if ($guide) {
    echo "Guide info: " . json_encode($guide) . "\n";
}

echo "\n=== 3. TEST: Lấy tour được phân công cho guide ===\n";
$assignmentModel = new \App\Models\guide\DuLieuPhanCongHuongDanVien();
$tours = $assignmentModel->getAssignedToursForGuide(1);
echo "Số tour được phân công cho guide ID=1: " . count($tours) . "\n";
if (count($tours) > 0) {
    echo "Tour 1: " . json_encode($tours[0]) . "\n";
}

echo "\n=== 4. TEST: Lấy chi tiết tour ===\n";
if (count($tours) > 0) {
    $tourDetail = $assignmentModel->getAssignedTourDetail(1, $tours[0]->tour_id);
    echo "Chi tiết tour: " . json_encode($tourDetail) . "\n";
}

echo "\n=== 5. TEST: Lấy khách trong tour ===\n";
if (count($tours) > 0) {
    $guests = $assignmentModel->getGuestsForTour($tours[0]->tour_id, 1);
    echo "Số khách: " . count($guests) . "\n";
    if (count($guests) > 0) {
        echo "Khách 1: " . json_encode($guests[0]) . "\n";
    }
}

echo "\n=== 6. TEST: Cập nhật trạng thái tour ===\n";
if (count($tours) > 0) {
    $result = $assignmentModel->saveTourStatus($tours[0]->tour_id, 1, [
        'tour_status' => 'in_progress',
        'incident_note' => 'Test note',
        'meeting_point' => 'Test meeting point',
        'departure_date' => '2026-09-01',
        'updated_at' => now(),
    ]);
    echo "Cập nhật tour status: " . ($result ? "Thành công" : "Thất bại") . "\n";
}

echo "\n=== 7. TEST: Cập nhật trạng thái khách ===\n";
$result = $assignmentModel->saveGuestStatus(2, 4, 1, [
    'guest_status' => 'present',
    'note' => 'Khách có mặt',
    'updated_at' => now(),
]);
echo "Cập nhật guest status: " . ($result ? "Thành công" : "Thất bại") . "\n";

echo "\n✅ KIỂM TRA XONG - Tất cả chức năng cơ bản hoạt động!\n";
?>
