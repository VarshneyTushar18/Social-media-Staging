<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\AppAgencyOnboarding\Models\AgencyProject;

/**
 * @extends Factory<AgencyProject>
 */
class AgencyProjectFactory extends Factory
{
    protected $model = AgencyProject::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_user_id' => 1,
            'name' => fake()->company(),
            'industry' => 'other',
            'monthly_budget' => 'flexible',
            'brand_tone' => 'professional',
            'enabled_agents' => ['social', 'website', 'seo', 'email'],
            'active_agent' => 'social',
            'setup_completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'setup_completed_at' => now(),
        ]);
    }

    public function incomplete(): static
    {
        return $this->state(fn (): array => [
            'setup_completed_at' => null,
            'enabled_agents' => null,
        ]);
    }
}