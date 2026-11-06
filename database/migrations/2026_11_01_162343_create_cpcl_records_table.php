<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cpcl_records', function (Blueprint $table): void {
            $table->id();
            $table->string('no', 30)->nullable();
            $table->string('nama_di', 150);
            $table->string('kewenangan', 100)->nullable();
            $table->string('keterangan', 255)->nullable();
            $table->string('provinsi', 100);
            $table->string('kabupaten', 100);
            $table->string('kecamatan', 100);
            $table->string('desa', 100);
            $table->string('kel_tani', 150);
            $table->string('nama_ketua', 150)->nullable();
            $table->string('realisasi', 100)->nullable();
            $table->decimal('luas_ha', 12, 2)->nullable();
            $table->decimal('x', 12, 7)->nullable();
            $table->decimal('y', 12, 7)->nullable();
            $table->boolean('aktif')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['provinsi', 'kabupaten', 'kecamatan', 'desa', 'kel_tani'], 'cpcl_identity_unique');
            $table->index(['provinsi', 'kabupaten']);
            $table->index(['kel_tani', 'nama_di']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cpcl_records');
    }
};
