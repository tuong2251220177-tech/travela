<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tbl_booking') && !Schema::hasColumn('tbl_booking', 'depositPaidAt')) {
            DB::statement('ALTER TABLE `tbl_booking` ADD `depositPaidAt` DATETIME NULL AFTER `depositStatus`');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tbl_booking', 'depositPaidAt')) {
            DB::statement('ALTER TABLE `tbl_booking` DROP COLUMN `depositPaidAt`');
        }
    }
};