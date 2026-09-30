<?php

namespace App\Http\Controllers\clients;

use App\Http\Controllers\Controller;
use App\Models\clients\DuLieuTour as Tours;
use Illuminate\Http\Request;

class DieuKhienDiemDen extends Controller
{
    private $tours;
    public function __construct(){
        $this->tours = new Tours();
    }

    public function index()
    {
        $title = 'Điểm đến';
        $tours = $this->tours->getAllTours( 9);
        return view('clients.diem-den', compact('title','tours'));
    }

    
}
