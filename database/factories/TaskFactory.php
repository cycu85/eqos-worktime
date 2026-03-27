<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-2 months', 'now')->format('Y-m-d');
        $endDate = $this->faker->dateTimeBetween($startDate, '+1 month')->format('Y-m-d');

        return [
            'title'       => $this->faker->sentence(4),
            'description' => $this->faker->optional()->paragraph(),
            'start_date'  => $startDate,
            'end_date'    => $endDate,
            'leader_id'   => User::factory(),
            'team_id'     => null,
            'team'        => null,
            'notes'       => null,
            'lokalizacja' => null,
            'nr_lini'     => null,
            'status'      => 'planned',
        ];
    }
}
