<?php

namespace App\Console\Commands;

use App\Models\admin\DuLieuDatTour;
use Illuminate\Console\Command;

class HuyDatTourHetHanCoc extends Command
{
    protected $signature = 'bookings:expire-deposits';
    protected $description = 'Hủy booking quá hạn chưa thanh toán tiền cọc';

    public function handle(DuLieuDatTour $booking): int
    {
        $count = $booking->expireOverdueDeposits();
        $this->info("Đã xử lý {$count} booking quá hạn tiền cọc.");

        return self::SUCCESS;
    }
}
