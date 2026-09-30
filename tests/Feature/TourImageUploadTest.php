<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TourImageUploadTest extends TestCase
{
    private array $createdFiles = [];
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('tbl_tours', fn (Blueprint $t) => $t->increments('tourId'));
        (require database_path('migrations/2026_09_16_000002_create_tbl_temp_images_table.php'))->up();
        Schema::create('tbl_images', function (Blueprint $t) { $t->increments('id'); $t->integer('tourId'); $t->string('imageURL'); });
        DB::table('tbl_tours')->insert(['tourId' => 1]);
        DB::table('tbl_images')->insert(['tourId' => 1, 'imageURL' => 'existing.jpg']);
        $this->withSession(['admin' => true]);
    }

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $file) {
            $path = public_path('admin/assets/images/gallery-tours/' . basename($file));
            if (is_file($path)) unlink($path);
        }
        parent::tearDown();
    }

    public function test_five_same_named_uploads_have_unique_saved_names(): void
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a2ioAAAAASUVORK5CYII=');
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/admin/add-temp-images', ['tourId' => 1, 'image' => UploadedFile::fake()->createWithContent('tour.png', $png)]);
            $response->assertOk()->assertJson(['success' => true]);
            $name = $response->json('data.filename');
            $this->createdFiles[] = $name;
            $this->assertFileExists(public_path('admin/assets/images/gallery-tours/' . $name));
        }
        $this->assertCount(5, array_unique($this->createdFiles));
        $this->assertSame(5, DB::table('tbl_temp_images')->count());
        $this->assertSame('existing.jpg', DB::table('tbl_images')->value('imageURL'));
    }

    public function test_invalid_upload_and_incomplete_image_selection_do_not_replace_existing_images(): void
    {
        $this->postJson('/admin/add-temp-images', ['tourId' => 1, 'image' => UploadedFile::fake()->createWithContent('bad.png', 'not an image')])->assertUnprocessable();
        $this->postJson('/admin/edit-tour', ['tourId' => 1, 'images' => ['existing.jpg'], 'price_adult' => '3590000', 'price_child' => '2490000'])->assertUnprocessable()->assertJsonValidationErrors('images');
        $this->assertSame('existing.jpg', DB::table('tbl_images')->value('imageURL'));
        $this->assertSame(0, DB::table('tbl_temp_images')->count());
    }

    public function test_six_megabyte_image_is_accepted_but_over_ten_is_rejected(): void
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a2ioAAAAASUVORK5CYII=');
        $response = $this->postJson('/admin/add-temp-images', ['tourId' => 1,
            'image' => UploadedFile::fake()->createWithContent('large.png', $png . str_repeat("\0", 6 * 1024 * 1024))]);
        $response->assertOk()->assertJson(['success' => true]);
        $this->createdFiles[] = $response->json('data.filename');
        $this->postJson('/admin/add-temp-images', ['tourId' => 1,
            'image' => UploadedFile::fake()->createWithContent('oversize.png', $png . str_repeat("\0", 11 * 1024 * 1024))])->assertUnprocessable()->assertJsonValidationErrors('image');
        $this->assertSame(1, DB::table('tbl_temp_images')->count());
    }
}
