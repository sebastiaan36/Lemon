<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_page_contents', function (Blueprint $table) {
            $table->id();

            // Hero (yellow section)
            $table->string('hero_title')->default('This is Lemon');
            $table->text('hero_description')->nullable();

            // Intro quote (over background image)
            $table->string('intro_quote_line1')->default('And Lemon?');
            $table->string('intro_quote_line2')->default('We keep it fresh.');
            $table->string('intro_bg_image')->nullable(); // Curator UUID

            // CTA button
            $table->string('cta_text')->default('Tell us about your project');
            $table->string('cta_href')->default('/contact');

            // Credible Creativity section (dark)
            $table->string('credible_title_line1')->default('Credible');
            $table->string('credible_title_line2')->default('Creativity');
            $table->text('credible_body')->nullable();
            $table->json('credible_cases')->nullable(); // [{image: curator_uuid, client_name: string}]

            // Total brand growth section (dark)
            $table->string('growth_title_line1')->default('Total brand');
            $table->string('growth_title_line2')->default('growth');
            $table->text('growth_body')->nullable();
            $table->string('growth_image')->nullable(); // Curator UUID
            $table->string('growth_client_name')->nullable();

            // Services section (dark)
            $table->json('services')->nullable(); // [{name, description, tags[]}]

            // "We're keeping ancient actual" section
            $table->string('ancient_title_line1')->default("We're keeping");
            $table->string('ancient_title_line2')->default('ancient actual');
            $table->text('ancient_body')->nullable();
            $table->string('ancient_image')->nullable(); // Curator UUID

            // International community section
            $table->string('international_title_line1')->default('International');
            $table->string('international_title_line2')->default('community');
            $table->text('international_body')->nullable();

            // Team / people photos
            $table->json('team_photos')->nullable(); // [curator_uuid, ...]

            // Join team button
            $table->string('join_team_text')->default('Join our team');
            $table->string('join_team_href')->default('/careers');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_page_contents');
    }
};
