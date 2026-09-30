<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Services\TourRieng;
use App\Services\QuyTrinhDatTour;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PrivateTourTest extends TestCase
{
    private TourRieng $service;
    private function requestData(): array
    {
        return ['tourId' => 1, 'token' => (string) Str::uuid(), 'fullName' => 'Nguyen An', 'email' => 'an@example.com', 'phoneNumber' => '0901234567', 'address' => 'Da Nang', 'numAdults' => 8, 'numChildren' => 2, 'requestedDate' => now()->addDays(40)->toDateString(), 'notes' => 'Gia dinh di rieng'];
    }

    private function quoteData(int $revision = 0): array
    {
        return ['revision' => $revision, 'quoteTotal' => 15000001, 'startDate' => now()->addDays(40)->toDateTimeString(), 'endDate' => now()->addDays(42)->toDateTimeString(), 'expiresAt' => now()->addDays(2)->toDateTimeString(), 'quoteNotes' => 'Xe rieng, khach san, huong dan vien. Huy theo thoa thuan.', 'confirmedResources' => '1'];
    }

    private function passengers(): array
    {
        return array_fill(0, 10, ['fullName' => 'Nguyen An', 'email' => 'an@example.com', 'tel' => '0901234567', 'address' => 'Da Nang']);
    }

    public function test_quote_acceptance_is_idempotent_private_and_keeps_public_tour_unchanged(): void
    {
        $original = DB::table('tbl_tours')->first();
        $data = $this->requestData();
        $id = $this->service->request(10, $data);
        $this->assertSame($id, $this->service->request(10, $data));
        $quote = $this->quoteData();
        $this->service->quote($id, $quote);
        $bookingId = $this->service->accept($id, 10, 1, 'bank-transfer', $this->passengers());
        $this->assertSame($bookingId, $this->service->accept($id, 10, 1, 'bank-transfer', $this->passengers()));
        $this->assertEquals($original, DB::table('tbl_tours')->where('tourId', 1)->first());
        $booking = DB::table('tbl_booking')->first();
        $private = DB::table('tbl_tours')->where('tourId', $booking->tourId)->first();
        $this->assertNotEquals(1, $private->tourId);
        $this->assertEquals(0, $private->availability);
        $this->assertEquals($id, $private->privateRequestId);
        $this->assertEquals(10, $private->minGuests);
        $this->assertEquals(15000001, $booking->totalPrice);
        $this->assertEquals(3000000, $booking->depositAmount);
        $this->assertSame(10, DB::table('tbl_booking_passengers')->count());
        $this->assertSame(2, DB::table('tbl_booking_passengers')->where('passengerType', 'child')->count());
        $this->assertEquals($quote['startDate'], $private->startDate);
        $this->assertEquals('n', DB::table('tbl_checkout')->value('paymentStatus'));
        $this->assertSame(1, DB::table('tbl_images')->where('tourId', $private->tourId)->count());
        $this->assertSame(1, DB::table('tbl_timeline')->where('tourId', $private->tourId)->count());
        $this->assertSame(1, DB::table('tbl_booking')->count());
        $this->assertSame([1], app(\App\Models\admin\DuLieuTour::class)->getAllTours()->pluck('tourId')->all());
        app(QuyTrinhDatTour::class)->receiveMoney($bookingId);
        app(QuyTrinhDatTour::class)->transition($bookingId, 'y');
        $this->assertTrue(app(\App\Services\NhomTour::class)->summary($private->tourId, 1)->ready);
        $this->assertEquals(10, app(\App\Services\NhomTour::class)->summary($private->tourId, 1)->confirmed);
    }

    public function test_stale_and_expired_quotes_cannot_create_bookings(): void
    {
        $id = $this->service->request(10, $this->requestData());
        $this->service->quote($id, $this->quoteData());
        $this->service->quote($id, $this->quoteData(1));
        try { $this->service->accept($id, 10, 1, 'bank-transfer', $this->passengers()); $this->fail('Stale quote accepted'); }
        catch (ValidationException $e) { $this->assertSame(0, DB::table('tbl_booking')->count()); }
        DB::table('tbl_private_tour_requests')->update(['expiresAt' => now()->subMinute()]);
        try { $this->service->accept($id, 10, 2, 'bank-transfer', $this->passengers()); $this->fail('Expired quote accepted'); }
        catch (ValidationException $e) { $this->assertSame(1, DB::table('tbl_tours')->count()); }
    }

    public function test_owner_and_admin_http_boundaries_and_forms(): void
    {
        $id = $this->service->request(10, $this->requestData());
        $this->get(route('private-tours.index'))->assertRedirect(route('login'));
        $this->withSession(['username' => 'other', 'userId' => 11])->get(route('private-tours.show', $id))->assertNotFound();
        $this->withSession(['username' => 'owner', 'userId' => 10])->get(route('private-tours.index'))->assertOk()->assertSee('Gửi yêu cầu tour riêng');
        $this->get(route('private-tours.show', $id))->assertOk();
        $this->post(route('admin.private-tours.quote', $id), $this->quoteData())->assertRedirect(route('admin.login'));
        $this->withSession(['admin' => 'admin'])->post(route('admin.private-tours.quote', $id), $this->quoteData())->assertSessionHasNoErrors();
        $this->get(route('admin.private-tours.index'))->assertOk();
        $this->get(route('admin.private-tours.show', $id))->assertOk()->assertSee('Cập nhật báo giá');
        $this->get(route('private-tours.show', $id))->assertOk()->assertSee('Chấp nhận báo giá');
        $payload = ['revision' => 1, 'payment' => 'office-payment', 'agree' => 1, 'passengers' => $this->passengers(), 'quoteTotal' => 1];
        $this->withSession(['username' => 'other', 'userId' => 11])->post(route('private-tours.accept', $id), $payload)->assertNotFound();
        $this->withSession(['username' => 'owner', 'userId' => 10])->post(route('private-tours.accept', $id), $payload)->assertRedirect();
        $this->assertEquals(15000001, DB::table('tbl_booking')->value('totalPrice'));
    }

    public function test_cancelled_request_cannot_be_accepted_and_cannot_affect_other_users(): void
    {
        $id = $this->service->request(10, $this->requestData());
        $this->service->quote($id, $this->quoteData());
        $this->withSession(['username' => 'other', 'userId' => 11])->post(route('private-tours.cancel', $id))->assertNotFound();
        $this->service->close($id, 10);
        $this->expectException(ValidationException::class);
        $this->service->accept($id, 10, 1, 'bank-transfer', $this->passengers());
    }

    public function test_private_departure_cannot_be_booked_as_a_shared_tour_even_if_reopened(): void
    {
        $id = $this->service->request(10, $this->requestData());
        $this->service->quote($id, $this->quoteData());
        $bookingId = $this->service->accept($id, 10, 1, 'bank-transfer', $this->passengers());
        $tourId = DB::table('tbl_booking')->where('bookingId', $bookingId)->value('tourId');
        DB::table('tbl_tours')->where('tourId', $tourId)->update(['availability' => true]);
        $this->expectException(ValidationException::class);
        app(QuyTrinhDatTour::class)->create(11, ['tourId' => $tourId, 'bookingToken' => (string) Str::uuid(), 'numAdults' => 1, 'numChildren' => 0, 'payment' => 'bank-transfer'], [$this->passengers()[0]]);
    }

    public function test_failure_rolls_back_private_departure_and_request(): void
    {
        $id = $this->service->request(10, $this->requestData());
        $this->service->quote($id, $this->quoteData());
        DB::statement("CREATE TRIGGER fail_checkout BEFORE INSERT ON tbl_checkout BEGIN SELECT RAISE(ABORT, 'test failure'); END");
        try { $this->service->accept($id, 10, 1, 'bank-transfer', $this->passengers()); $this->fail('Expected failed checkout'); }
        catch (\Illuminate\Database\QueryException $e) {
            $this->assertSame(1, DB::table('tbl_tours')->count());
            $this->assertSame(0, DB::table('tbl_booking')->count());
            $this->assertSame('quoted', DB::table('tbl_private_tour_requests')->value('status'));
        }
    }
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
        DB::table('tbl_tours')->update(['minGuests' => 1, 'maxGuests' => 3]);
        Schema::create('tbl_images', function (Blueprint $t) { $t->increments('imageId'); $t->integer('tourId'); $t->string('imageURL'); $t->string('description')->nullable(); });
        Schema::create('tbl_timeline', function (Blueprint $t) { $t->increments('timelineId'); $t->integer('tourId'); $t->string('title'); $t->text('description'); });
        Schema::create('tbl_contact', function (Blueprint $t) { $t->increments('contactId'); $t->string('isReply'); });
        (require database_path('migrations/2026_09_29_000001_create_private_tour_requests.php'))->up();
        (require database_path('migrations/2026_09_29_000002_add_booking_transport_options.php'))->up();
        (require database_path('migrations/2026_09_29_000003_add_booking_payment_plan.php'))->up();
        DB::table('tbl_images')->insert(['tourId' => 1, 'imageURL' => 'tour.jpg']);
        DB::table('tbl_timeline')->insert(['tourId' => 1, 'title' => 'Day 1', 'description' => 'Original itinerary']);
        $this->service = app(TourRieng::class);
    }

}
