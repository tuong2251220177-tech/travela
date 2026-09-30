<?php

namespace App\Models\guide;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DuLieuDangNhapHuongDanVien extends Model
{
    use HasFactory;

    protected $table = 'tbl_admin';

    public function login($username, $pass)
    {
        $query = \App\Services\QuanLyHuongDanVien::guides(DB::table($this->table))
            ->where('username', $username)
            ->where('guideEnabled', true)
            ->where('password', $pass);

        if (Schema::hasColumn($this->table, 'role')) {
            $query->where('role', 'guide');
        }

        $record = $query->select('*', 'adminId as adminId')->first();

        if ($record) {
            return $record;
        }

        return null;
    }
}
