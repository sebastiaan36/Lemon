<?php

namespace Database\Factories;

use App\Enums\CaseStatus;
use App\Models\Industry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Industry>
 */
class IndustryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'status' => CaseStatus::Published,
            'sort_order' => 0,
            'headline' => fake()->sentence(),
            'why_title' => 'Why this industry',
            'why_lead' => fake()->sentence(),
            'why_body' => fake()->paragraph(),
            'cases' => [],
            'approach_title' => 'Our approach',
            'approach_items' => [
                ['title' => 'Build the brand', 'subtitle' => fake()->sentence(), 'body' => fake()->sentence(), 'bullets' => ['One', 'Two']],
            ],
            'proof_logos' => [],
        ];
    }

    public function concept(): static
    {
        return $this->state(['status' => CaseStatus::Concept]);
    }
}
