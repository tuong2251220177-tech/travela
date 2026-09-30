<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PhuongTienTour
{
    public function price(int $tourId, ?int $optionId, int $guests, bool $lock = false): array
    {
        if (!$optionId) return ['transportOptionId' => null, 'transportName' => 'Phương tiện tiêu chuẩn theo chương trình', 'transportFee' => 0];
        $query = DB::table('tbl_tour_transport_options')->where('id', $optionId)->where('tourId', $tourId)->where('active', true);
        if ($lock) $query->lockForUpdate();
        $option = $query->first();
        if (!$option) throw ValidationException::withMessages(['transportOptionId' => 'Phương tiện không thuộc tour hoặc đã ngừng nhận đặt. Vui lòng tải lại trang và chọn lại.']);
        return ['transportOptionId' => $option->id, 'transportName' => $option->name,
            'transportFee' => (int) $option->surcharge * ($option->chargeUnit === 'person' ? $guests : 1)];
    }
}
