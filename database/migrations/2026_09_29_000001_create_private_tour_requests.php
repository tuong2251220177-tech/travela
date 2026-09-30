<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_private_tour_requests', function (Blueprint $t) {
            $t->id();
            $t->unsignedInteger('userId')->index();
            $t->unsignedInteger('tourId')->index();
            $t->string('requestKey', 64)->unique();
            $t->string('tourTitle');
            $t->string('fullName');
            $t->string('email');
            $t->string('phoneNumber', 30);
            $t->string('address');
            $t->unsignedInteger('numAdults');
            $t->unsignedInteger('numChildren');
            $t->date('requestedDate');
            $t->text('notes')->nullable();
            $t->string('status', 20)->default('pending')->index();
            $t->unsignedInteger('revision')->default(0);
            $t->unsignedBigInteger('quoteTotal')->nullable();
            $t->dateTime('startDate')->nullable();
            $t->dateTime('endDate')->nullable();
            $t->dateTime('expiresAt')->nullable();
            $t->text('quoteNotes')->nullable();
            $t->longText('snapshot')->nullable();
            $t->unsignedInteger('bookingId')->nullable()->unique();
            $t->timestamps();
        });
        Schema::table('tbl_tours', function (Blueprint $t) {
            $t->unsignedBigInteger('privateRequestId')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('tbl_tours', fn (Blueprint $t) => $t->dropColumn('privateRequestId'));
        Schema::dropIfExists('tbl_private_tour_requests');
    }
};
