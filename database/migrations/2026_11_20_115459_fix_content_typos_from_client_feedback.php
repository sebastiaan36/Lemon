<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Typfouten uit de klantfeedback van 19-11. Vervangt alleen de exacte fout,
     * dus content die al in het CMS is verbeterd blijft ongemoeid.
     *
     * @var array<string, array<string, string>>
     */
    private const FIXES = [
        'about_page_contents' => [
            'Posistioning' => 'Positioning',
        ],
        'case_studies' => [
            'visit Ouwehand ' => 'visit Ouwehands ',
            'great  responsibility' => 'great responsibility',
        ],
        'job_listings' => [
            'Stagair(e)' => 'Stagiair(e)',
        ],
    ];

    public function up(): void
    {
        foreach (self::FIXES as $table => $replacements) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach (DB::table($table)->get() as $row) {
                $changes = [];

                foreach ((array) $row as $column => $value) {
                    if (! is_string($value) || $column === 'slug') {
                        continue;
                    }

                    $fixed = str_replace(array_keys($replacements), array_values($replacements), $value);

                    if ($fixed !== $value) {
                        $changes[$column] = $fixed;
                    }
                }

                if ($changes !== []) {
                    DB::table($table)->where('id', $row->id)->update($changes);
                }
            }
        }
    }

    public function down(): void
    {
        // Typfouten worden niet teruggezet.
    }
};
