<?php

namespace Database\Factories;

use App\Models\Spell;
use App\Models\Tome;
use App\Enums\DangerLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpellFactory extends Factory
{
    protected $model = Spell::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word() . ' Spell',
            'effect' => $this->faker->sentence(),
            'danger_level' => DangerLevel::Medium,
            'tome_id' => Tome::factory(),
        ];
    }
}
