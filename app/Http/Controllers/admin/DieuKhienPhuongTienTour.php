<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DieuKhienPhuongTienTour extends Controller
{
    private function tour(int $id): object
    {
        $tour = DB::table('tbl_tours')->where('tourId', $id)->first();
        abort_unless($tour && empty($tour->privateRequestId), 404);
        return $tour;
    }

    public function index(int $tourId)
    {
        return view('admin.phuong-tien-tour', ['title' => 'Phương tiện di chuyển', 'tour' => $this->tour($tourId),
            'options' => DB::table('tbl_tour_transport_options')->where('tourId', $tourId)->orderBy('id')->get()]);
    }

    public function store(Request $request, int $tourId)
    {
        $this->tour($tourId);
        $data = $request->validate(['name' => 'required|string|max:100', 'surcharge' => 'required|integer|min:0|max:100000000',
            'chargeUnit' => 'required|in:person,booking']);
        DB::table('tbl_tour_transport_options')->insert($data + ['tourId' => $tourId, 'active' => true, 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Đã thêm phương tiện.');
    }

    public function update(Request $request, int $tourId, int $optionId)
    {
        $this->tour($tourId);
        $data = $request->validate(['surcharge' => 'required|integer|min:0|max:100000000',
            'chargeUnit' => 'required|in:person,booking', 'active' => 'required|boolean']);
        abort_unless(DB::table('tbl_tour_transport_options')->where('tourId', $tourId)->where('id', $optionId)->exists(), 404);
        DB::table('tbl_tour_transport_options')->where('tourId', $tourId)->where('id', $optionId)->update($data + ['updated_at' => now()]);
        return back()->with('success', 'Đã cập nhật. Giá trong các đơn đã đặt được giữ nguyên.');
    }
}
