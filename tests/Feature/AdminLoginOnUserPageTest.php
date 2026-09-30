<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminLoginOnUserPageTest extends TestCase
{
    public function test_admin_username_on_user_login_page_redirects_to_admin_login(): void
    {
        $data = [
            'username' => 'admin',
            'password' => md5('admin123'),
            'email' => 'admin@travela.local',
        ];

        if (DB::getSchemaBuilder()->hasColumn('tbl_admin', 'created_at')) {
            $data['created_at'] = now();
        }

        if (DB::getSchemaBuilder()->hasColumn('tbl_admin', 'updated_at')) {
            $data['updated_at'] = now();
        }

        DB::table('tbl_admin')->updateOrInsert(['username' => 'admin'], $data);

        $response = $this->from('/login')->post('/login', [
            'username' => 'admin',
            'password' => 'admin123',
        ]);

        $response->assertRedirect('/admin/login');
        $this->assertNull(session('admin'));
    }

    public function test_admin_username_on_user_login_form_fields_redirects_to_admin_login(): void
    {
        $data = [
            'username' => 'admin',
            'password' => md5('admin123'),
            'email' => 'admin@travela.local',
        ];

        if (DB::getSchemaBuilder()->hasColumn('tbl_admin', 'created_at')) {
            $data['created_at'] = now();
        }

        if (DB::getSchemaBuilder()->hasColumn('tbl_admin', 'updated_at')) {
            $data['updated_at'] = now();
        }

        DB::table('tbl_admin')->updateOrInsert(['username' => 'admin'], $data);

        $response = $this->from('/login')->post('/login', [
            'username_login' => 'admin',
            'password_login' => 'admin123',
        ]);

        $response->assertRedirect('/admin/login');
        $this->assertNull(session('admin'));
    }
}
