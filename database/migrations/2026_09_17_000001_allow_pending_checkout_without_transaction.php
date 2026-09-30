<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Legacy MySQL installations already had these columns, so the original
        // create-if-missing migration never corrected their NOT NULL constraints.
        if (DB::getDriverName() === 'mysql' && Schema::hasTable('tbl_checkout')) {
            DB::statement('ALTER TABLE `tbl_checkout`
                MODIFY `transactionId` VARCHAR(255) NULL DEFAULT NULL,
                MODIFY `paymentDate` TIMESTAMP NULL DEFAULT NULL');
        }
    }

    public function down(): void
    {
        // Keep nullable columns: reverting would invalidate pending payments or
        // require inventing transaction IDs/payment dates for unpaid bookings.
    }
};
