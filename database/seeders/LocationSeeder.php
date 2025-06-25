<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        Location::factory()->create(['name' => 'Camelot', 'realm' => 'Arthurian Legends', 'description' => 'The castle of King Arthur.']);
        Location::factory()->create(['name' => 'Frankenstein\'s Laboratory', 'realm' => 'Gothic Horror', 'description' => 'Where the monster was created.']);
    }
}
