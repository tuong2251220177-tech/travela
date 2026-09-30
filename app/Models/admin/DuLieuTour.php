<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DuLieuTour extends Model
{
    use HasFactory;

    protected $table = 'tbl_tours';

    public function getAllTours()
    {
        return DB::table($this->table)
            ->orderBy('tourId', 'DESC')
            ->get()->filter(fn ($tour) => empty($tour->privateRequestId))->values();
    }

    public function createTours($data)
    {
        return DB::table($this->table)->insertGetId($data);
    }

    public function uploadImages($data)
    {
        return DB::table('tbl_images')->insert($data);
    }

    public function uploadTempImages($data)
    {
        return DB::table('tbl_temp_images')->insert($data);
    }

    public function addTimeLine($data)
    {
        return DB::table('tbl_timeline')->insert($data);
    }

    public function updateTour($tourId,$data){
        $updated = DB::table($this->table)
        ->where('tourId',$tourId)
        ->update($data);

        return $updated;
    }
    public function deleteTour($tourId)
    {
        try {
            return DB::transaction(function () use ($tourId) {
                $tour = DB::table($this->table)->where('tourId', $tourId)->lockForUpdate()->first();
                if (!$tour) return ['success' => false, 'message' => 'Tour không còn tồn tại. Vui lòng tải lại danh sách.'];
                $bookings = DB::table('tbl_booking')->where('tourId', $tourId)->count();
                if ($bookings) {
                    return ['success' => false, 'message' => "Tour có {$bookings} đơn đặt nên không thể xóa để bảo toàn lịch sử thanh toán. Hãy sửa tour và tắt khả dụng nếu muốn ngừng nhận khách."];
                }
                DB::table('tbl_timeline')->where('tourId', $tourId)->delete();
                DB::table('tbl_images')->where('tourId', $tourId)->delete();
                DB::table($this->table)->where('tourId', $tourId)->delete();
                return ['success' => true, 'message' => 'Tour đã được xóa thành công.'];
            }, 3);
        } catch (\Illuminate\Database\QueryException $exception) {
            if (in_array((int) ($exception->errorInfo[1] ?? 0), [1451, 19], true)) {
                return ['success' => false, 'message' => 'Tour còn dữ liệu liên quan nên chưa thể xóa. Hãy tắt khả dụng để ngừng nhận khách; ảnh và lịch trình được giữ nguyên.'];
            }
            throw $exception;
        }
    }

    public function getTour($tourId){
        return DB::table($this->table)->where('tourId', $tourId)->first();
    }

    public function getImages($tourId){
        return DB::table('tbl_images')->where('tourId', $tourId)->get();
    }

    public function getTimeLine($tourId){
        return DB::table('tbl_timeline')->where('tourId', $tourId)->get();
    }

    public function deleteData($tourId, $tbl){
        return DB::table($tbl)->where('tourId', $tourId)->delete();
    }

}
