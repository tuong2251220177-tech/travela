<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DuLieuPhanCongHuongDanVien extends Model
{
    use HasFactory;

    protected $table = 'tbl_guide_assignments';

    public function getGuides()
    {
        $select = ['adminId as guide_id', 'username as name', 'email'];

        if (Schema::hasColumn('tbl_admin', 'full_name')) {
            $select[] = 'full_name';
        }

        if (Schema::hasColumn('tbl_admin', 'avatar')) {
            $select[] = 'avatar';
        }

        if (Schema::hasColumn('tbl_admin', 'description')) {
            $select[] = 'description';
        }

        $query = DB::table('tbl_admin')->select($select);

        if (Schema::hasColumn('tbl_admin', 'role')) {
            $query->where('role', 'guide');
        } else {
            $query->where('username', '!=', 'admin');
        }

        $query->where('guideEnabled', true);
        return $query->orderBy('username')->get();
    }

    public function getTours()
    {
        if (!Schema::hasTable('tbl_booking')) {
            return collect();
        }

        return DB::table('tbl_tours as t')
            ->join('tbl_booking as b', 'b.tourId', '=', 't.tourId')
            ->where('b.bookingStatus', '!=', 'c')
            ->select('t.tourId as id', 't.title', 't.destination', 't.time', 't.minGuests', 't.maxGuests', 'b.groupNumber')
            ->selectRaw('SUM(b.numAdults + b.numChildren) as registered_guests')
            ->selectRaw("SUM(CASE WHEN b.bookingStatus IN ('y', 'f') THEN b.numAdults + b.numChildren ELSE 0 END) as confirmed_guests")
            ->groupBy('t.tourId', 't.title', 't.destination', 't.time', 't.minGuests', 't.maxGuests', 'b.groupNumber')
            ->orderByDesc('t.tourId')
            ->get();
    }

    public function getAssignments()
    {
        return DB::table('tbl_guide_assignments as ga')
            ->leftJoin('tbl_admin as a', 'a.adminId', '=', 'ga.guide_id')
            ->leftJoin('tbl_tours as t', 't.tourId', '=', 'ga.tour_id')
            ->select(
                'ga.assignment_id',
                'ga.groupNumber',
                'ga.guide_id',
                'ga.tour_id',
                'ga.meeting_point',
                'ga.departure_date',
                'ga.tour_status',
                'a.username as guide_name',
                't.title as tour_title',
                't.destination as tour_destination'
            )
            ->orderByDesc('ga.assignment_id')
            ->get();
    }

    public function assignTour(array $data)
    {
        return DB::transaction(function () use ($data) {
            $guide = \App\Services\QuanLyHuongDanVien::guides(DB::table('tbl_admin'))->where('adminId', $data['guide_id'])->where('guideEnabled', true)->lockForUpdate()->first();
            if (!$guide) throw \Illuminate\Validation\ValidationException::withMessages(['guide_id' => 'Hướng dẫn viên đã bị khóa hoặc không tồn tại. Vui lòng chọn lại.']);
        if (Schema::hasTable($this->table)) {
            return DB::table($this->table)
                ->updateOrInsert(
                    ['guide_id' => $data['guide_id'], 'tour_id' => $data['tour_id'], 'groupNumber' => $data['groupNumber']],
                    [
                        'meeting_point' => $data['meeting_point'] ?? null,
                        'departure_date' => $data['departure_date'] ?? null,
                        'tour_status' => $data['tour_status'] ?? 'assigned',
                        'incident_note' => $data['incident_note'] ?? null,
                        'updated_at' => now(),
                    ]
                );
        }

        return false;
        });
    }

    public function createGuide(array $data)
    {
        if (!Schema::hasTable('tbl_admin')) {
            return false;
        }

        $username = trim((string) ($data['username'] ?? ''));
        if ($username === '' || DB::table('tbl_admin')->where('username', $username)->exists()) {
            return false;
        }

        $insert = [
            'username' => $username,
            'password' => md5((string) ($data['password'] ?? '')),
            'email' => $data['email'] ?? null,
        ];

        if (Schema::hasColumn('tbl_admin', 'full_name')) {
            $insert['full_name'] = trim((string) ($data['full_name'] ?? $username));
        }

        if (Schema::hasColumn('tbl_admin', 'avatar')) {
            $insert['avatar'] = $data['avatar'] ?? null;
        }

        if (Schema::hasColumn('tbl_admin', 'description')) {
            $insert['description'] = $data['description'] ?? null;
        }

        if (Schema::hasColumn('tbl_admin', 'role')) {
            $insert['role'] = 'guide';
        }

        if (Schema::hasColumn('tbl_admin', 'created_at')) {
            $insert['created_at'] = now();
        }

        if (Schema::hasColumn('tbl_admin', 'updated_at')) {
            $insert['updated_at'] = now();
        }

        return DB::table('tbl_admin')->insert($insert);
    }
}
