<?php

namespace App\Http\Controllers\clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DieuKhienLienHe extends Controller
{
    public function index()
    {
        $title = 'Liên hệ';
        return view('clients.lien-he', compact('title'));
    }

    public function createContact(Request $req){
        $name = $req->name;
        $phone = $req->phone_number;
        $email = $req->email;
        $message = $req->message;

        $dataContact = [
            'fullName'    => $name,
            'phoneNumber' => $phone,
            'email'       => $email,
            'message'     => $message
        ];

        $createContact = DB::table('tbl_contact')->insert($dataContact); 

        if($createContact){
            toastr()->success('Gửi thành công. Chúng tôi sẽ sớm liên hệ tới bạn!');
        }else{
            toastr()->error('Có lỗi xảy ra. Xin vui lòng thử lại');
        }
        return redirect()->back();

    }
}
