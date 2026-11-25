<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Copy-doc P07 (Financial services): "What we believe", het gele blok en de
     * cases. Our approach en klantlogo's ontbreken nog in de copy. De proof-titel
     * blijft "Proof that counts" (klantfeedback 25-11). Overschrijft niets wat al
     * in het CMS is ingevuld.
     */
    public function up(): void
    {
        DB::table('industries')
            ->where('slug', 'financial-services')
            ->where(fn ($query) => $query->whereNull('belief_body')->orWhere('belief_body', ''))
            ->update([
                'belief_title' => 'What we believe',
                'belief_body' => 'Technology may streamline finance. But it is the people who make a true connection and create trust. In the way a team member listens to a customer. In the confidence you give your customer to make a smart decision.',
                'belief_highlight' => 'That’s where your brand lives.',
                'updated_at' => now(),
            ]);

        DB::table('industries')
            ->where('slug', 'financial-services')
            ->where(fn ($query) => $query->whereNull('cta_text')->orWhere('cta_text', ''))
            ->update([
                'cta_text' => 'We believe the strongest financial brands grow from the inside out. We turn stories into behavior, so customers feel heard, guided, and respected, and your teams know exactly how to deliver on your promise.',
                'cta_primary_text' => 'Get in touch',
                'cta_primary_href' => '/contact',
                'cta_secondary_text' => 'View insights',
                'cta_secondary_href' => '/blog',
            ]);

        $caseIds = DB::table('case_studies')
            ->where(function ($query) {
                foreach (['Hamburg Commercial', 'HCOB', 'ING', 'NN', 'PNOZ'] as $name) {
                    $query->orWhere('name', 'like', $name.'%');
                }
            })
            ->where('name', 'not like', '%(kopie%')
            ->orderBy('sort_order')
            ->pluck('id')
            ->map(fn (mixed $id): string => (string) $id)
            ->all();

        DB::table('industries')
            ->where('slug', 'financial-services')
            ->where(fn ($query) => $query->whereNull('cases')->orWhere('cases', '[]'))
            ->update(['cases' => json_encode($caseIds)]);
    }

    public function down(): void
    {
        //
    }
};
