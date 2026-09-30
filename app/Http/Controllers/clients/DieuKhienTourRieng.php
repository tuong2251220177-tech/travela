<?php

namespace App\Http\Controllers\clients;

use App\Http\Controllers\Controller;
use App\Services\TourRieng;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DieuKhienTourRieng extends Controller
{
    private function owner(): int
    {
        $id = (int) $this->getUserId();
        abort_unless($id > 0, 401);
        return $id;
    }

    public function index(Request $request)
    {
        $requests = DB::table('tbl_private_tour_requests')->where('userId', $this->owner())->orderByDesc('id')->paginate(10);
        $tours = DB::table('tbl_tours')->where('availability', true)->whereNull('privateRequestId')->orderBy('title')->get(['tourId', 'title']);
        return view('clients.tour-rieng', ['title' => 'Tour riêng của bạn', 'requests' => $requests, 'tours' => $tours]);
    }

    public function store(Request $request, TourRieng $service)
    {
        $data = $request->validate([
            'tourId' => 'required|integer|min:1', 'token' => 'required|uuid',
            'numAdults' => 'required|integer|min:1|max:100', 'numChildren' => 'required|integer|min:0|max:100',
            'requestedDate' => 'required|date_format:Y-m-d|after:today',
            'fullName' => 'required|string|max:150', 'email' => 'required|email|max:150',
            'phoneNumber' => 'required|string|regex:/^[0-9+().\s-]{8,20}$/', 'address' => 'required|string|max:255',
            'notes' => 'nullable|string|max:3000',
        ]);
        $id = $service->request($this->owner(), $data);
        return redirect()->route('private-tours.show', $id)->with('success', 'Đã gửi yêu cầu. Bạn có thể theo dõi báo giá tại đây.');
    }

    public function show(int $id)
    {
        $item = DB::table('tbl_private_tour_requests')->where('id', $id)->where('userId', $this->owner())->first();
        abort_unless($item, 404);
        return view('clients.chi-tiet-tour-rieng', ['title' => 'Yêu cầu tour riêng #'.$id, 'item' => $item]);
    }

    public function accept(int $id, Request $request, TourRieng $service)
    {
        $data = $request->validate([
            'revision' => 'required|integer|min:1', 'agree' => 'accepted',
            'payment' => 'required|in:bank-transfer,office-payment', 'passengers' => 'required|array|min:1|max:200',
            'paymentPlan' => 'nullable|in:deposit,full',
            'passengers.*.fullName' => 'required|string|max:150',
        ]);
        $item = DB::table('tbl_private_tour_requests')->where('id', $id)->where('userId', $this->owner())->first();
        abort_unless($item, 404);
        $passengers = array_map(fn ($p) => ['fullName' => $p['fullName'], 'email' => $item->email, 'tel' => $item->phoneNumber, 'address' => $item->address], array_values($data['passengers']));
        $bookingId = $service->accept($id, $this->owner(), (int) $data['revision'], $data['payment'], $passengers, $data['paymentPlan'] ?? 'deposit');
        return redirect()->route('tour-booked', ['bookingId' => $bookingId])->with('success', 'Đã tạo đơn tour riêng. Vui lòng đặt cọc theo hướng dẫn trong đơn.');
    }

    public function cancel(int $id, TourRieng $service)
    {
        $service->close($id, $this->owner());
        return back()->with('success', 'Đã rút yêu cầu tour riêng.');
    }
}
