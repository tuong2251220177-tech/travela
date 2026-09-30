<?php

namespace App\Models\clients;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DuLieuNguoiDung extends Model
{
    use HasFactory;

    protected $table = 'tbl_users';


    public function getUserId($username)
    {
        return DB::table($this->table)
            ->select('userId')
            ->where('userName', $username)->value('userId');
    }
    public function getUser($id)
    {
        $users = DB::table($this->table)
            ->where('userId', $id)->first();

        return $users;
    }

    public function updateUser($id, $data)
    {
        $update = DB::table($this->table)
            ->where('userid', $id)
            ->update($data);

        return $update;
    }

    public function getMyTours($id, $status = 'all', $period = 'all')
    {
            $query = DB::table('tbl_booking')
        ->join('tbl_tours', 'tbl_booking.tourId', '=', 'tbl_tours.tourId')
        ->join('tbl_checkout', 'tbl_booking.bookingId', '=', 'tbl_checkout.bookingId')
        ->where('tbl_booking.userId', $id)
            ->select(
                'tbl_booking.bookingId', 'tbl_booking.bookingDate', 'tbl_booking.bookingStatus',
                'tbl_booking.numAdults', 'tbl_booking.numChildren', 'tbl_tours.tourId',
                'tbl_tours.title', 'tbl_tours.destination', 'tbl_tours.startDate',
                'tbl_tours.endDate', 'tbl_tours.time', 'tbl_checkout.checkoutId',
                  'tbl_booking.totalPrice', 'tbl_booking.cancelStatus', 'tbl_booking.transferReportedAt', 'tbl_checkout.paymentStatus'
            );

            if ($status !== 'all') {
                $query->where('tbl_booking.bookingStatus', $status);
            }

            if ($period === 'completed') {
                $query->whereDate('tbl_tours.endDate', '<', now());
            } elseif ($period === 'upcoming') {
                $query->where(function ($periodQuery) {
                    $periodQuery->whereNull('tbl_tours.startDate')
                        ->orWhereDate('tbl_tours.startDate', '>=', now());
                });
            }

            $myTours = $query->orderByDesc('tbl_booking.bookingDate')->get();

        foreach ($myTours as $tour) {
            // Lấy rating từ tbl_reviews cho mỗi tour
            $tour->rating = DB::table('tbl_reviews')
                ->where('tourId', $tour->tourId)
                ->where('userId', $id)
                ->value('rating'); // Dùng value() để lấy giá trị rating
        }
        foreach ($myTours as $tour) {
            // Lấy danh sách hình ảnh thuộc về tour
            $tour->images = DB::table('tbl_images')
                ->where('tourId', $tour->tourId)
                ->pluck('imageUrl');
        }

        return $myTours;
    }
}
