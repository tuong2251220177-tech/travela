<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuanLyHuongDanVien
{
    public static function guides($query, string $prefix = '')
    {
        return \Illuminate\Support\Facades\Schema::hasColumn('tbl_admin', 'role')
            ? $query->where($prefix.'role', 'guide')
            : $query->where($prefix.'username', '!=', 'admin');
    }
    public function change(int $id, ?bool $enabled): void
    {
        DB::transaction(function () use ($id, $enabled) {
            $guide = self::guides(DB::table('tbl_admin'))->where('adminId', $id)->lockForUpdate()->first();
            abort_unless($guide, 404);
            if ($enabled === null) {
                if (DB::table('tbl_guide_assignments')->where('guide_id', $id)->exists()
                    || (\Illuminate\Support\Facades\Schema::hasTable('tbl_guide_booking_status') && DB::table('tbl_guide_booking_status')->where('guide_id', $id)->exists())) {
                    throw ValidationException::withMessages(['guide' => 'Tài khoản đã có lịch sử phân công. Hãy khóa tài khoản để giữ lịch sử tour.']);
                }
                DB::table('tbl_admin')->where('adminId', $id)->delete();
            } else {
                DB::table('tbl_admin')->where('adminId', $id)->update(['guideEnabled' => $enabled]);
            }
        });
    }
}
