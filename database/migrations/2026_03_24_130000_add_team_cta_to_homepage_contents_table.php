<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homepage_contents', function (Blueprint $table) {
            $table->string('team_button_text')->nullable()->after('team_description');
            $table->string('team_button_href')->nullable()->after('team_button_text');
        });

        DB::table('homepage_contents')->update([
            'team_button_text' => 'About us',
            'team_button_href' => '#about',
        ]);
    }

    public function down(): void
    {
        Schema::table('homepage_contents', function (Blueprint $table) {
            $table->dropColumn(['team_button_text', 'team_button_href']);
        });
    }
};
