<?php

namespace Database\Factories;

// database/factories/TomeFactory.php

use App\Models\Character;
use App\Models\Language;
use App\Models\Location;
use App\Models\Tome;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\DangerLevel;

class TomeFactory extends Factory
{
    protected $model = Tome::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'alternate_titles' => [$this->faker->sentence(2)],
            'origin' => $this->faker->word(),
            'author_id' => Character::factory(),
            'language_id' => Language::factory(),
            'contents_summary' => $this->faker->paragraph(),
            'cursed' => $this->faker->boolean(),
            'sentient' => $this->faker->boolean(),
            'current_owner_id' => Character::factory(),
            'last_known_location_id' => Location::factory(),
            'danger_level' => DangerLevel::Medium,
            'artifact_type' => 'Grimoire',
            'cover_material' => 'Leather',
            'pages' => $this->faker->numberBetween(100, 500),
            'illustrated' => $this->faker->boolean(),
            'notable_quotes' => [$this->faker->sentence()],
        ];
    }
}
