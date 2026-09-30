<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('tbl_booking', function (Blueprint $table) {
            $table->string('paymentPlan', 10)->default('deposit');
        });
    }
    public function down(): void {
        Schema::table('tbl_booking', fn (Blueprint $table) => $table->dropColumn('paymentPlan'));
    }
};
