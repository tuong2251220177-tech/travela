<?php

namespace App\Http\Controllers\guide;

use App\Http\Controllers\Controller;
use App\Models\admin\DuLieuDatTour;
use App\Models\guide\DuLieuPhanCongHuongDanVien;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DieuKhienBangDieuKhienHuongDanVien extends Controller
{
    protected $guideAssignments;
    protected $bookings;

    public function __construct()
    {
        $this->guideAssignments = new DuLieuPhanCongHuongDanVien();
        $this->bookings = new DuLieuDatTour();
    }

    public function index(Request $request)
    {
        $guideId = $this->resolveGuideId($request);
        $guideName = $request->session()->get('guide');
        $tours = $this->guideAssignments->getAssignedToursForGuide($guideId);

        return view('guide.bang-dieu-khien', compact('tours', 'guideName'));
    }

    public function detail(Request $request, $tourId)
    {
        $guideId = $this->resolveGuideId($request);
        $tour = $this->guideAssignments->getAssignedTourDetail($guideId, $tourId, (int) $request->input('groupNumber', 1));
        if (!$tour) {
            toastr()->error('Bạn không có quyền xem tour này.', 'Thông báo');
            return redirect()->route('guide.dashboard');
        }

        $guests = $this->guideAssignments->getGuestsForTour($tourId, $guideId, (int) $request->input('groupNumber', 1));

        return view('guide.chi-tiet-tour', compact('tour', 'guests'));
    }

    public function downloadManifest(Request $request, $tourId, $format)
    {
        $guideId = $this->resolveGuideId($request);
        $tour = $this->guideAssignments->getAssignedTourDetail($guideId, $tourId, (int) $request->input('groupNumber', 1));
        if (!$tour) {
            abort(403);
        }

        $rows = $this->bookings->getApprovedPassengerManifest($tourId, (int) $request->input('groupNumber', 1));
        if ($rows->isEmpty()) {
            abort(404, 'Chưa có booking đã duyệt cho tour này.');
        }

        if ($format === 'pdf') {
            return Pdf::loadView('exports.danh-sach-doan-pdf', compact('tour', 'rows'))
                ->download('tour-' . $tourId . '-passengers.pdf');
        }

        if ($format === 'xls') {
            $content = view('exports.danh-sach-doan-excel', compact('tour', 'rows'))->render();
            return response($content, 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="tour-' . $tourId . '-passengers.xls"',
            ]);
        }

        abort(404);
    }

    public function updateTourStatus(Request $request, $tourId)
    {
        $guideId = $this->resolveGuideId($request);
        $groupNumber = (int) $request->input('groupNumber', 1);
        abort_unless($this->guideAssignments->getAssignedTourDetail($guideId, $tourId, $groupNumber), 403);
        $request->validate([
            'tour_status' => 'required|in:assigned,in_progress,completed,cancelled',
            'incident_note' => 'nullable|string',
            'summary_report' => 'nullable|string',
        ]);

        if (in_array($request->tour_status, ['in_progress', 'completed']) && !app(\App\Services\NhomTour::class)->summary((int) $tourId, $groupNumber)->ready) {
            return back()->withErrors(['tour_status' => 'Đoàn chưa đủ số khách đã xác nhận tối thiểu để khởi hành.']);
        }
        $this->guideAssignments->saveTourStatus($tourId, $guideId, [
            'tour_status' => $request->input('tour_status'),
            'incident_note' => $request->input('incident_note'),
            'summary_report' => $request->input('summary_report'),
            'meeting_point' => $request->input('meeting_point'),
            'departure_date' => $request->input('departure_date'),
            'updated_at' => now(),
        ], $groupNumber);

        toastr()->success('Cập nhật trạng thái tour thành công.', 'Thông báo');
        return redirect()->route('guide.tour-detail', ['tourId' => $tourId, 'groupNumber' => $request->input('groupNumber', 1)]);
    }

    public function updateGuestStatus(Request $request, $tourId)
    {
        $guideId = $this->resolveGuideId($request);
        $bookingId = $request->input('booking_id');
        $groupNumber = (int) $request->input('groupNumber', 1);
        abort_unless($this->guideAssignments->getAssignedTourDetail($guideId, $tourId, $groupNumber), 403);
        abort_unless(DB::table('tbl_booking')->where('bookingId', $bookingId)->where('tourId', $tourId)->where('groupNumber', $groupNumber)->whereIn('bookingStatus', ['y', 'f'])->exists(), 403);
        $guestStatus = $request->input('guest_status');

        if (!$bookingId || !$guestStatus) {
            toastr()->error('Thông tin khách không hợp lệ.', 'Thông báo');
            return back();
        }

        $this->guideAssignments->saveGuestStatus($bookingId, $tourId, $guideId, [
            'guest_status' => $guestStatus,
            'note' => $request->input('guest_note'),
            'updated_at' => now(),
        ]);

        toastr()->success('Cập nhật trạng thái khách thành công.', 'Thông báo');
        return back();
    }

    public function profile(Request $request)
    {
        $guideName = $request->session()->get('guide');
        $guide = DB::table('tbl_admin')->where('username', $guideName)->first();

        return view('guide.ho-so', compact('guide', 'guideName'));
    }

    public function updateProfile(Request $request)
    {
        $guideName = $request->session()->get('guide');
        $fullName = $request->input('full_name');
        $email = $request->input('email');
        $phone = $request->input('phone');
        $address = $request->input('address');

        DB::table('tbl_admin')
            ->where('username', $guideName)
            ->update([
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'updated_at' => now(),
            ]);

        toastr()->success('Cập nhật thông tin cá nhân thành công.', 'Thông báo');
        return redirect()->route('guide.profile');
    }

    private function resolveGuideId(Request $request): int
    {
        $guideId = (int) $request->session()->get('guide_id', 0);
        $guideName = $request->session()->get('guide');

        if (empty($guideName)) {
            return $guideId;
        }

        $guide = DB::table('tbl_admin')
            ->where('username', $guideName)
            ->value('adminId');

        if ($guide) {
            $request->session()->put('guide_id', (int) $guide);
            return (int) $guide;
        }

        return 0;
    }
}
