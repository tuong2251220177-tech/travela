<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tbl_booking', function (Blueprint $table) {
            $table->decimal('amountPaid', 15, 0)->default(0);
        });
        Schema::create('tbl_payment_receipts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('bookingId')->index();
            $table->uuid('requestToken')->unique();
            $table->decimal('amount', 15, 0);
            $table->string('method', 30);
            $table->string('staff');
            $table->string('reference')->nullable();
            $table->text('note');
            $table->timestamp('created_at');
        });
        DB::table('tbl_booking')->whereIn('bookingId', function ($query) {
            $query->select('bookingId')->from('tbl_checkout')->where('paymentStatus', 'y');
        })->update(['amountPaid' => DB::raw('totalPrice')]);
    }
    public function down(): void
    {
        Schema::dropIfExists('tbl_payment_receipts');
        Schema::table('tbl_booking', fn (Blueprint $table) => $table->dropColumn('amountPaid'));
    }
};
