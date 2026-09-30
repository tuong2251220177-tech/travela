<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\admin\DuLieuDatTour;
use App\Models\admin\DuLieuPhanCongHuongDanVien;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class DieuKhienPhanCongHuongDanVien extends Controller
{
    protected $assignment;
    protected $bookings;

    public function __construct()
    {
        $this->assignment = new DuLieuPhanCongHuongDanVien();
        $this->bookings = new DuLieuDatTour();
    }

    public function index()
    {
        $title = 'Phân công hướng dẫn viên';
        $guides = $this->assignment->getGuides();
        $tours = $this->assignment->getTours();
        $assignments = $this->assignment->getAssignments();

        return view('admin.phan-cong-huong-dan-vien', compact('title', 'guides', 'tours', 'assignments'));
    }

    public function assign(Request $request)
    {
        $parts = explode(':', (string) $request->input('tour_id'));
        $request->merge(['tour_id' => $parts[0], 'groupNumber' => $parts[1] ?? 1]);
        $request->validate([
            'guide_id' => 'required',
            'tour_id' => 'required',
            'groupNumber' => 'required|integer|min:1',
            'tour_status' => 'nullable|in:assigned,in_progress,completed,cancelled',
            'meeting_point' => 'nullable|string',
            'departure_date' => 'nullable|date',
        ]);

        abort_unless(DB::table('tbl_booking')->where('tourId', $request->tour_id)->where('groupNumber', $request->groupNumber)->where('bookingStatus', '!=', 'c')->exists(), 422, 'Đoàn không tồn tại.');
        if (in_array($request->tour_status, ['in_progress', 'completed']) && !app(\App\Services\NhomTour::class)->summary((int) $request->tour_id, $request->integer('groupNumber'))->ready) {
            return back()->withErrors(['tour_status' => 'Đoàn chưa đủ số khách đã xác nhận tối thiểu để khởi hành.']);
        }
        $result = $this->assignment->assignTour([
            'groupNumber' => $request->integer('groupNumber'),
            'guide_id' => $request->input('guide_id'),
            'tour_id' => $request->input('tour_id'),
            'meeting_point' => $request->input('meeting_point'),
            'departure_date' => $request->input('departure_date'),
            'tour_status' => $request->input('tour_status', 'assigned'),
            'incident_note' => $request->input('incident_note'),
        ]);

        if ($result) {
            toastr()->success('Phân công tour cho hướng dẫn viên thành công.', 'Thông báo');
            return redirect()->route('admin.guide-assignment');
        }

        toastr()->error('Không thể phân công tour. Vui lòng kiểm tra bảng dữ liệu.', 'Thông báo');
        return redirect()->route('admin.guide-assignment');
    }

    public function sendManifest(Request $request)
    {
        $request->validate([
            'guide_id' => 'required|integer',
            'tour_id' => 'required|integer',
            'format' => 'required|in:pdf,xls',
        ]);

        $guide = DB::table('tbl_admin')->where('adminId', $request->guide_id)->first();
        $tour = DB::table('tbl_tours')->where('tourId', $request->tour_id)->first();
        $rows = $this->bookings->getApprovedPassengerManifest($request->tour_id, (int) $request->input('groupNumber', 1));

        if (!$guide || !$tour || !$guide->email) {
            toastr()->error('Hướng dẫn viên hoặc email chưa hợp lệ.', 'Thông báo');
            return back();
        }

        if ($rows->isEmpty()) {
            toastr()->error('Tour chưa có booking đã duyệt để gửi.', 'Thông báo');
            return back();
        }

        if ($request->format === 'pdf') {
            $attachment = Pdf::loadView('exports.danh-sach-doan-pdf', compact('tour', 'rows'))->output();
            $filename = 'tour-' . $tour->tourId . '-passengers.pdf';
            $mime = 'application/pdf';
        } else {
            $attachment = view('exports.danh-sach-doan-excel', compact('tour', 'rows'))->render();
            $filename = 'tour-' . $tour->tourId . '-passengers.xls';
            $mime = 'application/vnd.ms-excel';
        }

        try {
            Mail::send('admin.emails.danh-sach-doan', compact('tour'), function ($message) use ($guide, $tour, $attachment, $filename, $mime) {
                $message->to($guide->email)
                    ->subject('Danh sách hành khách tour ' . $tour->title)
                    ->attachData($attachment, $filename, ['mime' => $mime]);
            });
        } catch (\Throwable $exception) {
            toastr()->error('Không thể gửi manifest: ' . $exception->getMessage(), 'Thông báo');
            return back();
        }

        toastr()->success('Đã gửi manifest cho hướng dẫn viên.', 'Thông báo');
        return back();
    }

    public function addGuide(Request $request)
    {
        $request->validate([
            'username' => 'required|string|min:3|unique:tbl_admin,username',
            'password' => 'required|string|min:3',
            'full_name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'description' => 'nullable|string|max:500',
        ]);

        $avatarFileName = null;
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $avatarFileName = 'guide_' . time() . '_' . uniqid() . '.' . $avatar->getClientOriginalExtension();
            $uploadPath = public_path('admin/assets/images/user-profile');

            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $avatar->move($uploadPath, $avatarFileName);
        }

        $result = $this->assignment->createGuide([
            'username' => $request->input('username'),
            'password' => $request->input('password'),
            'full_name' => $request->input('full_name'),
            'email' => $request->input('email'),
            'avatar' => $avatarFileName,
            'description' => $request->input('description'),
        ]);

        if ($result) {
            toastr()->success('Thêm hướng dẫn viên thành công.', 'Thông báo');
            return redirect()->route('admin.guide-assignment');
        }

        toastr()->error('Tên hướng dẫn viên đã tồn tại hoặc dữ liệu không hợp lệ.', 'Thông báo');
        return redirect()->route('admin.guide-assignment');
    }

    public function deleteGuide($id)
    {
        app(\App\Services\QuanLyHuongDanVien::class)->change((int) $id, null);
        return back()->with('success', 'Đã xóa tài khoản chưa có lịch sử phân công.');
    }

    public function updateGuide(Request $request, $id)
    {
        $guide = \App\Services\QuanLyHuongDanVien::guides(DB::table('tbl_admin'))->where('adminId', $id)->first();

        if (!$guide) {
            toastr()->error('Hướng dẫn viên không tồn tại.', 'Thông báo');
            return redirect()->route('admin.guide-assignment');
        }

        if (strtolower((string) $guide->username) === 'admin') {
            toastr()->error('Không thể sửa tài khoản admin chính.', 'Thông báo');
            return redirect()->route('admin.guide-assignment');
        }

        $request->validate([
            'username' => 'nullable|string|min:3|unique:tbl_admin,username,' . $id . ',adminId',
            'full_name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'description' => 'nullable|string|max:500',
        ]);

        $updateData = [];

        if ($request->filled('username')) {
            $updateData['username'] = $request->input('username');
        }

        if ($request->filled('full_name')) {
            $updateData['full_name'] = $request->input('full_name');
        }

        if ($request->filled('email')) {
            $updateData['email'] = $request->input('email');
        }

        if ($request->filled('description')) {
            $updateData['description'] = $request->input('description');
        }

        if (Schema::hasColumn('tbl_admin', 'avatar') && $request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $avatarFileName = 'guide_' . time() . '_' . uniqid() . '.' . $avatar->getClientOriginalExtension();
            $uploadPath = public_path('admin/assets/images/user-profile');

            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $avatar->move($uploadPath, $avatarFileName);
            $updateData['avatar'] = $avatarFileName;
        }

        if (Schema::hasColumn('tbl_admin', 'updated_at')) {
            $updateData['updated_at'] = now();
        }

        if (!empty($updateData)) {
            DB::table('tbl_admin')->where('adminId', $id)->update($updateData);
            toastr()->success('Cập nhật hướng dẫn viên thành công.', 'Thông báo');
        } else {
            toastr()->info('Không có dữ liệu nào được thay đổi.', 'Thông báo');
        }

        return back();
    }
}
