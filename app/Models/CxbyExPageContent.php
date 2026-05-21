<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'hero_bg_image',
    'hero_subtitle',
    'narrative_text',
    'case_bg_image',
    'case_body_text',
    'case_client_name',
    'case_tags',
    'body_col1',
    'body_col2',
    'quote_bg_image',
    'quote_text',
    'quote_author',
    'steps',
    'checklist_image',
    'checklist_button_text',
    'checklist_href',
    'brands_title_line1',
    'brands_title_line2',
    'brand_logos',
    'seo_title',
    'meta_description',
])]
class CxbyExPageContent extends Model
{
    protected function casts(): array
    {
        return [
            'case_tags' => 'array',
            'steps' => 'array',
            'brand_logos' => 'array',
        ];
    }

    public static function getSingleton(): self
    {
        return self::firstOrCreate([], [
            'hero_subtitle' => 'Credible Creativity ensures that what you promise on the outside through campaigns, identities, and customer experiences is truly delivered on the inside by your employees and your product.',
            'narrative_text' => 'Your Brand Narrative can be told with confidence because it reflects your core essence. It helps employees live the brand story not only because they can, but because they want to. In short, Customer Experience by Employee Experience. Or shorter still, CXbyEX. A Lemon specialty.',
            'case_body_text' => 'Boost your brand from the inside out with CXbyEX. Deliver unforgettable customer experiences by building an exceptional employee experience.',
            'case_client_name' => 'Delta Air Lines',
            'case_tags' => ['Positioning', 'Employer branding'],
            'body_col1' => "You can't grow a brand from the outside in. Real, lasting growth happens when your employees live your brand every day, at every touchpoint. When employee experience (EX) fuels customer experience (CX).",
            'body_col2' => "That's when companies outperform in loyalty, retention, and reputation. That's where Lemon CXbyEX comes in. We help service-driven brands in aviation, hospitality, finance, healthcare, and retail turn internal energy into external impact.",
            'quote_text' => 'Our people live the brand, passengers feel it instantly, journeys become memorable and your airline becomes unmistakable',
            'quote_author' => 'Michelle  —  DELTA airlines',
            'steps' => [
                ['number' => '01', 'title' => 'Define your brand narrative',             'body' => 'What makes you different? We help you craft a compelling internal story that inspires and aligns.'],
                ['number' => '02', 'title' => 'Translate values into behaviors',          'body' => 'We turn abstract values into clear, daily actions your teams can understand, embrace, and deliver.'],
                ['number' => '03', 'title' => 'Bring it to life',                         'body' => 'Creative internal campaigns, rituals, and storytelling that activate belief and energy across teams.'],
                ['number' => '04', 'title' => 'Optimize customer & employee journeys',    'body' => 'We identify the high-impact moments where human interaction shapes the brand and make them better.'],
                ['number' => '05', 'title' => 'Build momentum',                           'body' => 'We keep the fire burning with recognition programs, measurement, and ongoing culture nudges.'],
            ],
            'checklist_button_text' => 'Download our free checklist',
            'checklist_href' => '#',
            'brands_title_line1' => 'Leading brands',
            'brands_title_line2' => 'choose Lemon',
            'brand_logos' => [],
        ]);
    }
}
