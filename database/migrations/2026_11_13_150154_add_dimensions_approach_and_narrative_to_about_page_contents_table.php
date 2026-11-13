<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD_GROWTH_BODY = 'The entire customer journey, employer branding, and 360° advertising brought together in one cohesive strategy. That\'s how real brand growth happens. Consistent, credible and built to last. We make it our daily mission to guide your brand towards that growth. Totally.';

    /**
     * Nieuwe secties uit het About-ontwerp van november 2026: "The 4 dimensions of
     * Total Brand Growth", "Our Brand Narrative Approach" en "Your Credible Brand
     * narrative". Bestaande content krijgt de teksten uit het ontwerp.
     */
    public function up(): void
    {
        Schema::table('about_page_contents', function (Blueprint $table) {
            $table->string('dimensions_title')->nullable()->after('growth_tags');
            $table->text('dimensions_body')->nullable()->after('dimensions_title');
            $table->string('dimensions_center_title')->nullable()->after('dimensions_body');
            $table->string('dimensions_center_subtitle')->nullable()->after('dimensions_center_title');
            $table->json('dimensions_items')->nullable()->after('dimensions_center_subtitle');

            $table->string('approach_title')->nullable()->after('ancient_image');
            $table->text('approach_body')->nullable()->after('approach_title');
            $table->string('approach_button_text')->nullable()->after('approach_body');
            $table->string('approach_button_href')->nullable()->after('approach_button_text');

            $table->string('narrative_title')->nullable()->after('approach_button_href');
            $table->text('narrative_body')->nullable()->after('narrative_title');
            $table->string('narrative_button_text')->nullable()->after('narrative_body');
            $table->string('narrative_button_href')->nullable()->after('narrative_button_text');
            $table->string('narrative_image')->nullable()->after('narrative_button_href');
            $table->string('narrative_client_name')->nullable()->after('narrative_image');
            $table->json('narrative_tags')->nullable()->after('narrative_client_name');
        });

        DB::table('about_page_contents')->update([
            'dimensions_title' => 'The 4 dimensions of Total Brand Growth',
            'dimensions_body' => 'We measure Total Brand Growth across four brand dimensions: Fame, Experience, Proposition and Participation.',
            'dimensions_center_title' => 'Brand narrative',
            'dimensions_center_subtitle' => 'Promise & Values',
            'dimensions_items' => json_encode([
                ['name' => 'Brand', 'subtitle' => 'Advertising, design, PR & social'],
                ['name' => 'Employee experience', 'subtitle' => null],
                ['name' => 'Proposition', 'subtitle' => 'Product & Services'],
                ['name' => 'Customer experience', 'subtitle' => null],
            ]),
            'approach_title' => 'Our Brand Narrative Approach™',
            'approach_body' => "All great stories share the same structure. From the first campfire tale to the timeless Star Wars saga, every great story has an adversary. No adversary, no story.\n\nAdversarial thinking keeps people hooked. It’s human nature to pick a side and root for what matters. When your brand becomes part of that story, they can’t look away.",
            'approach_button_text' => 'Read more',
            'approach_button_href' => '/cxbyex',
            'narrative_title' => 'Your Credible Brand narrative',
            'narrative_body' => 'Your Credible Brand Narrative is our mutual creative playground to shape and grow your brand. From advertising to brand experience and employee engagement, everything connects. Creative, coherent, and credible.',
            'narrative_button_text' => 'Read even more',
            'narrative_button_href' => '/cases',
        ]);

        DB::table('about_page_contents')
            ->where('growth_body', self::OLD_GROWTH_BODY)
            ->update([
                'growth_body' => "The world's most successful brands don't choose between storytelling and experience. They master both. They capture attention through creativity, build belief through experience, and create products, services and communities people genuinely want to be part of. When every interaction reinforces the same core narrative, brands become credible, distinctive and more likely to grow.\n\nWe call this **Total Brand Growth.**",
            ]);
    }

    public function down(): void
    {
        DB::table('about_page_contents')
            ->where('growth_body', 'like', '%We call this **Total Brand Growth.**')
            ->update(['growth_body' => self::OLD_GROWTH_BODY]);

        Schema::table('about_page_contents', function (Blueprint $table) {
            $table->dropColumn([
                'dimensions_title',
                'dimensions_body',
                'dimensions_center_title',
                'dimensions_center_subtitle',
                'dimensions_items',
                'approach_title',
                'approach_body',
                'approach_button_text',
                'approach_button_href',
                'narrative_title',
                'narrative_body',
                'narrative_button_text',
                'narrative_button_href',
                'narrative_image',
                'narrative_client_name',
                'narrative_tags',
            ]);
        });
    }
};
