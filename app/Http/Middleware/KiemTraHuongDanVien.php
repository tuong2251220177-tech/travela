<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class KiemTraHuongDanVien
{
    public function handle(Request $request, Closure $next)
    {
        $guide = $request->session()->has('guide') ? \App\Services\QuanLyHuongDanVien::guides(\Illuminate\Support\Facades\DB::table('tbl_admin'))
            ->where('adminId', $request->session()->get('guide_id'))
            ->where('username', $request->session()->get('guide'))->where('guideEnabled', true)->first() : null;
        if (!$guide) {
            $request->session()->forget(['guide', 'guide_id']);
            toastr()->error('Vui lòng đăng nhập với tài khoản hướng dẫn viên.', 'Thông báo');
            return redirect()->route('guide.login');
        }

        return $next($request);
    }
}
