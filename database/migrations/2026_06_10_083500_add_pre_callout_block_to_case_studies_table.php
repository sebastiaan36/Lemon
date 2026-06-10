<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('case_studies', function (Blueprint $table) {
            $table->text('pre_callout_title')->nullable()->after('story_media');
            $table->text('pre_callout_body')->nullable()->after('pre_callout_title');
            $table->json('pre_callout_gallery_items')->nullable()->after('pre_callout_body');
        });
    }

    public function down(): void
    {
        Schema::table('case_studies', function (Blueprint $table) {
            $table->dropColumn([
                'pre_callout_title',
                'pre_callout_body',
                'pre_callout_gallery_items',
            ]);
        });
    }
};
