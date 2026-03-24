<?php

namespace Database\Factories;

use App\Models\CaseStudy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CaseStudy>
 */
class CaseStudyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company();

        return [
            'name' => $name,
            'slug' => fake()->slug(),
            'client_name' => $name,
            'accent_color' => '#0A7949',
            'hero_title' => $name,
            'hero_subtitle' => $name,
            'touchpoints' => ['Positioning', 'Narrative'],
            'gallery_items' => [],
            'story_images' => [],
            'results_heading' => 'Results',
            'results_stats' => [],
            'video' => null,
            'photo' => null,
            'is_featured' => false,
            'sort_order' => 0,
        ];
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }
}
