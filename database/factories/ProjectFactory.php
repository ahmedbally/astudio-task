<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'status' => $this->faker->randomElement(ProjectStatus::cases()),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(function () {
            return [
                'status' => ProjectStatus::PENDING
            ];
        });
    }

    public function active(): static
    {
        return $this->state(function () {
            return [
                'status' => ProjectStatus::ACTIVE
            ];
        });
    }

    public function inactive(): static
    {
        return $this->state(function () {
            return [
                'status' => ProjectStatus::INACTIVE
            ];
        });
    }
}
