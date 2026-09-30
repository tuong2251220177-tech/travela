<?php

namespace App\Http\Controllers\clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DieuKhienTourCuaToi extends Controller
{
    public function __construct()
    {
        parent::__construct(); // Gọi constructor của Controller để khởi tạo $user
    }

    public function index(Request $request)
    {
        $title = 'Tours đã đặt';
        $userId = $this->getUserId();
        
            $status = $request->query('status', 'all');
            $period = $request->query('period', 'all');
            $allowedStatuses = ['all', 'p', 'b', 'y', 'f', 'c'];
            $allowedPeriods = ['all', 'upcoming', 'completed'];
            $status = in_array($status, $allowedStatuses, true) ? $status : 'all';
            $period = in_array($period, $allowedPeriods, true) ? $period : 'all';

            $myTours = $this->user->getMyTours($userId, $status, $period);
            $tourCounts = [
                'all' => $this->user->getMyTours($userId)->count(),
                'upcoming' => $this->user->getMyTours($userId, 'all', 'upcoming')->count(),
                'completed' => $this->user->getMyTours($userId, 'all', 'completed')->count(),
            ];
        return view('clients.tour-cua-toi', compact('title', 'myTours', 'status', 'period', 'tourCounts'));
    }
}
