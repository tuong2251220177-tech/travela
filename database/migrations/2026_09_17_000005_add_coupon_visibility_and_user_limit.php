<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('tbl_coupons', function (Blueprint $t) {
            $t->boolean('isPublic')->default(true);
            $t->unsignedInteger('perUserLimit')->nullable();
        });
        Schema::table('tbl_booking', fn (Blueprint $t) => $t->index(['userId', 'couponCode'], 'booking_user_coupon'));
    }
    public function down(): void {
        Schema::table('tbl_booking', fn (Blueprint $t) => $t->dropIndex('booking_user_coupon'));
        Schema::table('tbl_coupons', fn (Blueprint $t) => $t->dropColumn(['isPublic', 'perUserLimit']));
    }
};
