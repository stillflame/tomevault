<?php

namespace Database\Seeders;

namespace Database\Seeders;


use App\Enums\DangerLevel;
use App\Models\Character;
use App\Models\Language;
use App\Models\Location;
use App\Models\Tome;
use Illuminate\Database\Seeder;

class TomeSeeder extends Seeder
{
    public function run(): void
    {
        $author = Character::where('name', 'Merlin')->first();
        $language = Language::where('name', 'Ancient Greek')->first();
        $location = Location::where('name', 'Camelot')->first();

        Tome::factory()->create([
            'title' => 'The Book of Spells',
            'alternate_titles' => ['Grimoire of Magic'],
            'origin' => 'Camelot',
            'author_id' => $author->id,
            'language_id' => $language->id,
            'contents_summary' => 'A powerful tome containing ancient spells.',
            'cursed' => true,
            'sentient' => false,
            'current_owner_id' => $author->id,
            'last_known_location_id' => $location->id,
            'danger_level' => DangerLevel::Severe,  // <-- Use enum case here
            'artifact_type' => 'Grimoire',
            'cover_material' => 'Dragonhide',
            'pages' => 300,
            'illustrated' => true,
            'notable_quotes' => ['“Magic is the fabric of the universe.”'],
        ]);
    }
}
