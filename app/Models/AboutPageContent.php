<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'hero_title',
    'hero_description',
    'hero_bg_image',
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
    'dimensions_title',
    'dimensions_body',
    'dimensions_formula_title',
    'dimensions_formula_subtitle',
    'dimensions_center_title',
    'dimensions_center_subtitle',
    'dimensions_items',
    'services',
    'ancient_title_line1',
    'ancient_title_line2',
    'ancient_body',
    'ancient_image',
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
    use LogsActivity;

    protected function casts(): array
    {
        return [
            'credible_cases' => 'array',
            'growth_tags' => 'array',
            'dimensions_items' => 'array',
            'narrative_tags' => 'array',
            'services' => 'array',
            'team_photos' => 'array',
        ];
    }

    public static function getSingleton(): self
    {
        return self::firstOrCreate([], [
            'hero_title' => 'We are Lemon',
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
            'growth_body' => "The world's most successful brands don't choose between storytelling and experience. They master both. They capture attention through creativity, build belief through experience, and create products, services and communities people genuinely want to be part of. When every interaction reinforces the same core narrative, brands become credible, distinctive and more likely to grow.\n\nWe call this **Total Brand Growth.**",
            'dimensions_title' => 'The 4 dimensions of Total Brand Growth',
            'dimensions_body' => 'We measure Total Brand Growth across four brand dimensions: Fame, Experience, Proposition and Participation.',
            'dimensions_formula_title' => 'Lemon’s Credible Creativity Formula',
            'dimensions_formula_subtitle' => 'How brand credibility drives growth',
            'dimensions_center_title' => 'Brand narrative',
            'dimensions_center_subtitle' => 'Promise & Values',
            'dimensions_items' => [
                ['name' => 'Brand', 'subtitle' => 'Advertising, PR & social'],
                ['name' => 'Employee experience', 'subtitle' => 'Internal teams'],
                ['name' => 'Proposition', 'subtitle' => 'Core product & NPD'],
                ['name' => 'Customer experience', 'subtitle' => 'Key touchpoints & customer care'],
            ],
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
            'approach_title' => 'Our Brand Narrative Approach™',
            'approach_body' => "All great stories share the same structure. From the first campfire tale to the timeless Star Wars saga, every great story has an adversary. No adversary, no story.\n\nAdversarial thinking keeps people hooked. It’s human nature to pick a side and root for what matters. When your brand becomes part of that story, they can’t look away.",
            'approach_button_text' => 'Read more',
            'approach_button_href' => '/cxbyex',
            'narrative_title' => 'Your Credible Brand narrative',
            'narrative_body' => 'Your Credible Brand Narrative is our mutual creative playground to shape and grow your brand. From advertising to brand experience and employee engagement, everything connects. Creative, coherent, and credible.',
            'narrative_button_text' => 'Read even more',
            'narrative_button_href' => '/cases',
            'international_title_line1' => 'International',
            'international_title_line2' => 'community',
            'international_body' => "Lemon Scented Tea is served hot in every time zone. You can find the Lemons in Amsterdam, Atlanta, London, and Munich. And just as the power of storytelling is universal, so are we.\n\nWe operate as one strong core surrounded by an eclectic community of top notch, like-minded professionals from around the world. All united by a single purpose: to create Credible Creativity.",
            'team_photos' => [],
            'join_team_text' => 'Join our team',
            'join_team_href' => '/jobs',
        ]);
    }
}
