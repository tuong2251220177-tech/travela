<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Services\TourRieng;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DieuKhienTourRieng extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        $status = is_string($status) ? $status : 'all';
        $items = DB::table('tbl_private_tour_requests')
            ->when(isset(TourRieng::LABELS[$status]), fn ($q) => $q->where('status', $status))->orderByDesc('id')->paginate(15)->withQueryString();
        return view('admin.tour-rieng', ['title' => 'Yêu cầu tour riêng', 'items' => $items, 'status' => $status]);
    }

    public function show(int $id)
    {
        $item = DB::table('tbl_private_tour_requests')->where('id', $id)->first();
        abort_unless($item, 404);
        return view('admin.chi-tiet-tour-rieng', ['title' => 'Tour riêng #'.$id, 'item' => $item]);
    }

    public function quote(int $id, Request $request, TourRieng $service)
    {
        $data = $request->validate([
            'revision' => 'required|integer|min:0', 'quoteTotal' => 'required|integer|min:1000|max:1000000000',
            'startDate' => 'required|date|after:now', 'endDate' => 'required|date|after_or_equal:startDate',
            'expiresAt' => 'required|date|after:now|before:startDate',
            'quoteNotes' => 'required|string|min:10|max:5000', 'confirmedResources' => 'accepted',
        ]);
        $service->quote($id, $data);
        return back()->with('success', 'Đã gửi báo giá. Khách xem và chấp nhận trong mục Tour riêng.');
    }

    public function decline(int $id, Request $request, TourRieng $service)
    {
        $data = $request->validate(['reason' => 'required|string|min:5|max:1000']);
        $service->close($id, null, $data['reason']);
        return back()->with('success', 'Đã từ chối yêu cầu và ghi rõ lý do cho khách.');
    }
}
