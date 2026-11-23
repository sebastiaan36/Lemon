<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Het cirkeldiagram op About wordt "Lemon's Total Brand Growth Formula":
     * een eigen titel + ondertitel, en subtitels bij Customer en Employee experience.
     */
    public function up(): void
    {
        Schema::table('about_page_contents', function (Blueprint $table) {
            $table->string('dimensions_formula_title')->nullable()->after('dimensions_body');
            $table->string('dimensions_formula_subtitle')->nullable()->after('dimensions_formula_title');
        });

        $subtitles = [
            'Customer experience' => 'Key touchpoints + customer care',
            'Employee experience' => 'Internal teams',
        ];

        foreach (DB::table('about_page_contents')->get() as $row) {
            $items = collect(json_decode((string) $row->dimensions_items, true) ?: [])
                ->map(fn (array $item): array => blank($item['subtitle'] ?? null) && isset($subtitles[$item['name'] ?? ''])
                    ? [...$item, 'subtitle' => $subtitles[$item['name']]]
                    : $item)
                ->all();

            DB::table('about_page_contents')->where('id', $row->id)->update([
                'dimensions_formula_title' => 'Lemon’s Total Brand Growth Formula',
                'dimensions_formula_subtitle' => 'How brand credibility drives growth',
                'dimensions_items' => json_encode($items),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('about_page_contents', function (Blueprint $table) {
            $table->dropColumn(['dimensions_formula_title', 'dimensions_formula_subtitle']);
        });
    }
};
