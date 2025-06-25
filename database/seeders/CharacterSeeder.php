<?php

namespace Database\Seeders;

use App\Models\Character;
use Illuminate\Database\Seeder;

class CharacterSeeder extends Seeder
{
    public function run(): void
    {
        Character::factory()->create([
            'name' => 'Merlin',
            'bio' => 'Legendary wizard from Arthurian lore.',
        ]);

        Character::factory()->create([
            'name' => 'Victor Frankenstein',
            'bio' => 'Famous scientist who created the monster.',
        ]);
    }
}
