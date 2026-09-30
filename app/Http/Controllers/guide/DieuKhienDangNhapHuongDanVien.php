<?php

namespace App\Http\Controllers\guide;

use App\Http\Controllers\Controller;
use App\Models\guide\DuLieuDangNhapHuongDanVien;
use Illuminate\Http\Request;

class DieuKhienDangNhapHuongDanVien extends Controller
{
    protected $guideLogin;

    public function __construct()
    {
        $this->guideLogin = new DuLieuDangNhapHuongDanVien();
    }

    public function index()
    {
        return view('guide.dang-nhap', ['title' => 'Đăng nhập hướng dẫn viên']);
    }

    public function loginGuide(Request $request)
    {
        $username = trim($request->input('username'));
        $password = md5($request->input('password'));

        $guide = $this->guideLogin->login($username, $password);

        if ($guide) {
            $request->session()->put('guide', $guide->username ?? $username);
            $request->session()->put('guide_id', $guide->id ?? $guide->adminId ?? 0);
            toastr()->success('Đăng nhập thành công.', 'Thông báo');
            return redirect()->route('guide.dashboard');
        }

        toastr()->error('Thông tin đăng nhập hướng dẫn viên không chính xác.', 'Thông báo');
        return redirect()->route('guide.login');
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['guide', 'guide_id']);
        toastr()->success('Đăng xuất thành công.', 'Thông báo');
        return redirect()->route('guide.login');
    }
}
