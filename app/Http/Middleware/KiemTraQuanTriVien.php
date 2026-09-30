<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;

class KiemTraQuanTriVien
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->session()->has('admin') && \Illuminate\Support\Facades\Schema::hasColumn('tbl_admin', 'guideEnabled')
            && \Illuminate\Support\Facades\DB::table('tbl_admin')->where('username', $request->session()->get('admin'))->where('guideEnabled', false)->exists()) {
            $request->session()->forget('admin');
        }
        if ($request->session()->has('admin') && \Illuminate\Support\Facades\Schema::hasColumn('tbl_admin', 'role')
            && \Illuminate\Support\Facades\DB::table('tbl_admin')->where('username', $request->session()->get('admin'))->where('role', 'guide')->exists()) {
            $request->session()->forget('admin');
        }
        if(!$request->session()->has('admin'))
        {
            toastr()->error('Vui lòng đăng nhập vào Admin để thực hiện.', "Thông báo");
            return redirect()->route('admin.login');
        }
        return $next($request);
    }
}
