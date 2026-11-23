<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('industries_page_contents', function (Blueprint $table) {
            $table->string('cta_avatar')->nullable()->after('nav_label');
        });
    }

    public function down(): void
    {
        Schema::table('industries_page_contents', function (Blueprint $table) {
            $table->dropColumn('cta_avatar');
        });
    }
};
