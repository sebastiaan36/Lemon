<?php

namespace Database\Seeders;

use App\Enums\CaseStatus;
use App\Models\Blog;
use App\Models\Media;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Voorbeeldblogs om het blogoverzicht te kunnen beoordelen.
 * Niet gekoppeld aan DatabaseSeeder; draai expliciet met
 * `php artisan db:seed --class=DemoBlogSeeder`.
 */
class DemoBlogSeeder extends Seeder
{
    public function run(): void
    {
        $blogs = [
            ['Your brand doesn’t need a better story. It needs a truer one.', 'Credible creativity', '2026-10-01', '019e0db6-995b-73f2-8e28-86f5415f54fa'],
            ['Great brands don’t invent. They uncover.', 'Branding', '2026-09-18', '019e459d-4653-717b-9aac-5d2a4521fe74'],
            ['When your brand story becomes more than a story', 'Brand experience', '2026-09-02', '019e0e68-e3f5-7047-a9ed-9d3e7e24dad1'],
            ['Why your employees are your most credible campaign', 'Employee experience', '2026-08-14', '019e470b-1bf1-73be-b1a7-6ee53b6abd98'],
            ['Five questions to ask before you rebrand', 'Branding', '2026-07-21', '019db0a4-4558-733a-b682-2fba43bbad23'],
            ['From A to Z: designing one connected customer journey', 'Brand experience', '2026-06-30', '019e8c81-d01a-7052-a59c-cf4a332f4ec2'],
        ];

        $authorPhoto = $this->authorPhoto();

        foreach ($blogs as [$title, $category, $date, $photo]) {
            Blog::updateOrCreate(['slug' => Str::slug($title)], [
                'title' => $title,
                'category' => $category,
                'tags' => ['Aviation'],
                'author_name' => 'Kirsten Markusse',
                'author_photo' => $authorPhoto,
                'intro' => 'People have become pretty good at spotting the difference between a brand that says the right things and one that actually means them. And no amount of polished copy, perfect design or clever advertising can hide that gap forever.',
                'header_photo' => $photo,
                'content' => $this->content(),
                'status' => CaseStatus::Published,
                'published_at' => $date.' 09:00:00',
            ]);
        }
    }

    /**
     * Zet de auteursfoto uit het Figma-ontwerp in de mediabibliotheek.
     */
    private function authorPhoto(): string
    {
        $path = 'demo/kirsten-markusse.png';

        Storage::disk('public')->put($path, file_get_contents(database_path('seeders/assets/kirsten-avatar.png')));

        $media = Media::query()->firstOrCreate(['path' => $path], [
            'disk' => 'public',
            'directory' => 'demo',
            'visibility' => 'public',
            'name' => 'kirsten-markusse',
            'title' => 'Kirsten Markusse',
            'width' => 78,
            'height' => 82,
            'size' => Storage::disk('public')->size($path),
            'type' => 'image/png',
            'ext' => 'png',
        ]);

        return (string) $media->getKey();
    }

    /**
     * Tekst uit het Figma-ontwerp van de losse blogpagina.
     */
    private function content(): string
    {
        $image = Storage::disk('public')->url('612e7726-9de0-43bd-b8ae-5f3c66d5ba1e-430350.png');

        return <<<HTML
            <p>For years, brands have been told to tell better stories. Be more emotional. More distinctive. More relevant. More human. And preferably squeeze all of that into six seconds. So we sharpen propositions, rewrite purpose statements and search for that one perfect line that will suddenly make people care. But maybe the problem isn’t the storytelling. Maybe it’s the story.</p>
            <blockquote><p>“We make your brand grow from the inside out. From strategy, to ideation to production to roll-out. We turn brand promises into everyday behaviors, so customers don’t just see your brand, they feel it.”</p></blockquote>
            <h2>Start with whats true</h2>
            <p>The strongest brands don’t invent a personality because research says their audience will like it. They uncover what is already there: what they believe, what makes them different and why any of that should matter to people.</p>
            <p>That sounds simple. It rarely is. Because finding the truth means looking beyond the words on the About page. It means understanding the choices a company makes, the culture behind those choices and the tension it wants to take a stand against.</p>
            <p>Once you find that core, creativity has something real to build on. Instead of searching for something interesting to say, you have something interesting to express.</p>
            <p><img src="{$image}" alt=""></p>
            <h2>Make the inside and outside agree</h2>
            <p>A brand story becomes powerful when it doesn’t stop at communication. What customers see on the outside should connect to what employees experience on the inside. Advertising, design, customer experience and culture shouldn’t feel like separate worlds.</p>
            <p>Because every interaction tells part of the same story. The campaign someone sees on Monday. The service they receive on Wednesday. The conversation an employee has with a customer on Friday. When those moments reinforce each other, a brand starts to feel consistent without becoming predictable.</p>
            <p>That’s when a brand moves from simply saying something to actually standing for something.</p>
            <h2>Creativity needs credibility</h2>
            <p>At Lemon Scented Tea, we call this Credible Creativity. Creativity that doesn’t start with making noise, but with finding the narrative a brand can genuinely own — and then expressing it in ways people can see, feel and experience.</p>
            <p>Credibility without creativity can be true but forgettable. Creativity without credibility can be exciting but empty. Bring the two together and you get ideas that attract attention and have a reason to exist.</p>
            <p>It gives campaigns more substance, design more meaning and experiences a clearer role in the bigger picture.</p>
            HTML;
    }
}
