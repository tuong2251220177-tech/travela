<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tbl_users')) {
            return;
        }

        if (Schema::hasColumn('tbl_users', 'userld') && !Schema::hasColumn('tbl_users', 'userId')) {
            DB::statement('ALTER TABLE tbl_users CHANGE userld userId INT NOT NULL AUTO_INCREMENT');
        }
    }

    public function down()
    {
        if (!Schema::hasTable('tbl_users')) {
            return;
        }

        if (Schema::hasColumn('tbl_users', 'userId') && !Schema::hasColumn('tbl_users', 'userld')) {
            DB::statement('ALTER TABLE tbl_users CHANGE userId userld INT NOT NULL AUTO_INCREMENT');
        }
    }
};