<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tbl_booking')) {
            return;
        }

        if (Schema::hasColumn('tbl_booking', 'useId') && !Schema::hasColumn('tbl_booking', 'userId')) {
            DB::statement('ALTER TABLE tbl_booking CHANGE useId userId INT NOT NULL');
        }

        foreach (['address', 'fullName', 'email', 'phoneNumber'] as $column) {
            if (!Schema::hasColumn('tbl_booking', $column)) {
                Schema::table('tbl_booking', function ($table) use ($column) {
                    $table->string($column)->nullable();
                });
            }
        }
    }

    public function down()
    {
        if (!Schema::hasTable('tbl_booking')) {
            return;
        }

        if (Schema::hasColumn('tbl_booking', 'userId') && !Schema::hasColumn('tbl_booking', 'useId')) {
            DB::statement('ALTER TABLE tbl_booking CHANGE userId useId INT NOT NULL');
        }
    }
};