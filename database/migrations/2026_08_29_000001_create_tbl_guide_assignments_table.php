<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tbl_guide_assignments')) {
            return;
        }

        Schema::create('tbl_guide_assignments', function (Blueprint $table) {
            $table->id('assignment_id');
            $table->unsignedBigInteger('guide_id');
            $table->unsignedBigInteger('tour_id');
            $table->string('meeting_point')->nullable();
            $table->date('departure_date')->nullable();
            $table->string('tour_status')->default('assigned');
            $table->text('incident_note')->nullable();
            $table->text('summary_report')->nullable();
            $table->timestamps();

            $table->unique(['guide_id', 'tour_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_guide_assignments');
    }
};
