<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('provinsi', 100)->nullable()->after('role_id');
            $table->string('kabupaten', 100)->nullable()->after('provinsi');
            $table->index(['provinsi', 'kabupaten']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['provinsi', 'kabupaten']);
            $table->dropColumn(['provinsi', 'kabupaten']);
        });
    }
};
