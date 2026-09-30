<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tbl_booking', function (Blueprint $table) {
            $table->string('requestKey', 64)->nullable()->unique();
            $table->timestamp('transferReportedAt')->nullable();
            $table->string('cancelStatus', 20)->nullable();
            $table->text('cancelReason')->nullable();
            $table->text('cancelNote')->nullable();
            $table->timestamp('cancelRequestedAt')->nullable();
            $table->timestamp('cancelReviewedAt')->nullable();
            $table->string('refundStatus', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tbl_booking', function (Blueprint $table) {
            $table->dropUnique(['requestKey']);
            $table->dropColumn(['requestKey', 'transferReportedAt', 'cancelStatus', 'cancelReason', 'cancelNote', 'cancelRequestedAt', 'cancelReviewedAt', 'refundStatus']);
        });
    }
};
