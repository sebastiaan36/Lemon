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
        Schema::table('case_studies', function (Blueprint $table) {
            $table->text('callout_title')->nullable()->after('story_images');
            $table->text('secondary_story_body')->nullable()->after('callout_title');
            $table->string('secondary_story_media')->nullable()->after('secondary_story_body');
            $table->string('secondary_story_button_text')->nullable()->after('secondary_story_media');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_studies', function (Blueprint $table) {
            $table->dropColumn([
                'callout_title',
                'secondary_story_body',
                'secondary_story_media',
                'secondary_story_button_text',
            ]);
        });
    }
};
