<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'hero_title',
    'hero_body_text',
    'hero_bg_image',
    'hero_floating_images',
    'services_intro_text',
    'services',
    'team_avatars',
    'cases',
    'testimonial_quote',
    'testimonial_author',
    'testimonial_author_avatar',
    'testimonial_logo',
    'client_logos',
    'team_description',
    'team_button_text',
    'team_button_href',
    'team_photos',
])]
class HomepageContent extends Model
{
    protected function casts(): array
    {
        return [
            'hero_floating_images' => 'array',
            'services' => 'array',
            'team_avatars' => 'array',
            'cases' => 'array',
            'client_logos' => 'array',
            'team_photos' => 'array',
        ];
    }

    /**
     * Haal de enige homepage-content-rij op, of maak een nieuwe aan.
     */
    public static function getSingleton(): self
    {
        return self::firstOrCreate([], [
            'hero_title' => 'Credible Creativity',
            'hero_body_text' => "People love brands that live up to what they say and do. Now more than ever. We help your brand uncover its true essence and turn it into a credible, compelling story. One that creates consistently engaging experiences, wows minds, and wins hearts. From A to Z. From customers to employees. That is how you deliver on your brand promise. It's what we love to do, and what we call Credible Creativity.",
            'services_intro_text' => 'Credible Creativity delivers on your brand promise through three connected services that engage everyone from customer to employee.',
            'services' => [
                [
                    'name' => 'Brand',
                    'description' => 'Tell your credible brand story, stand out in your market, and connect with your customers.',
                    'tags' => ['Positioning', 'Narrative', 'Campaigns', 'Visual identity', 'Activations', 'Social'],
                    'mediaUrl' => null,
                    'mediaType' => 'image',
                ],
                [
                    'name' => 'Experience',
                    'description' => 'Create memorable, connected brand experiences that drive engagement and loyalty from A to Z, customer to employee.',
                    'tags' => ['Customer Journey', 'Digital', 'Events', 'Environments'],
                    'mediaUrl' => null,
                    'mediaType' => 'image',
                ],
                [
                    'name' => 'Employee',
                    'description' => 'Activate your people as brand ambassadors and create a culture that lives your brand from the inside out.',
                    'tags' => ['Internal Comms', 'Culture', 'Engagement', 'Training'],
                    'mediaUrl' => null,
                    'mediaType' => 'image',
                ],
            ],
            'team_button_text' => 'About us',
            'team_button_href' => '#about',
        ]);
    }
}
