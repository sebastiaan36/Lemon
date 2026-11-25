<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('floating_pills', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('href');
            $table->string('page');
            $table->string('start_section');
            $table->string('end_section');
            $table->json('avatars')->nullable();
            $table->boolean('use_team_avatars')->default(false);
            $table->unsignedSmallInteger('desktop_bottom')->default(24);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'page']);
        });

        // Klantfeedback 25-11: alleen "Meet the team" en "Tell us about your project".
        // De checklist-, jobs- en insights-pills komen voorlopig niet terug.
        $now = now();
        $aboutCta = Schema::hasTable('about_page_contents')
            ? DB::table('about_page_contents')->first(['cta_text', 'cta_href'])
            : null;
        $tellUs = fn (string $page, string $start, string $end, int $order): array => [
            'label' => 'Tell us about your project',
            'href' => '/contact',
            'page' => $page,
            'start_section' => $start,
            'end_section' => $end,
            'avatars' => null,
            'use_team_avatars' => false,
            'desktop_bottom' => 24,
            'is_active' => true,
            'sort_order' => $order,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        DB::table('floating_pills')->insert([
            [
                'label' => 'Meet the team',
                'href' => '#team',
                'page' => 'home',
                'start_section' => 'about',
                'end_section' => 'about',
                'avatars' => null,
                'use_team_avatars' => true,
                'desktop_bottom' => 11,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                ...$tellUs('about', 'intro-quote', 'team-photos', 2),
                'label' => ($aboutCta?->cta_text ?: 'Tell us about your project'),
                'href' => ($aboutCta?->cta_href ?: '/contact'),
            ],
            $tellUs('cxbyex', 'narrative', 'brands', 3),
            $tellUs('cases-index', 'filter', 'cases', 4),
            $tellUs('cases-show', 'meta', 'more-cases', 5),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('floating_pills');
    }
};
