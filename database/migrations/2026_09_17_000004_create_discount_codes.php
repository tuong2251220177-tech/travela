<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void {
        Schema::create('tbl_coupons', function (Blueprint $t) {
            $t->bigIncrements('id'); $t->string('code', 40)->unique();
            $t->string('type', 10); $t->unsignedBigInteger('value');
            $t->unsignedBigInteger('minTotal')->default(0); $t->unsignedBigInteger('maxDiscount')->nullable();
            $t->unsignedInteger('usageLimit'); $t->unsignedInteger('usedCount')->default(0);
            $t->dateTime('expiresAt')->nullable(); $t->boolean('active')->default(true);
            $t->string('createdBy'); $t->timestamp('created_at');
        });
        Schema::table('tbl_booking', function (Blueprint $t) {
            $t->string('couponCode', 40)->nullable(); $t->decimal('discountAmount', 15, 0)->default(0);
        });
        // Preserve the two previously advertised codes, now managed by admin.
        foreach (['DISCOUNT10', 'KMTOUR10'] as $code) DB::table('tbl_coupons')->insert([
            'code' => $code, 'type' => 'percent', 'value' => 10, 'usageLimit' => 1000000,
            'createdBy' => 'Chuyển từ cấu hình cũ', 'created_at' => now(),
        ]);
    }
    public function down(): void {
        Schema::table('tbl_booking', fn (Blueprint $t) => $t->dropColumn(['couponCode', 'discountAmount']));
        Schema::dropIfExists('tbl_coupons');
    }
};
