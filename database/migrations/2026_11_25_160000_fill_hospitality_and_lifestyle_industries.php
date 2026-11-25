<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Copy-doc P08 (Hospitality) en P09 (Lifestyle), opgezet als Aviation.
     * Our approach, klantlogo's en impact-cijfers ontbreken nog in de copy;
     * die blokken blijven verborgen tot de klant ze invult. Overschrijft niets
     * wat al in het CMS is ingevuld.
     */
    public function up(): void
    {
        $shared = [
            'belief_title' => 'What we believe',
            'cta_primary_text' => 'Get in touch',
            'cta_primary_href' => '/contact',
            'cta_secondary_text' => 'View insights',
            'cta_secondary_href' => '/blog',
            'proof_title' => 'Proof that delivers',
            'status' => 'published',
            'updated_at' => now(),
        ];

        $this->fill('hospitality', [
            ...$shared,
            'headline' => 'Elevating the guest experience through brand, culture and creativity.',
            'why_title' => 'Why Hospitality by Lemon Scented Tea',
            'why_lead' => 'We understand what’s at stake when every moment matters.',
            'why_body' => 'In a world of digital ease and curated stays, the true differentiator remains human connection. Lemon Scented Tea helps hospitality brands grow by bringing their brand to life through every guest interaction. From campaigns to reception to suite, from breakfast service to brand rituals, we design emotional brands and journeys that feel warm, personal, and unmistakably yours. Trusted by leading hotels, resorts, and hospitality groups, we blend deep brand, CX, and EX expertise to deliver meaningful, memorable experiences.',
            'cases' => json_encode($this->caseIds(['ISS', 'Okura', 'Hotel V', 'Conscious'])),
            'belief_body' => 'Design and tech shape the setting. But people create the feeling. It’s in how someone greets you. Remembers your name. Anticipates your needs.',
            'belief_highlight' => 'That’s where your brand lives.',
            'cta_text' => 'We believe the most powerful hospitality brands grow from the inside out. We translate brand stories into daily behaviors, so guests don’t just see your brand, they feel it. And employees don’t just follow service guidelines, they live the brand.',
        ]);

        $this->fill('lifestyle', [
            ...$shared,
            'headline' => 'Designing lifestyle brands people don’t just buy, but live.',
            'why_title' => 'Why Lifestyle by Lemon Scented Tea',
            'why_lead' => 'In a world overflowing with choice, the strongest lifestyle brands don’t just sell products, they create meaning.',
            'why_body' => "It’s not just about if people wear, use, or see your brand. It’s about how your brand makes them feel. Lemon Scented Tea helps lifestyle brands grow by creating emotional connections between your brand, your customers, and your employees. We turn your brand into an experience that’s felt in every detail, from product to service, from story to team.\n\nTrusted by leading lifestyle brands, we create emotional narratives that we translate into campaigns. We make sure your brand not only lives in the outside world but also is loved, lived and shared on the inside. Fashion, wellness, design, and more.",
            'cases' => json_encode($this->caseIds(['Cortina', 'KNAAP', 'Veloretti', 'GOAT', 'Loods'])),
            'belief_body' => "Lifestyle is a deliberate choice. When people choose your brand, they’re not just wearing or using it, they’re declaring who they are and championing who you are.\n\nIt’s the spark of excitement when your newest drop lands. It’s the quiet pride your team feels, knowing they’re building something bigger than themselves.",
            'belief_highlight' => 'That’s where your brand truly lives.',
            'cta_text' => "Ready to elevate your lifestyle brand? Let’s create brand experiences that make life feel richer, more meaningful, and more connected. Whether you’re launching a new lifestyle brand or transforming an iconic one, we’re here to make every moment matter.\n\nWe believe the strongest lifestyle brands grow from the inside out: unforgettable, heart-touching creativity on the outside, a culture that lives and breathes the brand on the inside. We turn stories into action, so your people live the brand, and your customers welcome it into their own lives.",
        ]);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function fill(string $slug, array $values): void
    {
        DB::table('industries')
            ->where('slug', $slug)
            ->where(fn ($query) => $query->whereNull('headline')->orWhere('headline', ''))
            ->update($values);
    }

    /**
     * Ids (als string, zoals het CMS ze opslaat) van bestaande cases waarvan de naam
     * met een van de opgegeven namen begint.
     *
     * @param  list<string>  $names
     * @return list<string>
     */
    private function caseIds(array $names): array
    {
        return DB::table('case_studies')
            ->where(function ($query) use ($names) {
                foreach ($names as $name) {
                    $query->orWhere('name', 'like', $name.'%');
                }
            })
            ->where('name', 'not like', '%(kopie%')
            ->orderBy('sort_order')
            ->pluck('id')
            ->map(fn (mixed $id): string => (string) $id)
            ->values()
            ->all();
    }

    public function down(): void
    {
        DB::table('industries')
            ->whereIn('slug', ['hospitality', 'lifestyle'])
            ->update(['status' => 'concept']);
    }
};
