<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_admin')) {
            return;
        }

        Schema::table('tbl_admin', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_admin', 'full_name')) {
                $table->string('full_name')->nullable()->after('username');
            }

            if (!Schema::hasColumn('tbl_admin', 'avatar')) {
                $table->string('avatar')->nullable()->after('email');
            }

            if (!Schema::hasColumn('tbl_admin', 'description')) {
                $table->text('description')->nullable()->after('avatar');
            }

            if (!Schema::hasColumn('tbl_admin', 'phone')) {
                $table->string('phone')->nullable()->after('description');
            }

            if (!Schema::hasColumn('tbl_admin', 'address')) {
                $table->string('address')->nullable()->after('phone');
            }

            if (!Schema::hasColumn('tbl_admin', 'role')) {
                $table->string('role')->default('admin')->after('address');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('tbl_admin')) {
            return;
        }

        Schema::table('tbl_admin', function (Blueprint $table) {
            $columnsToDrop = ['full_name', 'avatar', 'description', 'phone', 'address', 'role'];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('tbl_admin', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
