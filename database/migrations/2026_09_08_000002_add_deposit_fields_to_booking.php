<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tbl_booking')) {
            return;
        }

        if (!Schema::hasColumn('tbl_booking', 'depositAmount')) {
            DB::statement('ALTER TABLE `tbl_booking` ADD `depositAmount` DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER `totalPrice`');
        }
        if (!Schema::hasColumn('tbl_booking', 'depositDueAt')) {
            DB::statement('ALTER TABLE `tbl_booking` ADD `depositDueAt` DATETIME NULL AFTER `depositAmount`');
        }
        if (!Schema::hasColumn('tbl_booking', 'depositStatus')) {
            DB::statement("ALTER TABLE `tbl_booking` ADD `depositStatus` VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER `depositDueAt`");
        }
    }

    public function down(): void
    {
        foreach (['depositStatus', 'depositDueAt', 'depositAmount'] as $column) {
            if (Schema::hasColumn('tbl_booking', $column)) {
                DB::statement('ALTER TABLE `tbl_booking` DROP COLUMN `' . $column . '`');
            }
        }
    }
};