<?php

namespace App\Models\guide;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DuLieuPhanCongHuongDanVien extends Model
{
    use HasFactory;

    public function getAssignedToursForGuide($guideId)
    {
        if (!Schema::hasTable('tbl_guide_assignments')) {
            return collect();
        }

        return DB::table('tbl_guide_assignments as ga')
            ->leftJoin('tbl_tours as t', 't.tourId', '=', 'ga.tour_id')
            ->where('ga.guide_id', $guideId)
            ->select(
                'ga.*',
                't.tourId',
                't.title',
                't.destination',
                't.time',
                't.quantity',
                't.description'
            )
            ->orderBy('ga.departure_date', 'asc')
            ->get();
    }

    public function getAssignedTourDetail($guideId, $tourId, $groupNumber = 1)
    {
        if (!Schema::hasTable('tbl_guide_assignments')) {
            return null;
        }

        return DB::table('tbl_guide_assignments as ga')
            ->leftJoin('tbl_tours as t', 't.tourId', '=', 'ga.tour_id')
            ->where('ga.guide_id', $guideId)
            ->where('ga.tour_id', $tourId)->where('ga.groupNumber', $groupNumber)
            ->select(
                'ga.*',
                't.tourId',
                't.title',
                't.description',
                't.destination',
                't.time',
                't.quantity',
                't.minGuests', 't.maxGuests',
                't.priceAdult',
                't.priceChild',
                DB::raw("(select coalesce(sum(b.numAdults + b.numChildren), 0) from tbl_booking as b where b.tourId = t.tourId and b.groupNumber = ga.groupNumber and b.bookingStatus in ('y', 'f')) as registered_guests")
            )
            ->first();
    }

    public function getGuestsForTour($tourId, $guideId, $groupNumber = 1)
    {
        if (!Schema::hasTable('tbl_booking')) {
            return collect();
        }

        $query = DB::table('tbl_booking as b')
            ->leftJoin('tbl_users as u', 'u.userId', '=', 'b.userId')
            ->where('b.tourId', $tourId)->where('b.groupNumber', $groupNumber)
            ->whereIn('b.bookingStatus', ['y', 'f'])
            ->select(
                'b.bookingId',
                'b.fullName as booking_name',
                'u.username as user_name',
                'b.email',
                'b.numAdults',
                'b.numChildren',
                'b.bookingStatus',
                'b.phoneNumber'
            );

        if (Schema::hasTable('tbl_guide_booking_status')) {
            $query->leftJoin('tbl_guide_booking_status as gbs', function ($join) use ($guideId, $tourId) {
                $join->on('gbs.booking_id', '=', 'b.bookingId')
                    ->where('gbs.guide_id', $guideId)
                    ->where('gbs.tour_id', $tourId);
            })
            ->addSelect('gbs.guest_status', 'gbs.note as guest_note');
        }

        return $query->orderBy('b.bookingDate', 'desc')->get();
    }

    public function saveTourStatus($tourId, $guideId, array $data, $groupNumber = 1)
    {
        return DB::table('tbl_guide_assignments')
            ->updateOrInsert(
                ['tour_id' => $tourId, 'guide_id' => $guideId, 'groupNumber' => $groupNumber],
                $data
            );
    }

    public function saveGuestStatus($bookingId, $tourId, $guideId, array $data)
    {
        return DB::table('tbl_guide_booking_status')
            ->updateOrInsert(
                ['booking_id' => $bookingId, 'tour_id' => $tourId, 'guide_id' => $guideId],
                $data
            );
    }
}
