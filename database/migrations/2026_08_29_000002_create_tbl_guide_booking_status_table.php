<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tbl_guide_booking_status')) {
            return;
        }

        Schema::create('tbl_guide_booking_status', function (Blueprint $table) {
            $table->id('status_id');
            $table->unsignedBigInteger('guide_id');
            $table->unsignedBigInteger('tour_id');
            $table->unsignedBigInteger('booking_id');
            $table->string('guest_status')->default('present'); // present, absent, cancelled
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['guide_id', 'tour_id', 'booking_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_guide_booking_status');
    }
};
