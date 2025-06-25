<?php

namespace Database\Factories;

use App\Models\Character;
use Illuminate\Database\Eloquent\Factories\Factory;

class CharacterFactory extends Factory
{
    protected $model = Character::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'bio' => $this->faker->paragraph(),
        ];
    }
}
