<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('case_studies', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->string('client_name')->nullable()->after('slug');
            $table->string('accent_color')->nullable()->after('client_name');
            $table->string('hero_title')->nullable()->after('accent_color');
            $table->string('hero_subtitle')->nullable()->after('hero_title');
            $table->string('hero_media')->nullable()->after('hero_subtitle');
            $table->string('hero_duration')->nullable()->after('hero_media');
            $table->string('intro_logo')->nullable()->after('hero_duration');
            $table->json('touchpoints')->nullable()->after('intro_logo');
            $table->string('overview_title')->nullable()->after('touchpoints');
            $table->text('overview_body')->nullable()->after('overview_title');
            $table->json('gallery_items')->nullable()->after('overview_body');
            $table->string('highlight_title')->nullable()->after('gallery_items');
            $table->text('highlight_body')->nullable()->after('highlight_title');
            $table->string('highlight_media')->nullable()->after('highlight_body');
            $table->string('highlight_button_text')->nullable()->after('highlight_media');
            $table->string('campaign_title')->nullable()->after('highlight_button_text');
            $table->text('campaign_body')->nullable()->after('campaign_title');
            $table->string('campaign_media')->nullable()->after('campaign_body');
            $table->string('story_title')->nullable()->after('campaign_media');
            $table->text('story_body')->nullable()->after('story_title');
            $table->json('story_images')->nullable()->after('story_body');
            $table->string('results_heading')->nullable()->after('story_images');
            $table->json('results_stats')->nullable()->after('results_heading');
            $table->string('optional_panel_title')->nullable()->after('results_stats');
            $table->text('optional_panel_body')->nullable()->after('optional_panel_title');
            $table->string('optional_panel_button_text')->nullable()->after('optional_panel_body');
        });

        $records = DB::table('case_studies')->select('id', 'name', 'photo', 'video')->get();

        foreach ($records as $record) {
            $baseSlug = Str::slug($record->name ?: 'case');
            $slug = $baseSlug !== '' ? $baseSlug : 'case-'.$record->id;
            $suffix = 1;

            while (DB::table('case_studies')->where('slug', $slug)->where('id', '!=', $record->id)->exists()) {
                $slug = $baseSlug.'-'.$suffix;
                $suffix++;
            }

            DB::table('case_studies')->where('id', $record->id)->update([
                'slug' => $slug,
                'client_name' => $record->name,
                'accent_color' => '#0A7949',
                'hero_title' => $record->name,
                'hero_subtitle' => $record->name,
                'hero_media' => $record->video ?: $record->photo,
                'overview_title' => 'Case story',
                'overview_body' => null,
                'gallery_items' => json_encode([]),
                'highlight_title' => null,
                'highlight_body' => null,
                'story_images' => json_encode([]),
                'results_heading' => 'Results',
                'results_stats' => json_encode([]),
            ]);
        }

        Schema::table('case_studies', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('case_studies', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn([
                'slug',
                'client_name',
                'accent_color',
                'hero_title',
                'hero_subtitle',
                'hero_media',
                'hero_duration',
                'intro_logo',
                'touchpoints',
                'overview_title',
                'overview_body',
                'gallery_items',
                'highlight_title',
                'highlight_body',
                'highlight_media',
                'highlight_button_text',
                'campaign_title',
                'campaign_body',
                'campaign_media',
                'story_title',
                'story_body',
                'story_images',
                'results_heading',
                'results_stats',
                'optional_panel_title',
                'optional_panel_body',
                'optional_panel_button_text',
            ]);
        });
    }
};
