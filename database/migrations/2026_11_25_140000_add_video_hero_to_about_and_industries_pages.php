<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Klantfeedback 25-11: About en Industries openen met een videohero zoals
     * CXbyEX. About: titel "We are Lemon" over de theevideo (die stond in het
     * verwijderde "We keep it fresh"-blok) en het nieuwe Credible Creativity
     * Formula-schema. Industries krijgt voorlopig de video van de CXbyEX-casekaart.
     */
    public function up(): void
    {
        Schema::table('about_page_contents', function (Blueprint $table) {
            $table->string('hero_bg_image')->nullable()->after('hero_description');
        });

        Schema::table('industries_page_contents', function (Blueprint $table) {
            $table->string('hero_bg_image')->nullable()->after('hero_description');
        });

        $formulaSubtitles = [
            'brand' => 'Advertising, PR & social',
            'employee experience' => 'Internal teams',
            'proposition' => 'Core product & NPD',
            'customer experience' => 'Key touchpoints & customer care',
        ];

        foreach (DB::table('about_page_contents')->get() as $row) {
            $items = collect(json_decode((string) $row->dimensions_items, true) ?: [])
                ->map(function (array $item) use ($formulaSubtitles): array {
                    $key = mb_strtolower(trim((string) ($item['name'] ?? '')));

                    return isset($formulaSubtitles[$key])
                        ? [...$item, 'subtitle' => $formulaSubtitles[$key]]
                        : $item;
                })
                ->all();

            DB::table('about_page_contents')->where('id', $row->id)->update([
                'hero_title' => 'We are Lemon',
                'hero_bg_image' => $row->intro_bg_image,
                'dimensions_formula_title' => 'Lemon’s Credible Creativity Formula',
                'dimensions_formula_subtitle' => 'How brand credibility drives growth',
                'dimensions_items' => json_encode($items),
            ]);
        }

        $cxbyexCaseMedia = Schema::hasTable('cxbyex_page_contents')
            ? DB::table('cxbyex_page_contents')->value('case_bg_image')
            : null;

        if ($cxbyexCaseMedia) {
            DB::table('industries_page_contents')
                ->whereNull('hero_bg_image')
                ->update(['hero_bg_image' => $cxbyexCaseMedia]);
        }

        if (Schema::hasTable('floating_pills')) {
            foreach (['start_section', 'end_section'] as $column) {
                DB::table('floating_pills')
                    ->where('page', 'about')
                    ->where($column, 'intro-quote')
                    ->update([$column => 'credible']);
            }
        }
    }

    public function down(): void
    {
        Schema::table('about_page_contents', function (Blueprint $table) {
            $table->dropColumn('hero_bg_image');
        });

        Schema::table('industries_page_contents', function (Blueprint $table) {
            $table->dropColumn('hero_bg_image');
        });
    }
};
