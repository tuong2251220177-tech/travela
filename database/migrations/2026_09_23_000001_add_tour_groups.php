<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tbl_tours', function (Blueprint $t) {
            $t->unsignedInteger('minGuests')->default(5);
            $t->unsignedInteger('maxGuests')->default(30);
        });
        Schema::table('tbl_booking', function (Blueprint $t) {
            $t->unsignedInteger('groupNumber')->default(1);
            $t->index(['tourId', 'groupNumber', 'bookingStatus'], 'booking_tour_group_status');
        });
        // Preserve existing parties and assignments in group 1. Recover the original capacity.
        foreach (DB::table('tbl_tours')->get() as $tour) {
            $booked = DB::table('tbl_booking')->where('tourId', $tour->tourId)->where('bookingStatus', '!=', 'c')->sum(DB::raw('numAdults + numChildren'));
            $max = max(5, (int) $tour->quantity + (int) $booked);
            DB::table('tbl_tours')->where('tourId', $tour->tourId)->update(['maxGuests' => $max]);
        }
        if (Schema::hasTable('tbl_guide_assignments')) {
            Schema::table('tbl_guide_assignments', function (Blueprint $t) {
                $t->unsignedInteger('groupNumber')->default(1);
                $t->dropUnique(['guide_id', 'tour_id']);
                $t->unique(['guide_id', 'tour_id', 'groupNumber'], 'guide_tour_group_unique');
            });
        }
    }
    public function down(): void
    {
        if (Schema::hasColumn('tbl_guide_assignments', 'groupNumber')) Schema::table('tbl_guide_assignments', function (Blueprint $t) { $t->dropUnique('guide_tour_group_unique'); $t->unique(['guide_id', 'tour_id']); $t->dropColumn('groupNumber'); });
        Schema::table('tbl_booking', function (Blueprint $t) { $t->dropIndex('booking_tour_group_status'); $t->dropColumn('groupNumber'); });
        Schema::table('tbl_tours', fn (Blueprint $t) => $t->dropColumn(['minGuests', 'maxGuests']));
    }
};
