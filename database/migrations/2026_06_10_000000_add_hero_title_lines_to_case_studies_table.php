<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('case_studies', function (Blueprint $table) {
            $table->string('hero_title_line_1')->nullable()->after('hero_title');
            $table->string('hero_title_line_2')->nullable()->after('hero_title_line_1');
            $table->string('hero_title_line_3')->nullable()->after('hero_title_line_2');
        });

        DB::table('case_studies')
            ->whereNotNull('hero_title')
            ->update([
                'hero_title_line_1' => DB::raw('hero_title'),
            ]);
    }

    public function down(): void
    {
        Schema::table('case_studies', function (Blueprint $table) {
            $table->dropColumn([
                'hero_title_line_1',
                'hero_title_line_2',
                'hero_title_line_3',
            ]);
        });
    }
};
