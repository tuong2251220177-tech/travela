<?php

namespace App\Http\Controllers\clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DieuKhienHuongDanVien extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'Hướng dẫn viên';

        $guides = collect();

        if (Schema::hasTable('tbl_admin')) {
            $select = [
                'a.adminId as id',
                'a.username as name',
                'a.email',
            ];

            if (Schema::hasColumn('tbl_admin', 'full_name')) {
                $select[] = 'a.full_name';
            }

            if (Schema::hasColumn('tbl_admin', 'avatar')) {
                $select[] = 'a.avatar';
            }

            if (Schema::hasColumn('tbl_admin', 'description')) {
                $select[] = 'a.description';
            }

            $query = DB::table('tbl_admin as a')->select($select);
            $query->where('a.guideEnabled', true);

            if (Schema::hasColumn('tbl_admin', 'role')) {
                $query->where('a.role', 'guide');
            } else {
                $query->where(function ($q) {
                    $q->where('a.username', 'not like', '%admin%')
                        ->where('a.username', '!=', 'admin');
                });
            }

            $guides = $query->orderBy('a.adminId')->get();
        }

        if ($guides->isEmpty()) {
            $guides = collect([
                ['id' => 1, 'name' => 'DEV DIEN', 'email' => 'guide1@travela.com', 'avatar' => 'clients/assets/images/team/guide-dien.jpg', 'description' => 'Hướng dẫn viên du lịch chuyên phục vụ tour miền núi và trải nghiệm văn hóa.'],
                ['id' => 2, 'name' => 'BAO NGAN', 'email' => 'guide2@travela.com', 'avatar' => 'clients/assets/images/team/guide-ngan.jpg', 'description' => 'Chuyên dẫn tour khám phá thành phố, phố cổ và ẩm thực địa phương.'],
                ['id' => 3, 'name' => 'MINH TOAN', 'email' => 'guide3@travela.com', 'avatar' => 'clients/assets/images/team/guide1.jpg', 'description' => 'Đưa khách đi trải nghiệm trekking, chụp ảnh và du lịch sinh thái.'],
                ['id' => 4, 'name' => 'TRONG HAI', 'email' => 'guide4@travela.com', 'avatar' => 'clients/assets/images/team/guide2.jpg', 'description' => 'Tư vấn tour dài ngày và trải nghiệm du lịch gia đình theo phong cách riêng.'],
            ]);
        }

        $defaultImages = [
            'clients/assets/images/team/guide-dien.jpg',
            'clients/assets/images/team/guide-ngan.jpg',
            'clients/assets/images/team/guide1.jpg',
            'clients/assets/images/team/guide2.jpg',
            'clients/assets/images/team/guide3.jpg',
            'clients/assets/images/team/guide4.jpg',
            'clients/assets/images/team/guide5.jpg',
            'clients/assets/images/team/guide6.jpg',
        ];

        $guides = $guides->map(function ($guide, $index) use ($defaultImages) {
            if (empty($guide->avatar)) {
                $guide->avatar = $defaultImages[$index % count($defaultImages)];
            } elseif (!str_contains($guide->avatar, '/')) {
                $guide->avatar = 'admin/assets/images/user-profile/' . $guide->avatar;
            }

            $guide->designation = 'Travel Guide';
            $guide->display_name = $guide->full_name ?? $guide->name ?? 'Guide';
            $guide->description = $guide->description ?? 'Hướng dẫn viên du lịch chuyên nghiệp, am hiểu địa phương và sẵn sàng mang đến trải nghiệm tốt nhất cho khách hàng.';
            return $guide;
        });

        return view('clients.huong-dan-vien', compact('title', 'guides'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
