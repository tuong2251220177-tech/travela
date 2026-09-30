<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tbl_booking_passengers')) {
            return;
        }

        Schema::create('tbl_booking_passengers', function (Blueprint $table) {
            $table->bigIncrements('passengerId');
            $table->unsignedBigInteger('bookingId');
            $table->unsignedInteger('passengerNumber');
            $table->string('passengerType', 20)->nullable();
            $table->string('fullName');
            $table->string('email')->nullable();
            $table->string('phoneNumber')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();

            $table->index(['bookingId', 'passengerNumber']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tbl_booking_passengers');
    }
};
