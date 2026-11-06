<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('nomor_tiket', 20)->unique();
            $table->enum('status', ['DRAF', 'TERKIRIM'])->default('DRAF');
            $table->date('tanggal_kejadian');
            $table->string('provinsi', 100);
            $table->string('kabupaten', 100);
            $table->string('kecamatan', 100)->nullable();
            $table->string('desa', 100)->nullable();
            $table->string('nama_saluran', 150)->nullable();
            $table->string('jenis_kerusakan', 50);
            $table->string('deskripsi', 500);
            $table->decimal('panjang_terdampak_m', 12, 2)->nullable();
            $table->decimal('luas_terdampak_ha', 12, 2)->nullable();
            $table->string('koordinat', 100)->nullable();
            $table->text('catatan')->nullable();
            $table->enum('sumber_isian', ['MANUAL', 'AI_SEBAGIAN', 'AI'])->default('MANUAL');
            $table->timestamp('dikirim_pada')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['provinsi', 'kabupaten']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
