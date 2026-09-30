<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tbl_users') && !Schema::hasColumn('tbl_users', 'avatar')) {
            Schema::table('tbl_users', function ($table) {
                $table->string('avatar')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('tbl_users') && Schema::hasColumn('tbl_users', 'avatar')) {
            Schema::table('tbl_users', function ($table) {
                $table->dropColumn('avatar');
            });
        }
    }
};