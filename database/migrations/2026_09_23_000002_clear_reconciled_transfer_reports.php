<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Proof files and receipt history stay intact. Clear only reports predating a receipt.
        foreach (DB::table('tbl_booking')->whereNotNull('transferReportedAt')->where('amountPaid', '>', 0)->get() as $booking) {
            $receipt = DB::table('tbl_payment_receipts')->where('bookingId', $booking->bookingId)->max('created_at');
            if ($receipt && $receipt >= $booking->transferReportedAt) {
                DB::table('tbl_booking')->where('bookingId', $booking->bookingId)->where('transferReportedAt', $booking->transferReportedAt)->update(['transferReportedAt' => null]);
            }
        }
    }
    public function down(): void {}
};
