<?php

use App\Models\Media;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Industries-pagina (Figma "LST - INDUSTRIES (D)"): één record per branche.
     * Aviation en Financial services krijgen de teksten uit het ontwerp.
     */
    public function up(): void
    {
        Schema::create('industries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('status')->default('concept');
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('headline')->nullable();
            $table->string('why_title')->nullable();
            $table->text('why_lead')->nullable();
            $table->text('why_body')->nullable();
            $table->json('cases')->nullable();
            $table->string('approach_title')->nullable();
            $table->json('approach_items')->nullable();
            $table->string('belief_title')->nullable();
            $table->text('belief_body')->nullable();
            $table->string('belief_highlight')->nullable();
            $table->text('cta_text')->nullable();
            $table->string('cta_primary_text')->nullable();
            $table->string('cta_primary_href')->nullable();
            $table->string('cta_secondary_text')->nullable();
            $table->string('cta_secondary_href')->nullable();
            $table->string('proof_title')->nullable();
            $table->json('proof_logos')->nullable();
            $table->timestamps();
        });

        $now = now();
        $deltaCaseId = DB::table('case_studies')->where('slug', 'delta-air-lines')->value('id');

        DB::table('industries')->insert([
            [
                'name' => 'Aviation',
                'slug' => 'aviation',
                'status' => 'published',
                'sort_order' => 1,
                'headline' => 'Elevating the aviation industry through insights, strategy and creative ideation.',
                'why_title' => 'Why aviation by Lemon Scented Tea',
                'why_lead' => 'We understand what’s at stake at 30.000 feet.',
                'why_body' => 'In an industry defined by complexity, protocol, and performance pressure, the ultimate differentiator is the overall experience. From booking to check-in to cabin, from crew to customer, we create journeys that feel personal, premium, and unmistakably yours. Trusted by global leaders in aviation and hospitality, we combine deep Brand, CX and EX expertise with a narrative that sticks.',
                'cases' => json_encode($deltaCaseId ? [(string) $deltaCaseId] : []),
                'approach_title' => 'Our approach',
                'approach_items' => json_encode([
                    [
                        'title' => 'Build the brand',
                        'subtitle' => 'Sharpen your positioning in a crowded market.',
                        'body' => 'We craft brand narratives and visual identities that inspire trust, energize employees, and emotionally connect with passengers.',
                        'bullets' => ['Rebranding & brand positioning', 'Brand Narrative translated into appealing stories and communication', 'Visual identity & design', 'Campaigns across multiple channels'],
                    ],
                    [
                        'title' => 'Design the experience',
                        'subtitle' => 'Make every interaction an opportunity to build loyalty.',
                        'body' => 'From seamless digital flows to unforgettable in-flight gestures, we design signature experiences that keep passengers coming back.',
                        'bullets' => ['End-to-end journey mapping', 'Service design', 'Passenger experience activations', 'Brand spaces'],
                    ],
                    [
                        'title' => 'Activate your employees',
                        'subtitle' => 'Make every interaction an opportunity to build loyalty.',
                        'body' => 'Your people bring your brand to life. We inspire and align teams through internal campaigns, behavioral activation, and crew programs that drive service excellence, consistency, employee engagement and pride.',
                        'bullets' => ['Internal brand activation', 'Brand-to-behavior programs', 'Training & onboarding experience design', 'Build a true service mindset and culture of hospitality'],
                    ],
                ]),
                'belief_title' => 'What we believe',
                'belief_body' => 'Strategy enables. Creativity connects. And it’s your people who make the difference.',
                'belief_highlight' => 'That’s where your brand lives.',
                'cta_text' => 'We make your brand grow from the inside out. From strategy, to ideation to production to roll-out. We turn brand promises into everyday behaviors, so customers don’t just see your brand, they feel it. And employees don’t just understand the brand, they embody it.',
                'cta_primary_text' => 'Get in touch',
                'cta_primary_href' => '/contact',
                'cta_secondary_text' => 'View insights',
                'cta_secondary_href' => '/blog',
                'proof_title' => 'Proof that flies',
                'proof_logos' => json_encode($this->importProofLogos()),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Financial services',
                'slug' => 'financial-services',
                'status' => 'published',
                'sort_order' => 2,
                'headline' => 'Elevating financial services through trust, creativity, and human connection.',
                'why_title' => 'Why Financial services by Lemon Scented Tea',
                'why_lead' => 'We understand what’s at stake when every decision impacts financial futures.',
                'why_body' => 'In a highly regulated, increasingly digital world, trust is your greatest asset, and your people are key to earning it. Lemon Scented Tea helps financial brands grow by building authentic connections across every interaction. From first impression to daily use of functionalities, we bring your brand to life in ways that feel clear, consistent, and human. Trusted by banks, insurers, and investment firms alike, we blend Brand, CX, EX, and storytelling to drive engagement, and loyalty in a world of choice.',
                'cases' => json_encode([]),
                'approach_title' => null,
                'approach_items' => json_encode([]),
                'belief_title' => null,
                'belief_body' => null,
                'belief_highlight' => null,
                'cta_text' => null,
                'cta_primary_text' => null,
                'cta_primary_href' => null,
                'cta_secondary_text' => null,
                'cta_secondary_href' => null,
                'proof_title' => null,
                'proof_logos' => json_encode([]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            ...collect(['Hospitality', 'Lifestyle'])->map(fn (string $name, int $index): array => [
                'name' => $name,
                'slug' => str($name)->slug()->toString(),
                'status' => 'concept',
                'sort_order' => 3 + $index,
                'headline' => null,
                'why_title' => null,
                'why_lead' => null,
                'why_body' => null,
                'cases' => json_encode([]),
                'approach_title' => null,
                'approach_items' => json_encode([]),
                'belief_title' => null,
                'belief_body' => null,
                'belief_highlight' => null,
                'cta_text' => null,
                'cta_primary_text' => null,
                'cta_primary_href' => null,
                'cta_secondary_text' => null,
                'cta_secondary_href' => null,
                'proof_title' => null,
                'proof_logos' => json_encode([]),
                'created_at' => $now,
                'updated_at' => $now,
            ])->all(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('industries');
    }

    /**
     * Zet de witte klantlogo's uit Figma in de mediabibliotheek.
     *
     * @return list<array{name: string, logo: string}>
     */
    private function importProofLogos(): array
    {
        $logos = [
            'Delta' => ['proof-delta.png', 'image/png', 1200, 185],
            'Cortina' => ['proof-cortina.svg', 'image/svg+xml', 180, 31],
            'Vattenfall' => ['proof-vattenfall.svg', 'image/svg+xml', 150, 23],
            'Loods 5' => ['proof-loods5.svg', 'image/svg+xml', 70, 68],
        ];

        $imported = [];

        foreach ($logos as $name => [$file, $type, $width, $height]) {
            $source = public_path('figma-assets/'.$file);

            if (! is_file($source)) {
                continue;
            }

            $path = 'industries/'.$file;
            Storage::disk('public')->put($path, (string) file_get_contents($source));

            $media = Media::query()->firstOrCreate(['path' => $path], [
                'disk' => 'public',
                'directory' => 'industries',
                'visibility' => 'public',
                'name' => pathinfo($file, PATHINFO_FILENAME),
                'title' => $name.' logo',
                'width' => $width,
                'height' => $height,
                'size' => Storage::disk('public')->size($path),
                'type' => $type,
                'ext' => pathinfo($file, PATHINFO_EXTENSION),
            ]);

            $imported[] = ['name' => $name, 'logo' => (string) $media->getKey()];
        }

        return $imported;
    }
};
