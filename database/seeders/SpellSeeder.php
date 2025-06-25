<?php

namespace Database\Seeders;

use App\Enums\DangerLevel;
use App\Models\Spell;
use App\Models\Tome;
use Illuminate\Database\Seeder;

class SpellSeeder extends Seeder
{
    public function run(): void
    {
        $tome = Tome::where('title', 'The Book of Spells')->first();

        Spell::factory()->create([
            'name' => 'Fireball',
            'effect' => 'Throws a ball of fire that explodes on impact.',
            'danger_level' => DangerLevel::Severe,  // <-- Use enum case here
            'tome_id' => $tome->id,
        ]);

        Spell::factory()->create([
            'name' => 'Invisibility',
            'effect' => 'Makes the caster invisible for 5 minutes.',
            'danger_level' => DangerLevel::Medium,  // <-- Use enum case here
            'tome_id' => $tome->id,
        ]);
    }
}
