<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (['homepage_contents', 'about_page_contents', 'case_studies'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->string('seo_title')->nullable();
                $table->text('meta_description')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['homepage_contents', 'about_page_contents', 'case_studies'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropColumn(['seo_title', 'meta_description']);
            });
        }
    }
};
