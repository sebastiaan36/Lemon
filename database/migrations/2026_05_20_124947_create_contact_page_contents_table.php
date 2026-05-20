<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_page_contents', function (Blueprint $table) {
            $table->id();

            $table->string('hero_title')->default('Get in touch');
            $table->string('hero_bg_image')->nullable();
            $table->string('hero_address')->nullable();
            $table->string('hero_phone')->nullable();
            $table->string('hero_email')->nullable();

            $table->text('intro_text')->nullable();

            $table->json('team_members')->nullable();

            $table->string('client_logos_label')->default('These brands called us before');
            $table->json('client_logos')->nullable();

            $table->string('join_intro_text')->default('We are always looking for new talent');
            $table->string('join_title_line1')->default('Join');
            $table->string('join_title_line2')->default('lemon');
            $table->json('join_jobs')->nullable();
            $table->string('join_button_text')->default('All jobs');
            $table->string('join_button_href')->nullable();

            $table->string('seo_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_page_contents');
    }
};
