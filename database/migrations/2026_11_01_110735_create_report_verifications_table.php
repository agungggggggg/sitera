<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('verifier_id')->constrained('users')->restrictOnDelete();
            $table->enum('hasil', ['SESUAI', 'TIDAK_SESUAI']);
            $table->text('catatan')->nullable();
            $table->timestamp('diperiksa_pada');
            $table->timestamps();
            $table->unique('report_id');
            $table->index(['verifier_id', 'hasil']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_verifications');
    }
};
