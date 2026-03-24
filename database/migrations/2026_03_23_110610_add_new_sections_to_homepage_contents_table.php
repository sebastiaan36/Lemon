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
        Schema::table('homepage_contents', function (Blueprint $table) {
            $table->json('cases')->nullable()->after('team_avatars');
            $table->text('testimonial_quote')->nullable()->after('cases');
            $table->string('testimonial_author')->nullable()->after('testimonial_quote');
            $table->string('testimonial_author_avatar')->nullable()->after('testimonial_author');
            $table->string('testimonial_logo')->nullable()->after('testimonial_author_avatar');
            $table->json('client_logos')->nullable()->after('testimonial_logo');
            $table->text('team_description')->nullable()->after('client_logos');
            $table->json('team_photos')->nullable()->after('team_description');
        });
    }

    public function down(): void
    {
        Schema::table('homepage_contents', function (Blueprint $table) {
            $table->dropColumn([
                'cases',
                'testimonial_quote',
                'testimonial_author',
                'testimonial_author_avatar',
                'testimonial_logo',
                'client_logos',
                'team_description',
                'team_photos',
            ]);
        });
    }
};
