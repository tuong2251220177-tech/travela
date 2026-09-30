<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\admin\DuLieuTour;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class DieuKhienQuanLyTour extends Controller
{
    private $tours;

    private function rejectPrivateTour(int $id): void
    {
        $tour = \Illuminate\Support\Facades\DB::table('tbl_tours')->where('tourId', $id)->first();
        abort_if($tour && !empty($tour->privateRequestId), 422, 'Lịch trình riêng đã chốt theo báo giá, không sửa hoặc xóa qua quản lý tour ghép.');
    }

    public function __construct()
    {
        $this->tours = new DuLieuTour();
    }
    public function index()
    {
        $title = 'Quản lý Tours';

        $tours = $this->tours->getAllTours();
        return view('admin.danh-sach-tour', compact('title', 'tours'));
    }

    public function pageAddTours()
    {
        $title = 'Thêm Tours';

        return view('admin.them-tour', compact('title'));
    }

    public function uploadItineraryImage(Request $request)
    {
        $request->validate(['upload' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240|dimensions:min_width=1,min_height=1']);
        $file = $request->file('upload');
        $name = (string) \Illuminate\Support\Str::uuid() . '.' . $file->extension();
        $file->move(public_path('admin/assets/images/itinerary'), $name);
        return response()->json(['uploaded' => 1, 'fileName' => $name, 'url' => asset('admin/assets/images/itinerary/' . $name)]);
    }

    public function addTours(Request $request)
    {
        $this->validatePrices($request);
        $request->validate(['number' => 'required|integer|min:1|max:200', 'minGuests' => 'required|integer|min:1|lte:number']);
        $dates = $this->tourDates($request);
        $name = $request->input('name');
        $destination = $request->input('destination');
        $domain = $request->input('domain');
        $quantity = $request->input('number');
        $price_adult = $request->input('price_adult');
        $price_child = $request->input('price_child');
        $description = $request->input('description');

        $dataTours = [
            'title' => $name,

            'description' => $description,
            'images' => '',
            'quantity' => $quantity, 'maxGuests' => $quantity, 'minGuests' => $request->integer('minGuests'),
            'priceAdult' => $price_adult,
            'priceChild' => $price_child,

            'itinerary' => '',
            'destination' => $destination,
            'domain' => $domain,
            'availability' => 0,


        ];
        // dd($dataTours);

        $createTour = $this->tours->createTours(array_merge($dataTours, $dates));
        session(['pending_tour_id' => $createTour]);

        // dd($createTour);
        return response()->json([
            'success' => true,
            'message' => 'Tour added successfully!',
            'tourId' => $createTour
        ]);

    }

    public function addImagesTours(Request $request)
    {
        $request->merge(['tourId' => $request->input('tourId') ?: session('pending_tour_id')]);
        $request->validate([
            'tourId' => 'required|integer|exists:tbl_tours,tourId',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|dimensions:min_width=1,min_height=1|max:10240',
        ], ['image.max' => 'Ảnh không được vượt quá 10 MB.']);
        try {
            $image = $request->file('image');
            $tourId = $request->input('tourId') ?: session('pending_tour_id');

            if (!$tourId) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy tour cần lưu ảnh.'], 422);
            }

            // Kiểm tra xem file có hợp lệ không
            if (!$image->isValid()) {
                return response()->json(['success' => false, 'message' => 'Invalid file upload'], 400);
            }

            // Lấy tên gốc của file (không bao gồm đường dẫn)
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);

            // Lấy phần mở rộng của file
            $extension = $image->getClientOriginalExtension();

            // Tạo tên file mới: [original_name]_[timestamp].[extension]
            $filename = (string) \Illuminate\Support\Str::uuid() . '.' . $image->extension();

            // Resize hình ảnh về kích thước 400x350
            // Preserve the validated original without requiring GD to decode large images.

            // Di chuyển file vào thư mục đích
            $destinationPath = public_path('admin/assets/images/gallery-tours/');
            $image->move($destinationPath, $filename);

            // Tạo dữ liệu để lưu vào cơ sở dữ liệu
            $dataUpload = [
                'tourId' => $tourId,
                'imageURL' => $filename,
                'description' => $originalName
            ];

            // Lưu thông tin vào cơ sở dữ liệu
            $uploadImage = $this->tours->uploadImages($dataUpload);

            // Kiểm tra kết quả lưu trữ
            if ($uploadImage) {
                return response()->json([
                    'success' => true,
                    'message' => 'Image uploaded successfully',
                    'data' => [
                        'filename' => $filename,
                        'tourId' => $tourId
                    ]
                ], 200);
            }

            return response()->json(['success' => false, 'message' => 'Failed to save image data'], 500);
        } catch (\Exception $e) {
            // Xử lý lỗi bất ngờ
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function addTimeline(Request $request)
    {
        $tourId = $request->input('tourId') ?: session('pending_tour_id');

        if (!$tourId) {
            return redirect()
                ->route('admin.page-add-tours')
                ->with('error', 'Không tìm thấy ID của tour. Vui lòng tạo lại tour.');
        }

        // Tạo một mảng chứa các timeline
        $timelines = [];

        // Lặp qua tất cả các keys trong request để tìm các cặp `day-X` và `itinerary-X`
        foreach ($request->all() as $key => $value) {
            if (preg_match('/^day-(\d+)$/', $key, $matches)) {
                $dayNumber = $matches[1]; // Lấy số ngày (X) từ `day-X`

                // Tìm `itinerary-X` tương ứng
                $itineraryKey = "itinerary-{$dayNumber}";
                if ($request->has($itineraryKey)) {
                    $timelines[] = [
                        'tourId' => $tourId,
                        'title' => $value,
                        'description' => $request->input($itineraryKey),
                    ];
                }
            }
        }

        foreach ($timelines as $timeline) {
            $this->tours->addTimeLine($timeline);
        }
        $dataUpdate = [
            'availability' => 1
        ];

        $updateAvailability = $this->tours->updateTour($tourId, $dataUpdate);
        session()->forget('pending_tour_id');
        toastr()->success('Thêm tour thành công!');
        return redirect()->route('admin.page-add-tours');
    }

    public function getTourEdit(Request $request)
    {
        $this->rejectPrivateTour((int) $request->tourId);
        $tourId = $request->tourId;
        \Illuminate\Support\Facades\DB::table('tbl_tours')->where('tourId', $tourId)->lockForUpdate()->first();
        $largest = \Illuminate\Support\Facades\DB::table('tbl_booking')->where('tourId', $tourId)->where('bookingStatus', '!=', 'c')->selectRaw('SUM(numAdults + numChildren) as guests')->groupBy('groupNumber')->get()->max('guests');
        if ($largest > $request->integer('number')) throw \Illuminate\Validation\ValidationException::withMessages(['number' => 'Max không được nhỏ hơn số khách đã xếp trong một đoàn.']);

        $getTour = $this->tours->getTour($tourId);
        $getImages = $this->tours->getImages($tourId);
        $getTimeLine = $this->tours->getTimeLine($tourId);
        if ($getTour) {
            return response()->json([
                'success' => true,
                'tour' => $getTour,
                'images' => $getImages,
                'timeline' => $getTimeLine
            ]);
        } else {
            return response()->json([
                'success' => false,
            ]);
        }
    }

    public function uploadTempImagesTours(Request $request)
    {
        $request->validate([
            'tourId' => 'required|integer|exists:tbl_tours,tourId',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|dimensions:min_width=1,min_height=1|max:10240',
        ], ['image.max' => 'Ảnh không được vượt quá 10 MB.', 'image.image' => 'Tệp tải lên phải là ảnh hợp lệ.']);
        try {
            $image = $request->file('image');
            $tourId = $request->tourId;
        \Illuminate\Support\Facades\DB::table('tbl_tours')->where('tourId', $tourId)->lockForUpdate()->first();
        $largest = \Illuminate\Support\Facades\DB::table('tbl_booking')->where('tourId', $tourId)->where('bookingStatus', '!=', 'c')->selectRaw('SUM(numAdults + numChildren) as guests')->groupBy('groupNumber')->get()->max('guests');
        if ($largest > $request->integer('number')) throw \Illuminate\Validation\ValidationException::withMessages(['number' => 'Max không được nhỏ hơn số khách đã xếp trong một đoàn.']);

            // Kiểm tra xem file có hợp lệ không
            if (!$image->isValid()) {
                return response()->json(['success' => false, 'message' => 'Invalid file upload'], 400);
            }

            // Lấy tên gốc của file (không bao gồm đường dẫn)
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);

            // Lấy phần mở rộng của file
            $extension = $image->getClientOriginalExtension();

            // Tạo tên file mới: [original_name]_[timestamp].[extension]
            $filename = (string) \Illuminate\Support\Str::uuid() . '.' . $image->extension();

            // Resize hình ảnh về kích thước 400x350
            // Store the validated original; uploading must not depend on the GD extension.

            // Di chuyển file vào thư mục đích
            $destinationPath = public_path('admin/assets/images/gallery-tours/');
            $image->move($destinationPath, $filename);

            // Tạo dữ liệu để lưu vào cơ sở dữ liệu
            $dataUpload = [
                'tourId' => $tourId,
                'imageTempURL' => $filename,
            ];

            // Lưu thông tin vào cơ sở dữ liệu
            $uploadImage = $this->tours->uploadTempImages($dataUpload);

            // Kiểm tra kết quả lưu trữ
            if ($uploadImage) {
                return response()->json([
                    'success' => true,
                    'message' => 'Image uploaded successfully',
                    'data' => [
                        'filename' => $filename,
                        'tourId' => $tourId
                    ]
                ], 200);
            }

            return response()->json(['success' => false, 'message' => 'Failed to save image data'], 500);
        } catch (\Exception $e) {
            // Xử lý lỗi bất ngờ
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateTour(Request $request)
    {
        $this->rejectPrivateTour((int) $request->tourId);
        $this->validatePrices($request);
        $request->validate(['number' => 'required|integer|min:1|max:200', 'minGuests' => 'required|integer|min:1|lte:number']);
        $request->validate([
            'tourId' => 'required|integer|exists:tbl_tours,tourId',
            'images' => 'required|array|size:5',
            'images.*' => 'required|string|distinct|max:255',
        ], ['images.size' => 'Cần đủ 5 ảnh tải thành công trước khi lưu tour.']);
        $dates = $this->tourDates($request);
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $dates) {
        $allowedImages = $this->tours->getImages($request->tourId)->pluck('imageURL')
            ->merge(\Illuminate\Support\Facades\DB::table('tbl_temp_images')->where('tourId', $request->tourId)->pluck('imageTempURL'));
        foreach ($request->input('images') as $filename) {
            if (!$allowedImages->contains($filename)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['images' => 'Có ảnh chưa tải thành công hoặc không thuộc tour này. Vui lòng chọn lại ảnh.']);
            }
        }
        $tourId = $request->tourId;
        \Illuminate\Support\Facades\DB::table('tbl_tours')->where('tourId', $tourId)->lockForUpdate()->first();
        $largest = \Illuminate\Support\Facades\DB::table('tbl_booking')->where('tourId', $tourId)->where('bookingStatus', '!=', 'c')->selectRaw('SUM(numAdults + numChildren) as guests')->groupBy('groupNumber')->get()->max('guests');
        if ($largest > $request->integer('number')) throw \Illuminate\Validation\ValidationException::withMessages(['number' => 'Max không được nhỏ hơn số khách đã xếp trong một đoàn.']);
        $name = $request->input('name');
        $destination = $request->input('destination');
        $domain = $request->input('domain');
        $quantity = $request->input('number');
        $price_adult = $request->input('price_adult');
        $price_child = $request->input('price_child');
        $description = $request->input('description');

        $dataTours = [
            'title'       => $name,
            'description' => $description,
            'quantity' => $quantity, 'maxGuests' => $quantity, 'minGuests' => $request->integer('minGuests'),
            'priceAdult'  => $price_adult,
            'priceChild'  => $price_child,
            'destination' => $destination,
            'domain'      => $domain,
        ];

        $delete_timeline = $this->tours->deleteData($tourId, 'tbl_timeline');
        $delete_images = $this->tours->deleteData($tourId, 'tbl_images');

        $updateTour = $this->tours->updateTour($tourId, array_merge($dataTours, $dates));

        // Tạo mảng tạm để lưu tên ảnh
        $images = $request->input('images');  // Mảng các tên ảnh gửi lên từ request

        if ($images && is_array($images)) {
            foreach ($images as $image) {
                $dataUpload = [
                    'tourId' => $tourId,
                    'imageURL' => $image, 
                    'description' => $name  
                ];
                $this->tours->uploadImages($dataUpload);
            }
        }

        $timelines = $request->input('timeline');

        if ($timelines && is_array($timelines)) {
            foreach ($timelines as $timeline) {
                $data = [
                    'tourId' => $tourId,
                    'title' => $timeline['title'],
                    'description' => $timeline['itinerary']
                ];

                $this->tours->addTimeLine($data);  // Gọi phương thức addTimeLine()
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Sửa thành công!',
        ]);
        });

    }

    public function deleteTour(Request $request)
    {
        $this->rejectPrivateTour((int) $request->tourId);
        $request->validate(['tourId' => 'required|integer|min:1']);
        $tourId = $request->tourId;
        \Illuminate\Support\Facades\DB::table('tbl_tours')->where('tourId', $tourId)->lockForUpdate()->first();
        $largest = \Illuminate\Support\Facades\DB::table('tbl_booking')->where('tourId', $tourId)->where('bookingStatus', '!=', 'c')->selectRaw('SUM(numAdults + numChildren) as guests')->groupBy('groupNumber')->get()->max('guests');
        if ($largest > $request->integer('number')) throw \Illuminate\Validation\ValidationException::withMessages(['number' => 'Max không được nhỏ hơn số khách đã xếp trong một đoàn.']);

        $result = $this->tours->deleteTour($tourId);
        // Kiểm tra kết quả trả về từ Model
        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                  'tourId' => (int) $tourId
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ]);
        }
    }

    private function validatePrices(Request $request): void
    {
        $request->validate([
            'price_adult' => 'required|integer|min:1',
            'price_child' => 'required|integer|min:0',
        ], [
            'price_adult.integer' => 'Giá người lớn phải là số đồng nguyên, không nhập dấu chấm/phẩy. Ví dụ: 3590000 là 3.590.000 đồng.',
            'price_child.integer' => 'Giá trẻ em phải là số đồng nguyên, không nhập dấu chấm/phẩy. Ví dụ: 2490000 là 2.490.000 đồng.',
            'price_adult.min' => 'Giá người lớn phải lớn hơn 0 đồng.',
            'price_child.min' => 'Giá trẻ em không được âm.',
        ]);
    }

    private function tourDates(Request $request): array
    {
        $data = $request->validate([
            'start_date' => 'required|date_format:d/m/Y',
            'end_date' => 'required|date_format:d/m/Y|after_or_equal:start_date',
        ]);
        $start = Carbon::createFromFormat('!d/m/Y', $data['start_date']);
        $end = Carbon::createFromFormat('!d/m/Y', $data['end_date']);
        $nights = (int) $start->diffInDays($end);
        $duration = ($nights + 1) . ' ngày ' . $nights . ' đêm';
        return ['startDate' => $start->format('Y-m-d'), 'endDate' => $end->format('Y-m-d'), 'time' => $duration, 'duration' => $duration];
    }

}
