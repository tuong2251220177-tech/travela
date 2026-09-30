<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TourRieng
{
    public const LABELS = ['pending' => 'Chờ tư vấn', 'quoted' => 'Đã báo giá', 'accepted' => 'Đã tạo đơn', 'cancelled' => 'Khách đã rút yêu cầu', 'declined' => 'Không thể tổ chức'];

    private function reject(string $message): void
    {
        throw ValidationException::withMessages(['private_tour' => $message]);
    }

    public function request(int $userId, array $data): int
    {
        return DB::transaction(function () use ($userId, $data) {
            $tour = DB::table('tbl_tours')->where('tourId', $data['tourId'])->lockForUpdate()->first();
            abort_unless($tour && $tour->availability && !$tour->privateRequestId, 404);
            $key = hash('sha256', $userId.':'.$data['token']);
            $existing = DB::table('tbl_private_tour_requests')->where('requestKey', $key)->first();
            if ($existing) return (int) $existing->id;
            return DB::table('tbl_private_tour_requests')->insertGetId([
                'userId' => $userId, 'tourId' => $tour->tourId, 'tourTitle' => $tour->title, 'requestKey' => $key,
                'fullName' => $data['fullName'], 'email' => $data['email'], 'phoneNumber' => $data['phoneNumber'], 'address' => $data['address'],
                'numAdults' => $data['numAdults'], 'numChildren' => $data['numChildren'], 'requestedDate' => $data['requestedDate'],
                'notes' => $data['notes'] ?? null, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }, 3);
    }

    public function quote(int $id, array $data): void
    {
        DB::transaction(function () use ($id, $data) {
            $request = DB::table('tbl_private_tour_requests')->where('id', $id)->lockForUpdate()->first();
            abort_unless($request, 404);
            if (!in_array($request->status, ['pending', 'quoted'])) $this->reject('Yêu cầu đã kết thúc, không thể thay đổi báo giá.');
            if ((int) $request->revision !== (int) $data['revision']) $this->reject('Yêu cầu đã thay đổi. Vui lòng tải lại trang.');
            $tour = DB::table('tbl_tours')->where('tourId', $request->tourId)->lockForUpdate()->first();
            abort_unless($tour && !$tour->privateRequestId, 404);
            $start = Carbon::parse($data['startDate']);
            $end = Carbon::parse($data['endDate']);
            $expiry = Carbon::parse($data['expiresAt']);
            if ($start->lte(now()) || $end->lt($start) || $expiry->lte(now()) || $expiry->gte($start) || (int) $data['quoteTotal'] < 1000) {
                $this->reject('Ngày đi, hạn báo giá hoặc tổng tiền không hợp lệ. Hạn báo giá phải trước giờ khởi hành.');
            }
            $snapshot = ['tour' => (array) $tour];
            foreach (['tbl_images', 'tbl_timeline'] as $table) $snapshot[$table] = DB::table($table)->where('tourId', $tour->tourId)->get()->map(fn ($row) => (array) $row)->all();
            DB::table('tbl_private_tour_requests')->where('id', $id)->update([
                'status' => 'quoted', 'revision' => $request->revision + 1, 'quoteTotal' => $data['quoteTotal'],
                'startDate' => $start, 'endDate' => $end, 'expiresAt' => $expiry, 'quoteNotes' => $data['quoteNotes'],
                'snapshot' => json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), 'updated_at' => now(),
            ]);
        }, 3);
    }

    public function close(int $id, ?int $userId, string $note = ''): void
    {
        DB::transaction(function () use ($id, $userId, $note) {
            $request = DB::table('tbl_private_tour_requests')->where('id', $id)->lockForUpdate()->first();
            abort_unless($request && ($userId === null || (int) $request->userId === $userId), 404);
            if (!in_array($request->status, ['pending', 'quoted'])) $this->reject('Yêu cầu đã được xử lý. Đơn đã tạo cần hủy trong chi tiết đơn.');
            DB::table('tbl_private_tour_requests')->where('id', $id)->update([
                'status' => $userId === null ? 'declined' : 'cancelled', 'quoteNotes' => $note ?: $request->quoteNotes,
                'revision' => $request->revision + 1, 'updated_at' => now(),
            ]);
        }, 3);
    }

    public function accept(int $id, int $userId, int $revision, string $payment, array $passengers, string $paymentPlan = 'deposit'): int
    {
        return DB::transaction(function () use ($id, $userId, $revision, $payment, $passengers, $paymentPlan) {
            $request = DB::table('tbl_private_tour_requests')->where('id', $id)->lockForUpdate()->first();
            abort_unless($request && (int) $request->userId === $userId, 404);
            if ($request->status === 'accepted') return (int) $request->bookingId;
            if ($request->status !== 'quoted' || (int) $request->revision !== $revision) $this->reject('Báo giá đã thay đổi hoặc chưa sẵn sàng. Vui lòng tải lại trang.');
            if (Carbon::parse($request->expiresAt)->lte(now()) || Carbon::parse($request->startDate)->lte(now())) $this->reject('Báo giá đã hết hạn. Vui lòng liên hệ admin để được báo giá lại.');
            $count = (int) $request->numAdults + (int) $request->numChildren;
            if (count($passengers) !== $count || !in_array($payment, ['bank-transfer', 'office-payment'], true)) $this->reject('Danh sách hành khách hoặc phương thức thanh toán không hợp lệ.');
            $snapshot = json_decode($request->snapshot, true, 512, JSON_THROW_ON_ERROR);
            $tour = $snapshot['tour'];
            unset($tour['tourId'], $tour['id']);
            // A separate, unpublished departure keeps its dates and guest manifest isolated.
            $tour = array_merge($tour, [
                'title' => Str::limit($request->tourTitle, 190, '').' · Riêng #'.$id,
                'privateRequestId' => $id, 'startDate' => $request->startDate, 'endDate' => $request->endDate,
                'availability' => true, 'quantity' => 0, 'minGuests' => $count, 'maxGuests' => $count,
                'priceAdult' => $request->quoteTotal / $count, 'priceChild' => $request->quoteTotal / $count,
            ]);
            if (array_key_exists('time', $tour)) {
                $nights = Carbon::parse($request->startDate)->startOfDay()->diffInDays(Carbon::parse($request->endDate)->startOfDay());
                $tour['time'] = ($nights + 1).' ngày '.$nights.' đêm';
                if (array_key_exists('duration', $tour)) $tour['duration'] = $nights + 1;
            }
            $tourId = DB::table('tbl_tours')->insertGetId($tour);
            foreach (['tbl_images', 'tbl_timeline'] as $table) {
                foreach ($snapshot[$table] as $row) {
                    $row = array_intersect_key($row, array_flip($table === 'tbl_images' ? ['imageURL', 'description'] : ['title', 'description']));
                    $row['tourId'] = $tourId;
                    DB::table($table)->insert($row);
                }
            }
            // Trusted quote amount is passed only from this server-side workflow.
            $booking = app(QuyTrinhDatTour::class)->createPrivate($userId, [
                'tourId' => $tourId, 'bookingToken' => (string) Str::uuid(), 'numAdults' => $request->numAdults,
                'numChildren' => $request->numChildren, 'payment' => $payment,
                'paymentPlan' => $paymentPlan,
            ], $passengers, (int) $request->quoteTotal, $id);
            DB::table('tbl_tours')->where('tourId', $tourId)->update(['availability' => false]);
            DB::table('tbl_private_tour_requests')->where('id', $id)->update(['status' => 'accepted', 'bookingId' => $booking->bookingId, 'updated_at' => now()]);
            return (int) $booking->bookingId;
        }, 3);
    }
}
