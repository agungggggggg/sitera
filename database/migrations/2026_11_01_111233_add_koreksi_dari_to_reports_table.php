<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->foreignId('koreksi_dari')->nullable()->after('user_id')->constrained('reports')->nullOnDelete();
            $table->index('koreksi_dari');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['koreksi_dari']);
            $table->dropIndex(['koreksi_dari']);
            $table->dropColumn('koreksi_dari');
        });
    }
};
