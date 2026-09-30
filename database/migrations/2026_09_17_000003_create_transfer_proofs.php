<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('tbl_transfer_proofs', function (Blueprint $t) {
            $t->bigIncrements('id'); $t->unsignedBigInteger('bookingId')->index();
            $t->string('path'); $t->timestamp('created_at');
        });
    }
    public function down(): void { Schema::dropIfExists('tbl_transfer_proofs'); }
};
