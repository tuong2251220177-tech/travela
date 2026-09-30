<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NhomTour
{
    // Caller holds the tour row lock, serializing group allocation and capacity edits.
    public function allocate(object $tour, int $count, ?int $transportOptionId = null): int
    {
        $max = (int) $tour->maxGuests;
        if ($count < 1 || $max < 1 || (int) $tour->minGuests < 1 || (int) $tour->minGuests > $max) {
            throw ValidationException::withMessages(['booking' => 'Số khách hoặc quy mô đoàn không hợp lệ. Vui lòng kiểm tra cấu hình tối thiểu/tối đa của tour.']);
        }
        if ($count > $max) throw ValidationException::withMessages(['booking' => "Mỗi đoàn tối đa {$max} khách. Vui lòng chia thành nhiều đơn hoặc liên hệ nhân viên để tổ chức đoàn riêng."]);
        $groups = DB::table('tbl_booking')->where('tourId', $tour->tourId)
            ->where('transportOptionId', $transportOptionId)
            ->where('bookingStatus', '!=', 'c')->select('groupNumber', DB::raw('SUM(numAdults + numChildren) as guests'))
            ->groupBy('groupNumber')->orderBy('groupNumber')->get();
        foreach ($groups as $group) if ((int) $group->guests + $count <= $max) return (int) $group->groupNumber;
        $last = (int) DB::table('tbl_booking')->where('tourId', $tour->tourId)->max('groupNumber');
        return $last + 1;
    }

    public function summary(int $tourId, int $groupNumber): object
    {
        $tour = DB::table('tbl_tours')->where('tourId', $tourId)->first();
        $query = DB::table('tbl_booking')->where('tourId', $tourId)->where('groupNumber', $groupNumber)->where('bookingStatus', '!=', 'c');
        $registered = (int) (clone $query)->sum(DB::raw('numAdults + numChildren'));
        $confirmed = (int) (clone $query)->whereIn('bookingStatus', ['y', 'f'])->sum(DB::raw('numAdults + numChildren'));
        return (object) ['registered' => $registered, 'confirmed' => $confirmed, 'minimum' => (int) $tour->minGuests, 'maximum' => (int) $tour->maxGuests, 'ready' => $confirmed >= (int) $tour->minGuests];
    }
}
