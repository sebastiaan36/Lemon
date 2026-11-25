<?php

namespace Database\Factories;

use App\Enums\PillPage;
use App\Models\FloatingPill;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FloatingPill>
 */
class FloatingPillFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label' => 'Tell us about your project',
            'href' => '/contact',
            'page' => PillPage::About,
            'start_section' => 'credible',
            'end_section' => 'team-photos',
            'avatars' => null,
            'use_team_avatars' => false,
            'desktop_bottom' => 24,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
