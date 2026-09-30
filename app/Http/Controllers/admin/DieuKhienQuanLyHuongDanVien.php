<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Services\QuanLyHuongDanVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DieuKhienQuanLyHuongDanVien extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['q' => 'nullable|string|max:100', 'status' => 'nullable|in:active,locked', 'history' => 'nullable|in:unused']);
        $query = QuanLyHuongDanVien::guides(DB::table('tbl_admin as a'), 'a.');
        $q = trim($data['q'] ?? '');
        if ($q !== '') $query->where(function ($query) use ($q) {
            $query->where('a.username', 'like', '%'.$q.'%')->orWhere('a.full_name', 'like', '%'.$q.'%')->orWhere('a.email', 'like', '%'.$q.'%');
        });
        if (!empty($data['status'])) $query->where('a.guideEnabled', $data['status'] === 'active');
        if (($data['history'] ?? '') === 'unused') $query->whereNotExists(fn ($q) => $q->selectRaw('1')->from('tbl_guide_assignments as ga')->whereColumn('ga.guide_id', 'a.adminId'));
        if (\Illuminate\Support\Facades\Schema::hasTable('tbl_guide_booking_status')) {
            if (($data['history'] ?? '') === 'unused') $query->whereNotExists(fn ($q) => $q->selectRaw('1')->from('tbl_guide_booking_status as gs')->whereColumn('gs.guide_id', 'a.adminId'));
        }
        $guides = $query->select('a.*', 'a.adminId as adminId')->selectSub(DB::table('tbl_guide_assignments as ga')->selectRaw('COUNT(*)')->whereColumn('ga.guide_id', 'a.adminId'), 'assignmentCount')
            ->orderByDesc('a.adminId')->paginate(15)->withQueryString();
        $historyIds = \Illuminate\Support\Facades\Schema::hasTable('tbl_guide_booking_status')
            ? DB::table('tbl_guide_booking_status')->whereIn('guide_id', $guides->pluck('adminId'))->distinct()->pluck('guide_id')->all() : [];
        foreach ($guides as $guide) $guide->hasGuestHistory = in_array($guide->adminId, $historyIds);
        return view('admin.quan-ly-huong-dan-vien', ['title' => 'Quản lý hướng dẫn viên', 'guides' => $guides, 'q' => $q]);
    }

    public function status(Request $request, int $id, QuanLyHuongDanVien $service)
    {
        $data = $request->validate(['enabled' => 'required|boolean']);
        $service->change($id, (bool) $data['enabled']);
        return back()->with('success', $data['enabled'] ? 'Đã mở khóa tài khoản.' : 'Đã khóa tài khoản. Lịch sử phân công được giữ nguyên.');
    }
}
