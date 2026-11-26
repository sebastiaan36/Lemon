<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Proof-deel per branche (feedback 26-11): Impact (case + cijfers),
     * Insights (blogs) en klantlogo's. Vult alleen wat nog leeg is.
     */
    public function up(): void
    {
        Schema::table('industries', function (Blueprint $table): void {
            $table->string('impact_title')->nullable()->after('proof_logos');
            $table->text('impact_body')->nullable()->after('impact_title');
            $table->json('impact_stats')->nullable()->after('impact_body');
            $table->string('insights_title')->nullable()->after('impact_stats');
            $table->json('insights_blogs')->nullable()->after('insights_title');
        });

        DB::table('industries')->update(['insights_title' => 'Insights']);

        DB::table('industries')
            ->where('slug', 'financial-services')
            ->whereNull('impact_title')
            ->update([
                'impact_title' => 'HCOB | Trust Through Every Touchpoint',
                'impact_body' => "We helped Hamburg Commercial Bank become the most profitable bank in its industry, without pretending to be something it's not. In a market clouded by heritage storytelling and vague sustainability jargon, we helped HCOB build a brand rooted in radical clarity.\nFrom the very first recruitment campaign, we positioned clarity as capital. We didn't sell dreams, we showed the real financial world as it is. Direct. Bold. Profitable.",
                'impact_stats' => json_encode([
                    ['value' => '+23%', 'label' => 'Brand awareness'],
                    ['value' => '+400%', 'label' => 'Web traffic'],
                    ['value' => '+300%', 'label' => 'Job applications'],
                    ['value' => '+100%', 'label' => 'Fully engaged organisation'],
                ]),
            ]);

        $this->fillLifestyleLogos();
    }

    public function down(): void
    {
        Schema::table('industries', function (Blueprint $table): void {
            $table->dropColumn(['impact_title', 'impact_body', 'impact_stats', 'insights_title', 'insights_blogs']);
        });
    }

    /**
     * Lifestyle-klanten waarvan al een wit logo in de mediabibliotheek staat.
     */
    private function fillLifestyleLogos(): void
    {
        $lifestyle = DB::table('industries')->where('slug', 'lifestyle')->first();

        if (! $lifestyle || ! empty(json_decode($lifestyle->proof_logos ?? '[]', true))) {
            return;
        }

        $logos = collect(['Cortina' => 'proof-cortina%', 'Loods 5' => 'proof-loods5%'])
            ->map(fn (string $pattern, string $name): ?array => ($id = DB::table('curator')->where('name', 'like', $pattern)->value('id'))
                ? ['name' => $name, 'logo' => (string) $id]
                : null)
            ->filter()
            ->values()
            ->all();

        if ($logos !== []) {
            DB::table('industries')->where('id', $lifestyle->id)->update(['proof_logos' => json_encode($logos)]);
        }
    }
};
