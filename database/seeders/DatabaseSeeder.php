<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CharacterSeeder::class,
            LanguageSeeder::class,
            LocationSeeder::class,
            TomeSeeder::class,
            SpellSeeder::class,
        ]);
    }

}
