<?php
namespace App\Http\Controllers\admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DieuKhienMaGiamGia extends Controller
{
    public function index() {
        return view('admin.ma-giam-gia', ['title' => 'Mã giảm giá', 'coupons' => DB::table('tbl_coupons')->orderByDesc('id')->paginate(30)]);
    }
    public function store(Request $request) {
        $data = $request->validate([
            'quantity' => 'required|integer|min:1|max:100', 'prefix' => 'nullable|regex:/^[A-Za-z0-9]{1,10}$/',
            'isPublic' => 'required|boolean', 'perUserLimit' => 'nullable|integer|in:1',
            'type' => 'required|in:percent,fixed', 'value' => 'required|integer|min:1|max:1000000000',
            'minTotal' => 'required|integer|min:0|max:1000000000', 'maxDiscount' => 'nullable|integer|min:1|max:1000000000',
            'usageLimit' => 'required|integer|min:1|max:1000000', 'expiresAt' => 'required|date_format:Y-m-d|after_or_equal:today',
        ]);
        if ($data['type'] === 'percent' && $data['value'] > 99) return back()->withErrors(['value' => 'Mức giảm phần trăm từ 1 đến 99.'])->withInput();
        DB::transaction(function () use ($data) {
            for ($i = 0; $i < $data['quantity']; $i++) {
                do { $code = strtoupper(($data['prefix'] ?? 'TOUR') . '-' . Str::random(12)); }
                while (DB::table('tbl_coupons')->where('code', $code)->exists());
                DB::table('tbl_coupons')->insert([
                    'code' => $code, 'type' => $data['type'], 'value' => $data['value'], 'minTotal' => $data['minTotal'],
                    'isPublic' => $data['isPublic'], 'perUserLimit' => 1,
                    'maxDiscount' => $data['type'] === 'percent' ? ($data['maxDiscount'] ?? null) : null,
                    'usageLimit' => $data['usageLimit'], 'expiresAt' => \Carbon\Carbon::parse($data['expiresAt'])->endOfDay(),
                    'createdBy' => (string) session('admin'), 'created_at' => now(),
                ]);
            }
        });
        return back()->with('success', 'Đã tạo ' . $data['quantity'] . ' mã ngẫu nhiên.');
    }
    public function toggle(Request $request, int $id) {
        $data = $request->validate(['active' => 'required|boolean']);
        abort_unless(DB::table('tbl_coupons')->where('id', $id)->exists(), 404);
        DB::table('tbl_coupons')->where('id', $id)->update(['active' => $data['active']]);
        return back()->with('success', 'Đã cập nhật trạng thái mã.');
    }
    public function settings(Request $request, int $id) {
        $data = $request->validate(['isPublic' => 'required|boolean', 'perUserLimit' => 'nullable|integer|in:1']);
        abort_unless(DB::table('tbl_coupons')->where('id', $id)->exists(), 404);
        DB::table('tbl_coupons')->where('id', $id)->update(['isPublic' => $data['isPublic'], 'perUserLimit' => 1]);
        return back()->with('success', 'Đã lưu phạm vi hiển thị và giới hạn mỗi tài khoản.');
    }
}
