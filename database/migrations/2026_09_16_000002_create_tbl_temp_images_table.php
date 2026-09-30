<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('tbl_temp_images')) {
            Schema::create('tbl_temp_images', function (Blueprint $table) {
                $table->bigIncrements('imageTempId');
                $table->unsignedBigInteger('tourId')->index();
                $table->string('imageTempURL')->unique();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_temp_images');
    }
};
