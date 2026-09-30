<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tbl_booking') && !Schema::hasColumn('tbl_booking', 'bookingId')) {
            Schema::table('tbl_booking', function (Blueprint $table) {
                $table->bigIncrements('bookingId')->first();
            });
        }

        if (Schema::hasTable('tbl_checkout') && !Schema::hasColumn('tbl_checkout', 'checkoutId')) {
            Schema::table('tbl_checkout', function (Blueprint $table) {
                $table->bigIncrements('checkoutId')->first();
            });
        }

        if (Schema::hasTable('tbl_booking') && !Schema::hasColumn('tbl_booking', 'tourId')) {
            Schema::table('tbl_booking', function (Blueprint $table) {
                $table->unsignedBigInteger('tourId')->nullable()->after('bookingId');
                $table->unsignedBigInteger('userId')->nullable()->after('tourId');
                $table->string('address')->nullable()->after('userId');
                $table->string('fullName')->nullable()->after('address');
                $table->string('email')->nullable()->after('fullName');
                $table->integer('numAdults')->default(0)->after('email');
                $table->integer('numChildren')->default(0)->after('numAdults');
                $table->string('phoneNumber')->nullable()->after('numChildren');
                $table->decimal('totalPrice', 15, 2)->default(0)->after('phoneNumber');
                $table->date('bookingDate')->nullable()->after('totalPrice');
                $table->char('bookingStatus', 1)->default('p')->after('bookingDate');
            });
        }

        if (Schema::hasTable('tbl_checkout') && !Schema::hasColumn('tbl_checkout', 'bookingId')) {
            Schema::table('tbl_checkout', function (Blueprint $table) {
                $table->unsignedBigInteger('bookingId')->nullable()->after('checkoutId');
                $table->string('paymentMethod')->nullable()->after('bookingId');
                $table->decimal('amount', 15, 2)->default(0)->after('paymentMethod');
                $table->char('paymentStatus', 1)->default('n')->after('amount');
                $table->string('transactionId')->nullable()->after('paymentStatus');
                $table->dateTime('paymentDate')->nullable()->after('transactionId');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tbl_booking')) {
            $columns = ['bookingId', 'tourId', 'userId', 'address', 'fullName', 'email', 'numAdults', 'numChildren', 'phoneNumber', 'totalPrice', 'bookingDate', 'bookingStatus'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('tbl_booking', $column) && $column !== 'bookingId') {
                    Schema::table('tbl_booking', function (Blueprint $table) use ($column) {
                        $table->dropColumn($column);
                    });
                }
            }
        }

        if (Schema::hasTable('tbl_checkout')) {
            $columns = ['bookingId', 'paymentMethod', 'amount', 'paymentStatus', 'transactionId', 'paymentDate'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('tbl_checkout', $column) && $column !== 'checkoutId') {
                    Schema::table('tbl_checkout', function (Blueprint $table) use ($column) {
                        $table->dropColumn($column);
                    });
                }
            }
        }
    }
};
