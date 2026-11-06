<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_attachments', function (Blueprint $table): void {
            $table->string('kategori', 30)->nullable()->after('report_id');
            $table->unsignedInteger('versi')->default(1)->after('kategori');
            $table->index(['report_id', 'versi', 'kategori'], 'attachment_report_version_category_index');
        });
    }

    public function down(): void
    {
        Schema::table('report_attachments', function (Blueprint $table): void {
            $table->dropIndex('attachment_report_version_category_index');
            $table->dropColumn(['kategori', 'versi']);
        });
    }
};
