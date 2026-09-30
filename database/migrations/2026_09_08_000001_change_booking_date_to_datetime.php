<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tbl_booking') && Schema::hasColumn('tbl_booking', 'bookingDate')) {
            DB::statement('ALTER TABLE `tbl_booking` MODIFY `bookingDate` DATETIME NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tbl_booking') && Schema::hasColumn('tbl_booking', 'bookingDate')) {
            DB::statement('ALTER TABLE `tbl_booking` MODIFY `bookingDate` DATE NULL');
        }
    }
};