<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\admin\DuLieuBangDieuKhien;
use Illuminate\Http\Request;

class DieuKhienBangDieuKhien extends Controller
{

    private $dashboard;

    public function __construct()
    {
        $this->dashboard = new DuLieuBangDieuKhien();
    }
    public function index()
    {
        $title = 'Admin';

        $summary = $this->dashboard->getSummary();
        $valueTour = $this->dashboard->getValueDomain();
        $dataDomain = [
            'values' => [
                $valueTour['b'] ?? 0,
                $valueTour['t'] ?? 0,
                $valueTour['n'] ?? 0,
            ]
        ];

        $paymentStatus = $this->dashboard->getValuePayment();

        $toursBooked = $this->dashboard->getMostTourBooked();
        $newBooking = $this->dashboard->getNewBooking();
        $revenue = $this->dashboard->getRevenuePerMonth();
        // dd($revenue);

        return view('admin.bang-dieu-khien', compact('title', 'summary', 'dataDomain', 'paymentStatus','toursBooked','newBooking','revenue'));
    }


}
