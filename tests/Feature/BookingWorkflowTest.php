<?php

namespace Tests\Feature;

use App\Services\QuyTrinhDatTour;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BookingWorkflowTest extends TestCase
{
    private QuyTrinhDatTour $workflow;
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('tbl_tours', function (Blueprint $t) {
            $t->increments('tourId'); $t->string('title')->default('Tour thử');
            $t->boolean('availability')->default(true); $t->integer('quantity');
            $t->double('priceAdult'); $t->double('priceChild');
            $t->dateTime('startDate'); $t->dateTime('endDate');
        });
        Schema::create('tbl_booking', function (Blueprint $t) {
            $t->increments('bookingId'); $t->integer('tourId'); $t->integer('userId');
            foreach (['fullName', 'email', 'phoneNumber', 'address', 'bookingStatus', 'depositStatus'] as $name) $t->string($name);
            $t->integer('numAdults'); $t->integer('numChildren');
            $t->double('totalPrice'); $t->double('depositAmount');
            $t->dateTime('bookingDate'); $t->dateTime('depositDueAt'); $t->dateTime('depositPaidAt')->nullable();
        });
        (require database_path('migrations/2026_09_16_000001_add_booking_workflow_fields.php'))->up();
        Schema::create('tbl_checkout', function (Blueprint $t) {
            $t->increments('checkoutId'); $t->integer('bookingId');
            $t->string('paymentMethod'); $t->string('paymentStatus'); $t->double('amount');
            $t->string('transactionId')->nullable(); $t->dateTime('paymentDate')->nullable();
        });
        Schema::create('tbl_booking_passengers', function (Blueprint $t) {
            $t->increments('id'); $t->integer('bookingId'); $t->integer('passengerNumber');
            foreach (['passengerType', 'fullName', 'email', 'phoneNumber', 'address'] as $name) $t->string($name);
            $t->timestamps();
        });
        DB::table('tbl_tours')->insert(['tourId' => 1, 'quantity' => 3, 'priceAdult' => 1690000, 'priceChild' => 1200000, 'startDate' => now()->addDays(20), 'endDate' => now()->addDays(22)]);
        (require database_path('migrations/2026_09_17_000002_add_booking_payment_receipts.php'))->up();
        (require database_path('migrations/2026_09_17_000003_create_transfer_proofs.php'))->up();
        (require database_path('migrations/2026_09_17_000004_create_discount_codes.php'))->up();
        (require database_path('migrations/2026_09_17_000005_add_coupon_visibility_and_user_limit.php'))->up();
        (require database_path('migrations/2026_09_23_000001_add_tour_groups.php'))->up();
        (require database_path('migrations/2026_09_29_000002_add_booking_transport_options.php'))->up();
        (require database_path('migrations/2026_09_29_000003_add_booking_payment_plan.php'))->up();
        DB::table('tbl_tours')->update(['minGuests' => 1, 'maxGuests' => 3]);
        $this->workflow = app(QuyTrinhDatTour::class);
    }

    private function proofImage()
    {
        return \Illuminate\Http\UploadedFile::fake()->createWithContent('proof.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aL1sAAAAASUVORK5CYII='));
    }

    public function test_full_group_opens_next_group_without_closing_tour(): void
    {
        DB::table('tbl_tours')->update(['quantity' => 0]);
        $first = $this->workflow->create(10, $this->data(['numAdults' => 3]), array_fill(0, 3, $this->passengers()[0]));
        $secondData = $this->data();
        $second = $this->workflow->create(11, $secondData, $this->passengers());
        $this->assertSame(1, (int) $first->groupNumber);
        $this->assertSame(2, (int) $second->groupNumber);
        $this->assertSame($second->bookingId, $this->workflow->create(11, $secondData, $this->passengers())->bookingId);
        $this->assertEquals(1, DB::table('tbl_tours')->value('availability'));
        $this->assertSame(2, DB::table('tbl_booking')->count());
    }

    public function test_many_parties_open_multiple_groups_without_stock_limit(): void
    {
        DB::table('tbl_tours')->update(['quantity' => 0, 'minGuests' => 5, 'maxGuests' => 30]);
        $assigned = [];
        foreach ([28, 5, 25, 7] as $count) {
            $booking = $this->workflow->create(10, $this->data(['numAdults' => $count]), array_fill(0, $count, $this->passengers()[0]));
            $assigned[] = (int) $booking->groupNumber;
        }
        $this->assertSame([1, 2, 2, 3], $assigned);
        $this->assertSame(65, DB::table('tbl_booking_passengers')->count());
        $groups = app(\App\Services\NhomTour::class);
        $this->assertSame(28, $groups->summary(1, 1)->registered);
        $this->assertSame(30, $groups->summary(1, 2)->registered);
        $this->assertSame(7, $groups->summary(1, 3)->registered);
        $this->assertEquals(1, DB::table('tbl_tours')->value('availability'));
        $this->assertEquals(0, DB::table('tbl_tours')->value('quantity'));
        $stats = app(\App\Models\admin\DuLieuBangDieuKhien::class)->getMostTourBooked()->first();
        $this->assertEquals(3, $stats->group_count);
        $this->assertEquals(65, $stats->booked_quantity);
    }

    public function test_under_minimum_group_accepts_booking_but_is_not_ready(): void
    {
        DB::table('tbl_tours')->update(['quantity' => 0, 'minGuests' => 5, 'maxGuests' => 30]);
        $booking = $this->workflow->create(10, $this->data(), $this->passengers());
        $this->workflow->receiveMoney($booking->bookingId);
        $this->workflow->transition($booking->bookingId, 'y');
        $summary = app(\App\Services\NhomTour::class)->summary(1, 1);
        $this->assertSame(1, $summary->confirmed);
        $this->assertFalse($summary->ready);
    }

    public function test_zero_legacy_quantity_does_not_show_sold_out_on_tour_card(): void
    {
        $tour = (object) ['tourId' => 1, 'images' => [], 'quantity' => 0, 'title' => 'Tour Da Lat', 'time' => '3 ngay', 'destination' => 'Da Lat', 'priceAdult' => 1000000];
        $html = view('clients.partials.the-tour-trang-chu', compact('tour'))->render();
        $this->assertStringNotContainsString('Đã hết chỗ', $html);
        $this->assertStringContainsString('Nhận đăng ký theo đoàn', $html);
        $this->assertStringContainsString(route('tour-detail', ['id' => 1]), $html);
    }

    public function test_party_stays_together_and_cancellation_releases_group_capacity(): void
    {
        $party = array_fill(0, 2, $this->passengers()[0]);
        $first = $this->workflow->create(10, $this->data(['numAdults' => 2]), $party);
        $second = $this->workflow->create(11, $this->data(['numAdults' => 2]), $party);
        $this->assertSame(2, (int) $second->groupNumber);
        $this->workflow->requestCancellation($first->bookingId, 10, 'Không thể tham gia');
        $this->workflow->reviewCancellation($first->bookingId, true, 'Đồng ý');
        // A cancelled group can accept a new party without moving existing bookings.
        $third = $this->workflow->create(12, $this->data(), $this->passengers());
        $this->assertSame(2, (int) $third->groupNumber);
        $this->assertSame(2, (int) DB::table('tbl_booking')->where('bookingId', $second->bookingId)->value('groupNumber'));
    }

    public function test_oversized_party_is_rejected_without_writes(): void
    {
        try {
            $this->workflow->create(10, $this->data(['numAdults' => 4]), array_fill(0, 4, $this->passengers()[0]));
            $this->fail('Party must fit one group');
        } catch (ValidationException $e) {
            $this->assertSame(0, DB::table('tbl_booking')->count());
        }
    }

    public function test_minimum_counts_confirmed_guests_only(): void
    {
        DB::table('tbl_tours')->update(['minGuests' => 2]);
        $booking = $this->workflow->create(10, $this->data(['numAdults' => 2]), array_fill(0, 2, $this->passengers()[0]));
        $groups = app(\App\Services\NhomTour::class);
        $this->assertFalse($groups->summary(1, 1)->ready);
        $this->workflow->receiveMoney($booking->bookingId);
        $this->workflow->transition($booking->bookingId, 'y');
        $this->assertTrue($groups->summary(1, 1)->ready);
        $this->assertFalse($groups->summary(1, 2)->ready);
    }

    public function test_guide_guest_list_is_scoped_to_one_group(): void
    {
        Schema::create('tbl_users', function (Blueprint $t) { $t->integer('userId'); $t->string('username'); });
        $first = $this->workflow->create(10, $this->data(['numAdults' => 3]), array_fill(0, 3, $this->passengers()[0]));
        $second = $this->workflow->create(11, $this->data(), $this->passengers());
        DB::table('tbl_booking')->update(['bookingStatus' => 'y']);
        $model = new \App\Models\guide\DuLieuPhanCongHuongDanVien();
        $this->assertSame([$first->bookingId], $model->getGuestsForTour(1, 1, 1)->pluck('bookingId')->all());
        $this->assertSame([$second->bookingId], $model->getGuestsForTour(1, 2, 2)->pluck('bookingId')->all());
    }

    public function test_deposit_clears_pending_report_and_new_report_preserves_confirmation(): void
    {
        $booking = $this->workflow->create(10, $this->data(), $this->passengers());
        $this->workflow->reportTransfer($booking->bookingId, 10);
        $token = (string) Str::uuid();
        $this->workflow->recordPayment($booking->bookingId, (int) $booking->depositAmount, 'bank-transfer', 'staff', '', 'Cọc', $token);
        $this->assertNull(DB::table('tbl_booking')->value('transferReportedAt'));
        $this->workflow->transition($booking->bookingId, 'y');
        $this->workflow->reportTransfer($booking->bookingId, 10);
        $this->assertNotNull(DB::table('tbl_booking')->value('transferReportedAt'));
        $this->assertSame('y', DB::table('tbl_booking')->value('bookingStatus'));
        // A duplicate receipt must not clear a later report.
        $this->workflow->recordPayment($booking->bookingId, (int) $booking->depositAmount, 'bank-transfer', 'staff', '', 'Cọc', $token);
        $this->assertNotNull(DB::table('tbl_booking')->value('transferReportedAt'));
    }

    public function test_transfer_proof_is_private_and_does_not_mark_payment_paid(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        $booking = $this->workflow->create(10, $this->data(), $this->passengers());
        $this->withSession(['username' => 'customer', 'userId' => 10])->post('/report-transfer', [
            'bookingId' => $booking->bookingId, 'proof' => $this->proofImage(),
        ])->assertRedirect()->assertSessionHasNoErrors();
        $proof = DB::table('tbl_transfer_proofs')->first();
        $this->assertNotNull($proof);
        \Illuminate\Support\Facades\Storage::disk('local')->assertExists($proof->path);
        $this->assertSame('n', DB::table('tbl_checkout')->value('paymentStatus'));
        $this->get('/transfer-proof/' . $proof->id)->assertOk();
        $this->withSession(['username' => 'other', 'userId' => 11])->get('/transfer-proof/' . $proof->id)->assertForbidden();
        $this->withSession(['admin' => 'staff'])->get('/transfer-proof/' . $proof->id)->assertOk();
    }

    public function test_invalid_transfer_proof_is_rejected_without_writes(): void
    {
        $booking = $this->workflow->create(10, $this->data(), $this->passengers());
        $this->withSession(['username' => 'customer', 'userId' => 10])->postJson('/report-transfer', [
            'bookingId' => $booking->bookingId,
            'proof' => \Illuminate\Http\UploadedFile::fake()->createWithContent('proof.png', '<script>invalid</script>'),
        ])->assertUnprocessable()->assertJsonValidationErrors('proof');
        $this->assertSame(0, DB::table('tbl_transfer_proofs')->count());
    }

    private function data(array $extra = []): array
    {
        return array_replace(['tourId' => 1, 'numAdults' => 1, 'numChildren' => 0, 'bookingToken' => (string) Str::uuid(), 'payment' => 'bank-transfer'], $extra);
    }

    public function test_admin_generates_unique_codes_with_requested_rules(): void
    {
        Schema::create('tbl_contact', function (Blueprint $t) { $t->increments('contactId'); $t->string('isReply'); });
        $this->withSession(['admin' => 'staff'])->post('/admin/coupons', [
            'quantity' => 3, 'prefix' => 'SUMMER', 'type' => 'percent', 'value' => 15,
            'isPublic' => 0, 'perUserLimit' => 1,
            'minTotal' => 1000000, 'maxDiscount' => 200000, 'usageLimit' => 2,
            'expiresAt' => now()->addDays(10)->toDateString(),
        ])->assertRedirect()->assertSessionHasNoErrors();
        $codes = DB::table('tbl_coupons')->where('createdBy', 'staff')->get();
        $this->assertCount(3, $codes);
        $this->assertCount(3, $codes->pluck('code')->unique());
        $this->assertStringStartsWith('SUMMER-', $codes[0]->code);
        $this->assertEquals(200000, app(\App\Services\DichVuMaGiamGia::class)->discount($codes[0]->code, 2000000, false, 10));
        $this->assertEquals(0, $codes[0]->isPublic);
        $this->assertEquals(1, $codes[0]->perUserLimit);
        $this->get('/admin/coupons')->assertOk()->assertSee($codes[0]->code);
    }

    public function test_coupon_limit_and_duplicate_booking_do_not_double_consume(): void
    {
        DB::table('tbl_coupons')->where('code', 'DISCOUNT10')->update(['usageLimit' => 1]);
        $data = $this->data(['couponCode' => 'discount10']);
        $this->workflow->create(10, $data, $this->passengers());
        $this->workflow->create(10, $data, $this->passengers());
        $this->assertEquals(1, DB::table('tbl_coupons')->where('code', 'DISCOUNT10')->value('usedCount'));
        $this->assertEquals(169000, DB::table('tbl_booking')->value('discountAmount'));
        $this->expectException(ValidationException::class);
        $this->workflow->create(11, $this->data(['couponCode' => 'DISCOUNT10']), $this->passengers());
    }

    public function test_private_coupon_user_limit_is_enforced_on_preview_and_booking(): void
    {
        DB::table('tbl_coupons')->where('code', 'DISCOUNT10')->update(['isPublic' => false, 'perUserLimit' => 1]);
        $data = $this->data(['couponCode' => 'discount10']);
        $first = $this->workflow->create(10, $data, $this->passengers());
        $this->workflow->create(10, $data, $this->passengers());
        $this->assertSame(1, DB::table('tbl_booking')->count());
        $this->withSession(['username' => 'customer', 'userId' => 10])->postJson('/booking/coupon-preview', [
            'tourId' => 1, 'adults' => 1, 'children' => 0, 'code' => 'DISCOUNT10',
        ])->assertUnprocessable()->assertJsonValidationErrors('couponCode');
        try {
            $this->workflow->create(10, $this->data(['couponCode' => 'DISCOUNT10']), $this->passengers());
            $this->fail('User limit must block a second booking');
        } catch (ValidationException $e) { $this->assertSame(1, DB::table('tbl_booking')->count()); }
        $this->workflow->create(11, $this->data(['couponCode' => 'DISCOUNT10']), $this->passengers());
        $this->assertSame(2, DB::table('tbl_booking')->count());
        $this->workflow->requestCancellation($first->bookingId, 10, 'Không thể tham gia');
        $this->workflow->reviewCancellation($first->bookingId, true, 'Đồng ý hủy đơn');
        $this->expectException(ValidationException::class);
        app(\App\Services\DichVuMaGiamGia::class)->discount('DISCOUNT10', 1690000, false, 10);
    }

    public function test_admin_can_change_visibility_but_user_limit_is_always_one(): void
    {
        $id = DB::table('tbl_coupons')->where('code', 'DISCOUNT10')->value('id');
        $this->withSession(['admin' => 'staff'])->post('/admin/coupons/' . $id . '/settings', [
            'isPublic' => 0, 'perUserLimit' => 1,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $coupon = DB::table('tbl_coupons')->where('id', $id)->first();
        $this->assertEquals(0, $coupon->isPublic);
        $this->assertEquals(1, $coupon->perUserLimit);
        $this->postJson('/admin/coupons/' . $id . '/settings', ['isPublic' => 1, 'perUserLimit' => 2])
            ->assertUnprocessable()->assertJsonValidationErrors('perUserLimit');
        $this->postJson('/admin/coupons/' . $id . '/settings', ['isPublic' => 1, 'perUserLimit' => 0])
            ->assertUnprocessable()->assertJsonValidationErrors('perUserLimit');
    }

    public function test_coupon_preview_rejects_reapply_in_same_session_after_activation(): void
    {
        $this->withSession(['username' => 'customer', 'userId' => 10])->postJson('/booking/coupon-preview', [
            'tourId' => 1, 'adults' => 1, 'children' => 0, 'code' => 'DISCOUNT10',
        ])->assertOk()->assertJsonPath('code', 'DISCOUNT10');

        $this->withSession(['username' => 'customer', 'userId' => 10])->postJson('/booking/coupon-preview', [
            'tourId' => 1, 'adults' => 1, 'children' => 0, 'code' => 'DISCOUNT10',
        ])->assertUnprocessable()->assertJsonValidationErrors('couponCode');
    }

    public function test_coupon_preview_uses_server_price_and_does_not_consume_usage(): void
    {
        DB::table('tbl_coupons')->where('code', 'DISCOUNT10')->update(['type' => 'fixed', 'value' => 200000]);
        $this->withSession(['username' => 'customer', 'userId' => 10])->postJson('/booking/coupon-preview', [
            'tourId' => 1, 'adults' => 1, 'children' => 0, 'code' => 'discount10', 'subtotal' => 1,
        ])->assertOk()->assertJsonPath('discount', 200000)->assertJsonPath('total', 1490000);
        $this->assertEquals(0, DB::table('tbl_coupons')->where('code', 'DISCOUNT10')->value('usedCount'));
    }

    public function test_full_payment_selection_requires_actual_receipt_before_marking_paid(): void
    {
        $booking = $this->workflow->create(10, $this->data(['paymentPlan' => 'full']), $this->passengers());
        $this->assertSame('full', $booking->paymentPlan);
        $this->assertSame('n', DB::table('tbl_checkout')->value('paymentStatus'));
        $this->assertEquals(0, $booking->amountPaid);
        $this->workflow->receiveMoney($booking->bookingId);
        $this->assertSame('y', DB::table('tbl_checkout')->value('paymentStatus'));
        $this->assertEquals($booking->totalPrice, DB::table('tbl_booking')->value('amountPaid'));
        $this->assertSame('paid', DB::table('tbl_booking')->value('depositStatus'));
        $this->assertNotEquals('y', DB::table('tbl_booking')->value('bookingStatus'));
    }

    public function test_transport_fee_uses_server_price_and_is_saved_with_deposit(): void
    {
        $option = DB::table('tbl_tour_transport_options')->first();
        $booking = $this->workflow->create(10, $this->data(['numAdults' => 1, 'numChildren' => 1,
            'transportOptionId' => $option->id, 'transportFee' => 1, 'totalPrice' => 1]), array_fill(0, 2, $this->passengers()[0]));
        $this->assertEquals(400000, $booking->transportFee);
        $this->assertEquals(3290000, $booking->totalPrice);
        $this->assertEquals(658000, $booking->depositAmount);
        $this->assertEquals(3290000, DB::table('tbl_checkout')->value('amount'));
        DB::table('tbl_tour_transport_options')->where('id', $option->id)->update(['surcharge' => 999999]);
        $this->assertEquals(400000, DB::table('tbl_booking')->value('transportFee'));
    }

    public function test_customer_booking_form_submits_transport_choice_to_server(): void
    {
        $option = DB::table('tbl_tour_transport_options')->first();
        $this->withSession(['username' => 'customer', 'userId' => 10])->post('/create-booking', [
            'tourId' => 1, 'numAdults' => 1, 'numChildren' => 0,
            'transportOptionId' => $option->id, 'transportFee' => 0, 'totalPrice' => 1,
            'paymentPlan' => 'full',
            'bookingToken' => (string) Str::uuid(), 'payment' => 'office-payment', 'agree' => 1,
            'fullName' => 'Khách thử', 'email' => 'test@example.com', 'tel' => '0900000000', 'address' => 'Đà Nẵng',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $booking = DB::table('tbl_booking')->first();
        $this->assertEquals($option->id, $booking->transportOptionId);
        $this->assertEquals(1890000, $booking->totalPrice);
        $this->assertSame('full', $booking->paymentPlan);
    }

    public function test_different_transport_choices_do_not_share_a_group(): void
    {
        $option = DB::table('tbl_tour_transport_options')->first();
        $standard = $this->workflow->create(10, $this->data(), $this->passengers());
        $upgrade = $this->workflow->create(11, $this->data(['transportOptionId' => $option->id]), $this->passengers());
        $another = $this->workflow->create(12, $this->data(['transportOptionId' => $option->id]), $this->passengers());
        $this->assertEquals(1, $standard->groupNumber);
        $this->assertEquals(2, $upgrade->groupNumber);
        $this->assertEquals(2, $another->groupNumber);
        $this->assertEquals(0, $standard->transportFee);
    }

    public function test_inactive_or_other_tour_transport_cannot_create_booking(): void
    {
        $option = DB::table('tbl_tour_transport_options')->first();
        foreach ([['active' => false], ['active' => true, 'tourId' => 999]] as $changes) {
            DB::table('tbl_tour_transport_options')->where('id', $option->id)->update($changes);
            try {
                $this->workflow->create(10, $this->data(['transportOptionId' => $option->id]), $this->passengers());
                $this->fail('Invalid transport accepted');
            } catch (ValidationException $e) { $this->assertArrayHasKey('transportOptionId', $e->errors()); }
        }
        $this->assertSame(0, DB::table('tbl_booking')->count());
    }

    public function test_coupon_does_not_discount_transport_and_preview_matches_booking(): void
    {
        $option = DB::table('tbl_tour_transport_options')->first();
        DB::table('tbl_coupons')->where('code', 'DISCOUNT10')->update(['type' => 'percent', 'value' => 10, 'maxDiscount' => null]);
        $preview = $this->withSession(['username' => 'customer', 'userId' => 10])->postJson('/booking/coupon-preview', [
            'tourId' => 1, 'adults' => 1, 'children' => 0, 'code' => 'DISCOUNT10', 'transportOptionId' => $option->id,
        ])->assertOk()->assertJsonPath('discount', 169000)->assertJsonPath('total', 1721000);
        $booking = $this->workflow->create(10, $this->data(['transportOptionId' => $option->id, 'couponCode' => 'DISCOUNT10']), $this->passengers());
        $this->assertEquals($preview->json('total'), $booking->totalPrice);
    }

    public function test_admin_can_configure_transport_and_fee_per_booking_is_only_added_once(): void
    {
        $this->withSession(['admin' => 'staff'])->post('/admin/tours/1/transport', [
            'name' => 'Xe cao cấp', 'surcharge' => 500000, 'chargeUnit' => 'booking',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $option = DB::table('tbl_tour_transport_options')->where('name', 'Xe cao cấp')->first();
        $booking = $this->workflow->create(10, $this->data(['numAdults' => 2, 'transportOptionId' => $option->id]), array_fill(0, 2, $this->passengers()[0]));
        $this->assertEquals(500000, $booking->transportFee);
        $this->assertEquals(3880000, $booking->totalPrice);
        $this->postJson('/admin/tours/1/transport/' . $option->id, ['surcharge' => -1, 'chargeUnit' => 'booking', 'active' => 1])
            ->assertUnprocessable()->assertJsonValidationErrors('surcharge');
        $this->post('/admin/tours/1/transport/' . $option->id, ['surcharge' => 600000, 'chargeUnit' => 'person', 'active' => 0])->assertRedirect();
        $this->assertEquals(0, DB::table('tbl_tour_transport_options')->where('id', $option->id)->value('active'));
    }

    public function test_old_unlimited_coupon_is_still_single_use_per_account(): void
    {
        DB::table('tbl_coupons')->where('code', 'DISCOUNT10')->update(['perUserLimit' => null]);
        $this->workflow->create(10, $this->data(['couponCode' => 'DISCOUNT10']), $this->passengers());
        foreach ([null, 5] as $oldLimit) {
            DB::table('tbl_coupons')->where('code', 'DISCOUNT10')->update(['perUserLimit' => $oldLimit]);
            try {
                $this->workflow->create(10, $this->data(['couponCode' => 'DISCOUNT10']), $this->passengers());
                $this->fail('A previously used coupon must be rejected regardless of old settings');
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('couponCode', $e->errors());
            }
        }
        $this->workflow->create(11, $this->data(['couponCode' => 'DISCOUNT10']), $this->passengers());
        $this->assertSame(2, DB::table('tbl_booking')->count());
        $this->assertEquals(2, DB::table('tbl_coupons')->where('code', 'DISCOUNT10')->value('usedCount'));
    }

    public function test_cancel_then_reapply_preview_does_not_accumulate_or_consume_discount(): void
    {
        $data = ['tourId' => 1, 'adults' => 1, 'children' => 0, 'code' => 'DISCOUNT10'];
        $first = $this->withSession(['username' => 'customer', 'userId' => 10])
            ->postJson('/booking/coupon-preview', $data)->assertOk()->json();
        $this->postJson('/booking/coupon-clear', ['code' => 'DISCOUNT10'])->assertOk();
        $this->postJson('/booking/coupon-preview', $data)->assertOk()
            ->assertJsonPath('discount', $first['discount'])->assertJsonPath('total', $first['total']);
        $this->assertEquals(0, DB::table('tbl_coupons')->where('code', 'DISCOUNT10')->value('usedCount'));
        $this->assertSame(0, DB::table('tbl_booking')->count());
    }

    public function test_coupon_rejects_inactive_expired_and_below_minimum(): void
    {
        foreach ([['active' => false], ['active' => true, 'expiresAt' => now()->subDay()], ['expiresAt' => null, 'minTotal' => 2000000]] as $changes) {
            DB::table('tbl_coupons')->where('code', 'DISCOUNT10')->update($changes);
            try { $this->workflow->create(10, $this->data(['couponCode' => 'DISCOUNT10']), $this->passengers()); $this->fail('Invalid coupon accepted'); }
            catch (ValidationException $e) { $this->assertSame(0, DB::table('tbl_booking')->count()); }
        }
    }

    public function test_failed_booking_rolls_back_coupon_usage(): void
    {
        Schema::drop('tbl_checkout');
        try { $this->workflow->create(10, $this->data(['couponCode' => 'DISCOUNT10']), $this->passengers()); $this->fail('Should fail'); }
        catch (\Illuminate\Database\QueryException $e) {
            $this->assertEquals(0, DB::table('tbl_coupons')->where('code', 'DISCOUNT10')->value('usedCount'));
        }
    }

    public function test_deposit_and_balance_are_logged_once_and_allow_correct_transitions(): void
    {
        $booking = $this->workflow->create(10, $this->data(), $this->passengers());
        $token = (string) Str::uuid();
        foreach ([1, 2] as $attempt) $this->workflow->recordPayment($booking->bookingId, 338000, 'office-payment', 'staff-a', 'PT001', 'Nhận cọc', $token);
        $this->assertEquals(338000, DB::table('tbl_booking')->value('amountPaid'));
        $this->assertSame(1, DB::table('tbl_payment_receipts')->count());
        $this->assertSame('n', DB::table('tbl_checkout')->value('paymentStatus'));
        $this->workflow->transition($booking->bookingId, 'y');
        DB::table('tbl_tours')->update(['endDate' => now()->subDay()]);
        try { $this->workflow->transition($booking->bookingId, 'f'); $this->fail('Unpaid balance must block completion'); }
        catch (ValidationException $e) { $this->assertSame('y', DB::table('tbl_booking')->value('bookingStatus')); }
        $this->workflow->recordPayment($booking->bookingId, 1352000, 'bank-transfer', 'staff-b', 'BANK01', 'Thu phần còn lại', (string) Str::uuid());
        $this->assertSame('y', DB::table('tbl_checkout')->value('paymentStatus'));
        $this->assertSame(2, DB::table('tbl_payment_receipts')->count());
        $this->workflow->transition($booking->bookingId, 'f');
    }

    public function test_partial_payment_blocks_expiry_and_requires_refund_review_on_cancellation(): void
    {
        $booking = $this->workflow->create(10, $this->data(), $this->passengers());
        $this->workflow->recordPayment($booking->bookingId, 100000, 'office-payment', 'admin', '', 'Thu một phần cọc', (string) Str::uuid());
        DB::table('tbl_booking')->update(['depositDueAt' => now()->subDay()]);
        $this->assertFalse($this->workflow->expire($booking->bookingId));
        $this->workflow->requestCancellation($booking->bookingId, 10, 'Không thể tham gia');
        $this->workflow->reviewCancellation($booking->bookingId, true, 'Đồng ý hủy');
        $this->assertSame('pending', DB::table('tbl_booking')->value('refundStatus'));
    }

    public function test_overpayment_and_zero_payment_leave_no_receipt(): void
    {
        $booking = $this->workflow->create(10, $this->data(), $this->passengers());
        foreach ([0, 1690001] as $amount) {
            try { $this->workflow->recordPayment($booking->bookingId, $amount, 'office-payment', 'admin', '', 'Thu tiền', (string) Str::uuid()); $this->fail('Must reject invalid amount'); }
            catch (ValidationException $e) { $this->assertSame(0, DB::table('tbl_payment_receipts')->count()); }
        }
    }

    public function test_admin_receipt_endpoint_records_authenticated_staff_and_rejects_old_button(): void
    {
        $booking = $this->workflow->create(10, $this->data(), $this->passengers());
        $this->withSession(['admin' => 'staff-test']);
        $this->postJson('/admin/received-money', ['bookingId' => $booking->bookingId])
            ->assertUnprocessable()->assertJsonValidationErrors('amount');
        $this->post('/admin/received-money', [
            'bookingId' => $booking->bookingId, 'amount' => 338000, 'method' => 'office-payment',
            'note' => 'Khách nộp cọc', 'verified' => 1, 'receiptToken' => (string) Str::uuid(),
            'staff' => 'forged-staff',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('staff-test', DB::table('tbl_payment_receipts')->value('staff'));
    }

    public function test_excel_upload_saves_all_passengers_and_reserves_seats_once(): void
    {
        Schema::create('tbl_admin', function (Blueprint $t) { $t->string('username'); $t->string('email'); });
        config(['mail.from.address' => null]); // No external email during this test.
        $rows = [['STT', 'Loại khách', 'Họ và tên', 'Email', 'Số điện thoại', 'Địa chỉ']];
        foreach (['Người lớn', 'Trẻ em', 'Trẻ em'] as $i => $type) {
            $rows[] = [$i + 1, $type, 'Hành khách ' . ($i + 1), 'test@example.com', "\0" . '0901234567', 'Đà Nẵng'];
        }
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('passengers.xlsx', (string) \Shuchkin\SimpleXLSXGen::fromArray($rows));
        $data = $this->data(['numChildren' => 2, 'agree' => 'on', 'passenger_file' => $file]);
        $this->withSession(['username' => 'customer', 'userId' => 10])->post('/create-booking', $data)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(3, DB::table('tbl_booking_passengers')->count());
        $this->assertSame('0901234567', DB::table('tbl_booking_passengers')->value('phoneNumber'));
        $this->assertSame(3, DB::table('tbl_tours')->value('quantity'));
        $this->post('/create-booking', $data)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(1, DB::table('tbl_booking')->count());
        $this->assertSame(3, DB::table('tbl_booking_passengers')->count());
    }

    private function passengers(): array
    {
        return [['fullName' => 'Khách thử', 'email' => 'test@example.com', 'tel' => '0901234567', 'address' => 'Hà Nội']];
    }

    public function test_duplicate_submission_keeps_one_booking_and_one_seat_decrement(): void
    {
        $data = $this->data(['totalPrice' => 1, 'transactionIdPaypal' => 'fake']);
        $first = $this->workflow->create(10, $data, $this->passengers());
        $second = $this->workflow->create(10, $data, $this->passengers());
        $this->assertSame($first->bookingId, $second->bookingId);
        $this->assertEquals(1690000, $first->totalPrice);
        $this->assertSame(1, DB::table('tbl_checkout')->count());
        $this->assertSame('n', DB::table('tbl_checkout')->value('paymentStatus'));
        $this->assertSame(3, DB::table('tbl_tours')->value('quantity'));
    }

    public function test_coupon_is_calculated_on_the_server(): void
    {
        $booking = $this->workflow->create(10, $this->data(['couponCode' => 'DISCOUNT10']), $this->passengers());
        $this->assertEquals(1521000, $booking->totalPrice);
    }

    public function test_overbooking_and_departed_tours_are_rejected(): void
    {
        foreach ([['availability' => false], ['quantity' => 3, 'startDate' => now()->subDay()], ['startDate' => now()->addDay(), 'availability' => false]] as $changes) {
            DB::table('tbl_tours')->update($changes);
            try { $this->workflow->create(10, $this->data(), $this->passengers()); $this->fail('Should reject unavailable tour'); }
            catch (ValidationException $e) { $this->assertSame(0, DB::table('tbl_booking')->count()); }
        }
    }

    public function test_failed_checkout_rolls_back_booking_passengers_and_seats(): void
    {
        DB::statement("CREATE TRIGGER fail_checkout BEFORE INSERT ON tbl_checkout BEGIN SELECT RAISE(ABORT, 'test checkout failure'); END;");
        try { $this->workflow->create(10, $this->data(), $this->passengers()); $this->fail('Should fail checkout'); }
        catch (\Illuminate\Database\QueryException $e) {
            $this->assertSame(0, DB::table('tbl_booking')->count());
            $this->assertSame(0, DB::table('tbl_booking_passengers')->count());
            $this->assertSame(3, DB::table('tbl_tours')->value('quantity'));
        }
    }

    public function test_transfer_report_does_not_mark_paid_and_prevents_automatic_expiry(): void
    {
        $b = $this->workflow->create(10, $this->data(), $this->passengers());
        $this->workflow->reportTransfer($b->bookingId, 10);
        $this->workflow->reportTransfer($b->bookingId, 10);
        DB::table('tbl_booking')->update(['depositDueAt' => now()->subHour()]);
        $this->assertFalse($this->workflow->expire($b->bookingId));
        $this->assertSame('n', DB::table('tbl_checkout')->value('paymentStatus'));
        $this->assertSame('b', DB::table('tbl_booking')->value('bookingStatus'));
    }

    public function test_paid_booking_can_be_confirmed_but_not_finished_early(): void
    {
        $b = $this->workflow->create(10, $this->data(), $this->passengers());
        try { $this->workflow->transition($b->bookingId, 'y'); $this->fail('Unpaid booking confirmed'); }
        catch (ValidationException $e) { $this->assertSame('p', DB::table('tbl_booking')->value('bookingStatus')); }
        $this->workflow->receiveMoney($b->bookingId);
        $this->workflow->receiveMoney($b->bookingId);
        $this->workflow->transition($b->bookingId, 'y');
        $this->assertNotNull(DB::table('tbl_checkout')->value('paymentDate'));
        $this->expectException(ValidationException::class);
        $this->workflow->transition($b->bookingId, 'f');
    }

    public function test_cancellation_restores_seats_only_once_after_admin_approval(): void
    {
        $b = $this->workflow->create(10, $this->data(), $this->passengers());
        $this->workflow->receiveMoney($b->bookingId);
        $this->workflow->requestCancellation($b->bookingId, 10, 'Thay đổi lịch đi');
        $this->workflow->requestCancellation($b->bookingId, 10, 'Gửi lại yêu cầu');
        $this->assertSame(3, DB::table('tbl_tours')->value('quantity'));
        $this->workflow->reviewCancellation($b->bookingId, true, 'Liên hệ đối chiếu hoàn tiền');
        $this->assertSame(3, DB::table('tbl_tours')->value('quantity'));
        $this->assertSame('pending', DB::table('tbl_booking')->value('refundStatus'));
        try { $this->workflow->reviewCancellation($b->bookingId, true, 'Gửi lại'); $this->fail('Duplicate review accepted'); }
        catch (ValidationException $e) { $this->assertSame(3, DB::table('tbl_tours')->value('quantity')); }
        $this->assertFalse($this->workflow->expire($b->bookingId));
    }

    public function test_rejected_cancellation_keeps_booking_and_seats(): void
    {
        $b = $this->workflow->create(10, $this->data(), $this->passengers());
        $this->workflow->requestCancellation($b->bookingId, 10, 'Thay đổi lịch đi');
        $this->workflow->reviewCancellation($b->bookingId, false, 'Vui lòng liên hệ để đổi lịch');
        $this->assertSame('p', DB::table('tbl_booking')->value('bookingStatus'));
        $this->assertSame(3, DB::table('tbl_tours')->value('quantity'));
    }

    public function test_expired_unpaid_booking_releases_seats_once(): void
    {
        $b = $this->workflow->create(10, $this->data(), $this->passengers());
        DB::table('tbl_booking')->update(['depositDueAt' => now()->subHour()]);
        $this->assertTrue($this->workflow->expire($b->bookingId));
        $this->assertFalse($this->workflow->expire($b->bookingId));
        $this->assertSame(3, DB::table('tbl_tours')->value('quantity'));
        $this->expectException(ValidationException::class);
        $this->workflow->receiveMoney($b->bookingId);
    }

    public function test_other_customer_cannot_view_cancel_or_report_payment(): void
    {
        $b = $this->workflow->create(10, $this->data(), $this->passengers());
        $this->withSession(['username' => 'other', 'userId' => 11]);
        $this->get('/tour-booked?bookingId=' . $b->bookingId)->assertNotFound();
        $this->post('/cancel-booking', ['bookingId' => $b->bookingId, 'reason' => 'Không phải đơn của tôi'])->assertNotFound();
        $this->post('/report-transfer', ['bookingId' => $b->bookingId, 'proof' => $this->proofImage()])->assertNotFound();
        $this->assertSame(3, DB::table('tbl_tours')->value('quantity'));
    }

    public function test_booking_endpoint_ignores_browser_price_and_transaction_flags(): void
    {
        $data = array_merge($this->passengers()[0], $this->data(), ['agree' => 'on', 'totalPrice' => 1, 'transactionIdPaypal' => 'fake-paid']);
        $this->withSession(['username' => 'customer', 'userId' => 10])->post('/create-booking', $data)->assertRedirect();
        $this->assertSame(1, DB::table('tbl_booking')->count());
        $this->assertEquals(1690000, DB::table('tbl_booking')->value('totalPrice'));
        $this->assertSame('n', DB::table('tbl_checkout')->value('paymentStatus'));
        $this->post('/create-booking', $data)->assertRedirect();
        $this->assertSame(1, DB::table('tbl_booking')->count());
        $this->get('/tour-booked?bookingId=' . DB::table('tbl_booking')->value('bookingId'))
            ->assertOk()->assertSee('TRAVELA B1')->assertSee('1.690.000')->assertSee('Gửi ảnh chuyển khoản');
    }

    public function test_invalid_counts_and_unauthenticated_requests_are_rejected(): void
    {
        $this->post('/create-booking', [])->assertRedirect(route('login'));
        $this->withSession(['username' => 'customer', 'userId' => 10])->postJson('/create-booking', $this->data(['numAdults' => -1, 'agree' => 'on']))->assertUnprocessable();
        $this->assertSame(0, DB::table('tbl_booking')->count());
    }

    public function test_close_departures_cannot_be_cancelled_online(): void
    {
        $b = $this->workflow->create(10, $this->data(), $this->passengers());
        DB::table('tbl_tours')->update(['startDate' => now()->addDays(2)]);
        $this->expectException(ValidationException::class);
        $this->workflow->requestCancellation($b->bookingId, 10, 'Muốn hủy đơn');
    }

    public function test_refund_resolution_records_staff_note_without_changing_payment_or_seats(): void
    {
        $b = $this->workflow->create(10, $this->data(), $this->passengers());
        $this->workflow->receiveMoney($b->bookingId);
        $this->workflow->requestCancellation($b->bookingId, 10, 'Thay đổi lịch đi');
        $this->workflow->reviewCancellation($b->bookingId, true, 'Đã duyệt yêu cầu');
        $this->workflow->resolveRefund($b->bookingId, 'Đã đối chiếu và hoàn tiền qua giao dịch TEST123');
        $this->assertSame('resolved', DB::table('tbl_booking')->value('refundStatus'));
        $this->assertStringContainsString('TEST123', DB::table('tbl_booking')->value('cancelNote'));
        $this->assertSame(3, DB::table('tbl_tours')->value('quantity'));
        $this->assertSame('y', DB::table('tbl_checkout')->value('paymentStatus'));
    }

    public function test_admin_review_form_is_rendered_for_pending_requests(): void
    {
        $b = $this->workflow->create(10, $this->data(), $this->passengers());
        $this->workflow->requestCancellation($b->bookingId, 10, 'Muốn đổi lịch');
        $invoice = DB::table('tbl_booking')->join('tbl_checkout', 'tbl_booking.bookingId', '=', 'tbl_checkout.bookingId')->first();
        $html = view('admin.partials.quy-trinh-dat-tour', ['invoice_booking' => $invoice, 'errors' => new \Illuminate\Support\ViewErrorBag()])->render();
        $this->assertStringContainsString('Chấp thuận hủy và trả chỗ', $html);
        $this->assertStringContainsString('TRAVELA B1', $html);
    }
}
