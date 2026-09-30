<?php

namespace Tests\Feature;

use App\Models\admin\DuLieuTour;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TourDeletionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.foreign_key_constraints' => true]);
        DB::purge('sqlite');
        Schema::create('tbl_tours', fn (Blueprint $t) => $t->increments('tourId'));
        foreach (['tbl_booking', 'tbl_images', 'tbl_timeline', 'tbl_reviews'] as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->increments('id'); $t->unsignedInteger('tourId');
                $t->foreign('tourId')->references('tourId')->on('tbl_tours');
            });
        }
        DB::table('tbl_tours')->insert(['tourId' => 1]);
        DB::table('tbl_images')->insert(['tourId' => 1]);
        DB::table('tbl_timeline')->insert(['tourId' => 1]);
    }

    public function test_booking_blocks_deletion_without_losing_images_or_itinerary(): void
    {
        DB::table('tbl_booking')->insert(['tourId' => 1]);
        $result = (new DuLieuTour())->deleteTour(1);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('1 đơn đặt', $result['message']);
        $this->assertSame(1, DB::table('tbl_images')->count());
        $this->assertSame(1, DB::table('tbl_timeline')->count());
        $this->assertSame(1, DB::table('tbl_tours')->count());
    }

    public function test_other_foreign_key_rolls_back_child_deletions(): void
    {
        DB::table('tbl_reviews')->insert(['tourId' => 1]);
        $this->assertFalse((new DuLieuTour())->deleteTour(1)['success']);
        $this->assertSame(1, DB::table('tbl_images')->count());
        $this->assertSame(1, DB::table('tbl_timeline')->count());
    }

    public function test_unused_tour_is_deleted_and_repeat_is_handled(): void
    {
        $this->assertTrue((new DuLieuTour())->deleteTour(1)['success']);
        $this->assertSame(0, DB::table('tbl_tours')->count());
        $this->assertSame(0, DB::table('tbl_images')->count());
        $this->assertFalse((new DuLieuTour())->deleteTour(1)['success']);
    }
}
