<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('tbl_tour_transport_options')) Schema::create('tbl_tour_transport_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('tourId')->index();
            $table->string('name', 100);
            $table->unsignedBigInteger('surcharge')->default(0);
            $table->string('chargeUnit', 20)->default('person');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::table('tbl_booking', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_booking', 'transportOptionId')) $table->unsignedBigInteger('transportOptionId')->nullable()->index();
            if (!Schema::hasColumn('tbl_booking', 'transportName')) $table->string('transportName', 100)->nullable();
            if (!Schema::hasColumn('tbl_booking', 'transportFee')) $table->unsignedBigInteger('transportFee')->default(0);
        });
        foreach (\Illuminate\Support\Facades\DB::table('tbl_tours')->get() as $tour) {
            if (!empty($tour->privateRequestId)) continue;
            if (\Illuminate\Support\Facades\DB::table('tbl_tour_transport_options')->where('tourId', $tour->tourId)->exists()) continue;
            \Illuminate\Support\Facades\DB::table('tbl_tour_transport_options')->insert([
                'tourId' => $tour->tourId, 'name' => 'Xe limousine', 'surcharge' => 200000,
                'chargeUnit' => 'person', 'active' => true, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('tbl_booking', function (Blueprint $table) {
            // transportFee may predate this migration in imported databases.
            $table->dropColumn(['transportOptionId', 'transportName']);
        });
        Schema::dropIfExists('tbl_tour_transport_options');
    }
};
