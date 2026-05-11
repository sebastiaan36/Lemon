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
        // The existing varchar columns (hero_media, intro_logo, highlight_media, campaign_media)
        // and TEXT columns (gallery_items, story_images) will now store Curator UUIDs/arrays.
        // We only need to add the homepage video/photo columns that previously had separate names.
        Schema::table('case_studies', function (Blueprint $table) {
            $table->string('homepage_video_id')->nullable()->after('photo');
            $table->string('homepage_photo_id')->nullable()->after('homepage_video_id');
        });
    }

    public function down(): void
    {
        Schema::table('case_studies', function (Blueprint $table) {
            $table->dropColumn(['homepage_video_id', 'homepage_photo_id']);
        });
    }
};
