<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'test',
            'description' => 'test',
            'status' => 'in_progress',
            'due_date' => now()->addDays(rand(10, 30)),
            'created_by_user_id' => User::factory(),
            'team_id' => Team::factory(),
        ];
    }
}
