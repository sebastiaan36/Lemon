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
        Schema::create('cxbyex_page_contents', function (Blueprint $table) {
            $table->id();

            $table->string('hero_bg_image')->nullable();
            $table->text('hero_subtitle')->nullable();

            $table->text('narrative_text')->nullable();

            $table->string('case_bg_image')->nullable();
            $table->text('case_body_text')->nullable();
            $table->string('case_client_name')->nullable();
            $table->json('case_tags')->nullable();

            $table->text('body_col1')->nullable();
            $table->text('body_col2')->nullable();

            $table->string('quote_bg_image')->nullable();
            $table->text('quote_text')->nullable();
            $table->string('quote_author')->nullable();

            $table->json('steps')->nullable();

            $table->string('checklist_image')->nullable();
            $table->string('checklist_button_text')->nullable();
            $table->string('checklist_href')->nullable();

            $table->string('brands_title_line1')->nullable();
            $table->string('brands_title_line2')->nullable();
            $table->json('brand_logos')->nullable();

            $table->string('seo_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cxbyex_page_contents');
    }
};
