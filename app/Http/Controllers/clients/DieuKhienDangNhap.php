<?php

namespace App\Http\Controllers\clients;

use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\clients\DuLieuDangNhap as Login;
use App\Models\clients\DuLieuNguoiDung as User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class DieuKhienDangNhap extends Controller
{

    private $login;
    protected $user;

    public function __construct()
    {
        $this->login = new Login();
        $this->user = new User();
    }
    public function index()
    {
        $title = 'Đăng nhập';
        return view('clients.dang-nhap', compact('title'));
    }


    public function register(Request $request)
    {
        try {
            $username_regis = $request->username_register;
            $email = $request->email_register;
            $password_regis = $request->password_register;

            // Validate input
            if (empty($username_regis) || empty($email) || empty($password_regis)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng điền đầy đủ thông tin!'
                ], 400);
            }

            $checkAccountExist = $this->login->checkUserExist($username_regis, $email);
            if ($checkAccountExist) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tên người dùng hoặc email đã tồn tại!'
                ], 400);
            }

            $dataInsert = [
                    'userName' => $username_regis,
                'email'    => $email,
                    'passWord' => md5($password_regis),
                'isActive' => 'y',
                'createdDate' => now()->toDateString(),
                'updatedDate' => now()->toDateString()
            ];

            $result = $this->login->registerAcount($dataInsert);
            
            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi khi tạo tài khoản. Vui lòng thử lại!'
                ], 500);
            }

            // Email sending skipped for faster registration
            // Tài khoản được tạo ngay lập tức

            return response()->json([
                'success' => true,
                'message' => 'Đăng ký thành công! Bạn có thể đăng nhập ngay.'
            ], 200);
            
        } catch (\Exception $e) {
            logger()->error('Registration error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra. Vui lòng thử lại sau!'
            ], 500);
        }
    }

    public function sendActivationEmail($email, $token)
    {
        $activation_link = route('activate.account', ['token' => $token]);

        Mail::send('clients.mail.thu-kich-hoat', ['link' => $activation_link], function ($message) use ($email) {
            $message->to($email);
            $message->subject('Kích hoạt tài khoản của bạn');
        });
    }

    public function activateAccount($token)
    {
        $user = $this->login->getUserByToken($token);
        if ($user) {
            $this->login->activateUserAccount($token);

            return redirect('/login')->with('message', 'Tài khoản của bạn đã được kích hoạt!');
        } else {
            return redirect('/login')->with('error', 'Mã kích hoạt không hợp lệ!');
        }
    }

    //Xử lý người dùng đăng nhập
    public function login(Request $request)
    {
        $username = $request->input('username', $request->input('username_login'));
        $password = $request->input('password', $request->input('password_login'));

        $data_login = [
            'username' => $username,
            'password' => md5((string) $password)
        ];

        $user_login = $this->login->login($data_login);
        $userId = $this->user->getUserId($username);
        $user = $this->user->getUser($userId);

        if ($user_login != null) {
            $request->session()->put('username', $username);
            $request->session()->put('avatar', $user->avatar ?? 'user_avatar.jpg');
            toastr()->success("Đăng nhập thành công!", 'Thông báo');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đăng nhập thành công!',
                    'redirectUrl' => route('home'),
                ]);
            }

            return redirect()->route('home');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Thông tin tài khoản không chính xác!',
            ]);
        }

        toastr()->error('Thông tin tài khoản không chính xác!', 'Thông báo');
        return redirect()->back();
    }

    //Xử lý đăng xuất
    public function logout(Request $request)
    {
        // Xóa session lưu trữ thông tin người dùng đã đăng nhập
        $request->session()->forget('username');
        $request->session()->forget('avatar');
        $request->session()->forget('userId');
        toastr()->success("Đăng xuất thành công!",'Thông báo');
        return redirect()->route('home');
    }


}
