<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'name' => 'The ' . $this->faker->word() . ' Archives',
            'realm' => $this->faker->optional()->word(),
            'description' => $this->faker->optional()->paragraph(),
        ];
    }
}
