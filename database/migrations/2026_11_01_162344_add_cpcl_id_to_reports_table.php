<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table): void {
            $table->foreignId('cpcl_id')->nullable()->after('user_id')->constrained('cpcl_records')->nullOnDelete();
            $table->unsignedInteger('versi')->default(1)->after('status');
            $table->index(['cpcl_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table): void {
            $table->dropIndex(['cpcl_id', 'status']);
            $table->dropConstrainedForeignId('cpcl_id');
            $table->dropColumn('versi');
        });
    }
};
