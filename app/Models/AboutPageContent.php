<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'hero_title',
    'hero_description',
    'intro_quote_line1',
    'intro_quote_line2',
    'intro_bg_image',
    'cta_text',
    'cta_href',
    'credible_title_line1',
    'credible_title_line2',
    'credible_body',
    'credible_cases',
    'growth_title_line1',
    'growth_title_line2',
    'growth_body',
    'growth_image',
    'growth_client_name',
    'growth_tags',
    'services',
    'ancient_title_line1',
    'ancient_title_line2',
    'ancient_body',
    'ancient_image',
    'international_title_line1',
    'international_title_line2',
    'international_body',
    'team_photos',
    'join_team_text',
    'join_team_href',
    'seo_title',
    'meta_description',
])]
class AboutPageContent extends Model
{
    protected function casts(): array
    {
        return [
            'credible_cases' => 'array',
            'growth_tags' => 'array',
            'services' => 'array',
            'team_photos' => 'array',
        ];
    }

    public static function getSingleton(): self
    {
        return self::firstOrCreate([], [
            'hero_title' => 'This is Lemon',
            'hero_description' => 'We compare what we do to the ritual of making tea. The boiling water represents society, while the tea bag holds the essence of your brand. We turn that essence into your credible brand narrative, strong enough to infuse people\'s lives and create a lasting, refreshing blend that helps your brand grow.',
            'intro_quote_line1' => 'And Lemon?',
            'intro_quote_line2' => 'We keep it fresh.',
            'cta_text' => 'Tell us about your project',
            'cta_href' => '/contact',
            'credible_title_line1' => 'Credible',
            'credible_title_line2' => 'Creativity',
            'credible_body' => 'In a world overloaded with brand noise, algorithms, and fake news, people are turning away from brands that fail to live up to what they say and do. Only those that wow minds and win hearts by consistently proving their core essence will stay relevant and future-proof.',
            'credible_cases' => [],
            'growth_title_line1' => 'Total brand',
            'growth_title_line2' => 'growth',
            'growth_body' => 'The entire customer journey, employer branding, and 360° advertising brought together in one cohesive strategy. That\'s how real brand growth happens. Consistent, credible and built to last. We make it our daily mission to guide your brand towards that growth. Totally.',
            'services' => [
                [
                    'name' => 'Brand',
                    'description' => 'Tell your credible brand story, stand out in your market, and connect with your customers.',
                    'tags' => ['Positioning', 'Narrative', 'Campaigns', 'Visual identity', 'Activations'],
                ],
                [
                    'name' => 'Experience',
                    'description' => 'Create memorable, connected brand experiences that drive engagement and loyalty from A to Z, customer to employee.',
                    'tags' => ['Customer Journey', 'Digital', 'Events', 'Environments'],
                ],
                [
                    'name' => 'Employee',
                    'description' => 'Activate your people as brand ambassadors and create a culture that lives your brand from the inside out.',
                    'tags' => ['Internal Comms', 'Culture', 'Engagement', 'Training'],
                ],
            ],
            'ancient_title_line1' => "We're keeping",
            'ancient_title_line2' => 'ancient actual',
            'ancient_body' => 'We love a great story. It\'s been the recipe for human engagement since the beginning of time. Stories engage, simplify and spread. We use that power for your brand.',
            'international_title_line1' => 'International',
            'international_title_line2' => 'community',
            'international_body' => "Lemon Scented Tea is served hot in every time zone. You can find the Lemons in Amsterdam, Atlanta, London, and Munich. And just as the power of storytelling is universal, so are we.\n\nWe operate as one strong core surrounded by an eclectic community of top notch, like-minded professionals from around the world. All united by a single purpose: to create Credible Creativity.",
            'team_photos' => [],
            'join_team_text' => 'Join our team',
            'join_team_href' => '/careers',
        ]);
    }
}
