<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GuideAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        \Illuminate\Support\Facades\Schema::create('tbl_admin', function (\Illuminate\Database\Schema\Blueprint $t) {
            $t->increments('adminid'); $t->string('username')->unique(); $t->string('password');
            $t->string('role')->default('guide'); $t->boolean('guideEnabled')->default(true);
            $t->string('email')->nullable(); $t->string('full_name')->nullable(); $t->string('avatar')->nullable();
            $t->text('description')->nullable(); $t->timestamps();
        });
        \Illuminate\Support\Facades\Schema::create('tbl_guide_assignments', function (\Illuminate\Database\Schema\Blueprint $t) {
            $t->increments('assignment_id'); $t->integer('guide_id'); $t->integer('tour_id');
            $t->integer('groupNumber')->default(1); $t->string('tour_status')->default('assigned');
        });
        DB::table('tbl_admin')->insert([
            ['username' => 'admin', 'password' => md5('admin123'), 'role' => 'admin'],
            ['username' => 'guide', 'password' => md5('guide123'), 'role' => 'guide'],
        ]);
        \Illuminate\Support\Facades\Schema::create('tbl_contact', function (\Illuminate\Database\Schema\Blueprint $t) {
            $t->increments('contactId'); $t->string('isReply')->default('n');
        });
    }
    public function test_guide_login_page_is_accessible(): void
    {
        $response = $this->get('/guide/login');

        $response->assertStatus(200);
    }

    public function test_guide_dashboard_requires_login(): void
    {
        $response = $this->get('/guide/dashboard');

        $response->assertRedirect('/guide/login');
    }

    public function test_demo_guide_account_can_login(): void
    {
        $response = $this->from('/guide/login')->post('/guide/login-account', [
            'username' => 'guide',
            'password' => 'guide123',
        ]);

        $response->assertRedirect('/guide/dashboard');
        $this->assertEquals('guide', session('guide'));
    }

    public function test_admin_can_create_guide_account(): void
    {
        session(['admin' => 'admin']);
        $username = 'guide_test_' . uniqid();

        $response = $this->from('/admin/guide-assignment')->post('/admin/add-guide', [
            'username' => $username,
            'password' => 'guide123',
            'email' => $username . '@test.com',
        ]);

        $response->assertRedirect('/admin/guide-assignment');
        $this->assertDatabaseHas('tbl_admin', ['username' => $username]);
    }

    public function test_travel_guides_page_excludes_admin_account(): void
    {
        $response = $this->get('/travel-guides');

        $response->assertStatus(200);
        $response->assertDontSeeText('ADMIN');
    }

    public function test_admin_can_save_full_guide_profile_fields(): void
    {
        session(['admin' => 'admin']);
        $username = 'guide_profile_' . uniqid();

        $this->from('/admin/guide-assignment')->post('/admin/add-guide', [
            'username' => $username,
            'password' => 'guide123',
            'full_name' => 'Đậu Cao Thanh',
            'email' => $username . '@test.com',
            'description' => 'Hướng dẫn viên du lịch chuyên nghiệp.',
        ]);

        $this->assertDatabaseHas('tbl_admin', [
            'username' => $username,
            'full_name' => 'Đậu Cao Thanh',
            'email' => $username . '@test.com',
            'description' => 'Hướng dẫn viên du lịch chuyên nghiệp.',
        ]);
    }

    public function test_admin_can_delete_guide_account(): void
    {
        session(['admin' => 'admin']);
        $guideData = [
            'username' => 'guide_delete_' . uniqid(),
            'password' => md5('guide123'),
            'email' => 'deleteguide@test.com',
            'full_name' => 'Guide Delete',
            'description' => 'Guide test xóa',
        ];

        if (DB::getSchemaBuilder()->hasColumn('tbl_admin', 'role')) {
            $guideData['role'] = 'guide';
        }

        if (DB::getSchemaBuilder()->hasColumn('tbl_admin', 'created_at')) {
            $guideData['created_at'] = now();
        }

        if (DB::getSchemaBuilder()->hasColumn('tbl_admin', 'updated_at')) {
            $guideData['updated_at'] = now();
        }

        $guideId = DB::table('tbl_admin')->insertGetId($guideData);

        $response = $this->from('/admin/guide-assignment')->delete('/admin/guide-assignment/' . $guideId);

        $response->assertRedirect('/admin/guide-assignment');
        $this->assertDatabaseMissing('tbl_admin', ['adminId' => $guideId]);
    }

    public function test_admin_can_update_guide_account(): void
    {
        session(['admin' => 'admin']);
        $guideData = [
            'username' => 'guide_update_' . uniqid(),
            'password' => md5('guide123'),
            'email' => 'updateguide@test.com',
            'full_name' => 'Guide Old',
            'description' => 'Old description',
        ];

        if (DB::getSchemaBuilder()->hasColumn('tbl_admin', 'created_at')) {
            $guideData['created_at'] = now();
        }

        if (DB::getSchemaBuilder()->hasColumn('tbl_admin', 'updated_at')) {
            $guideData['updated_at'] = now();
        }

        $guideId = DB::table('tbl_admin')->insertGetId($guideData);

        $response = $this->from('/admin/guide-assignment')->put('/admin/guide-assignment/' . $guideId, [
            'username' => 'guide_update_new',
            'full_name' => 'Guide Updated',
            'email' => 'updatedguide@test.com',
            'description' => 'New guide description',
        ]);

        $response->assertRedirect('/admin/guide-assignment');
        $this->assertDatabaseHas('tbl_admin', ['adminId' => $guideId, 'full_name' => 'Guide Updated']);
    }

    public function test_locked_guide_cannot_login_or_keep_existing_session(): void
    {
        $id = DB::table('tbl_admin')->where('username', 'guide')->value('adminId');
        $this->withSession(['admin' => 'admin', 'guide' => 'guide', 'guide_id' => $id])->post('/admin/guides/'.$id.'/status', ['enabled' => 0])->assertRedirect();
        $this->get('/guide/dashboard')->assertRedirect('/guide/login')->assertSessionMissing('guide');
        $this->post('/guide/login-account', ['username' => 'guide', 'password' => 'guide123'])->assertRedirect('/guide/login');
        $this->assertCount(0, app(\App\Models\admin\DuLieuPhanCongHuongDanVien::class)->getGuides());
        $this->post('/admin/guides/'.$id.'/status', ['enabled' => 1])->assertRedirect();
        $this->post('/guide/login-account', ['username' => 'guide', 'password' => 'guide123'])->assertRedirect('/guide/dashboard');
    }

    public function test_guide_with_history_cannot_be_deleted_and_admin_is_protected(): void
    {
        $id = DB::table('tbl_admin')->where('username', 'guide')->value('adminId');
        DB::table('tbl_guide_assignments')->insert(['guide_id' => $id, 'tour_id' => 10]);
        $this->withSession(['admin' => 'admin'])->deleteJson('/admin/guide-assignment/'.$id)->assertUnprocessable();
        $this->assertDatabaseHas('tbl_admin', ['adminId' => $id]);
        $this->assertDatabaseCount('tbl_guide_assignments', 1);
        $adminId = DB::table('tbl_admin')->where('username', 'admin')->value('adminId');
        $this->deleteJson('/admin/guide-assignment/'.$adminId)->assertNotFound();
        $this->postJson('/admin/guides/'.$adminId.'/status', ['enabled' => 0])->assertNotFound();
    }

    public function test_management_search_filters_and_paginates_without_admin_accounts(): void
    {
        for ($i = 0; $i < 20; $i++) DB::table('tbl_admin')->insert(['username' => 'sample_'.$i, 'password' => 'hash', 'role' => 'guide', 'full_name' => 'Guide '.$i]);
        $this->withSession(['admin' => 'admin'])->get('/admin/guides?q=sample_')->assertOk()->assertViewHas('guides', fn ($rows) => $rows->count() === 15 && $rows->total() === 20);
        $this->get('/admin/guides?q=sample_19')->assertOk()->assertSee('sample_19')->assertDontSee('sample_18');
        $this->get('/admin/guides?status=locked')->assertOk()->assertViewHas('guides', fn ($rows) => $rows->total() === 0);
    }

    public function test_locked_guide_cannot_receive_assignment_even_with_direct_request(): void
    {
        $id = DB::table('tbl_admin')->where('username', 'guide')->value('adminId');
        DB::table('tbl_admin')->where('adminId', $id)->update(['guideEnabled' => false]);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(\App\Models\admin\DuLieuPhanCongHuongDanVien::class)->assignTour(['guide_id' => $id, 'tour_id' => 1, 'groupNumber' => 1]);
    }

    public function test_legacy_database_without_role_supports_management_and_login(): void
    {
        DB::statement('ALTER TABLE tbl_admin DROP COLUMN role');
        $this->withSession(['admin' => 'admin'])->get('/admin/guides')->assertOk()->assertViewHas('guides', fn ($rows) => $rows->total() === 1);
        $id = DB::table('tbl_admin')->where('username', 'guide')->value('adminId');
        $this->post('/guide/login-account', ['username' => 'guide', 'password' => 'guide123'])->assertRedirect('/guide/dashboard')->assertSessionHas('guide_id', $id);
        $this->post('/admin/guides/'.$id.'/status', ['enabled' => 0])->assertRedirect();
        $this->post('/guide/login-account', ['username' => 'guide', 'password' => 'guide123'])->assertRedirect('/guide/login');
    }

    public function test_management_requires_admin_session(): void
    {
        $this->get('/admin/guides')->assertRedirect('/admin/login');
        $this->post('/admin/guides/2/status', ['enabled' => 0])->assertRedirect('/admin/login');
        $this->assertDatabaseHas('tbl_admin', ['username' => 'guide', 'guideEnabled' => true]);
    }
}
