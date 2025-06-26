<?php

namespace Database\Seeders;

use App\Enums\ArtifactType;
use App\Enums\CoverMaterial;
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
        // Get common references
        $merlin = Character::where('name', 'Merlin')->first();
        $ancientGreek = Language::where('name', 'Ancient Greek')->first();
        $camelot = Location::where('name', 'Camelot')->first();

        // 1. The Book of Spells (existing tome)
        Tome::factory()->create([
            'title' => 'The Book of Spells',
            'alternate_titles' => ['Grimoire of Magic'],
            'origin' => 'Camelot',
            'author_id' => $merlin->id,
            'language_id' => $ancientGreek->id,
            'contents_summary' => 'A powerful tome containing ancient spells.',
            'cursed' => true,
            'sentient' => false,
            'current_owner_id' => $merlin->id,
            'last_known_location_id' => $camelot->id,
            'danger_level' => DangerLevel::Severe,
            'artifact_type' => ArtifactType::Grimoire,
            'cover_material' => CoverMaterial::Dragonhide,
            'pages' => 300,
            'illustrated' => true,
            'notable_quotes' => ['"Magic is the fabric of the universe."'],
        ]);

        // 2. The Necronomicon
        $abdul = Character::where('name', 'Abdul Alhazred')->first();
        $arabic = Language::where('name', 'Arabic')->first();
        $eldritchAbyss = Location::where('name', 'Eldritch Abyss')->first();
        $blackLibrary = Location::where('name', 'The Black Library')->first();

        Tome::factory()->create([
            'title' => 'The Necronomicon',
            'alternate_titles' => ['Kitab al-Azif', 'Book of the Dead Names'],
            'origin' => 'Damascus',
            'author_id' => $abdul->id,
            'language_id' => $arabic->id,
            'contents_summary' => 'A blasphemous tome containing forbidden knowledge of cosmic horrors and ancient gods that predate humanity.',
            'cursed' => true,
            'sentient' => true,
            'current_owner_id' => null,
            'last_known_location_id' => $eldritchAbyss->id,
            'danger_level' => DangerLevel::Severe,
            'artifact_type' => ArtifactType::Grimoire,
            'cover_material' => CoverMaterial::BasiliskHide,
            'pages' => 666,
            'illustrated' => true,
            'notable_quotes' => [
                '"That is not dead which can eternal lie, and with strange aeons even death may die."',
                '"Ph\'nglui mglw\'nafh Cthulhu R\'lyeh wgah\'nagl fhtagn."'
            ],
        ]);

        // 3. Tobin's Spirit Guide
        $tobin = Character::where('name', 'Jonathon John Horace Tobin')->first();
        $oldEnglish = Language::where('name', 'Old English')->first();
        $greatLibrary = Location::where('name', 'The Great Library of Alexandria')->first();

        Tome::factory()->create([
            'title' => 'Tobin\'s Spirit Guide',
            'alternate_titles' => ['A Comprehensive Study of Supernatural Phenomena', 'The Ghost Hunter\'s Manual'],
            'origin' => 'London',
            'author_id' => $tobin->id,
            'language_id' => $oldEnglish->id,
            'contents_summary' => 'A detailed catalog of supernatural entities, their behaviors, and methods for dealing with paranormal manifestations.',
            'cursed' => false,
            'sentient' => false,
            'current_owner_id' => $tobin->id,
            'last_known_location_id' => $greatLibrary->id,
            'danger_level' => DangerLevel::Medium,
            'artifact_type' => ArtifactType::Compendium,
            'cover_material' => CoverMaterial::LeatherBound,
            'pages' => 485,
            'illustrated' => true,
            'notable_quotes' => [
                '"When investigating the supernatural, one must maintain both courage and caution."',
                '"The dead do not rest easy when their business remains unfinished."'
            ],
        ]);

        // 4. The Malleus Maleficarum
        $johnDee = Character::where('name', 'John Dee')->first();
        $latin = Language::where('name', 'Latin')->first();
        $obsidianTower = Location::where('name', 'The Obsidian Tower')->first();

        Tome::factory()->create([
            'title' => 'Malleus Maleficarum',
            'alternate_titles' => ['The Hammer of Witches', 'Hexenhammer'],
            'origin' => 'Holy Roman Empire',
            'author_id' => $johnDee->id,
            'language_id' => $latin->id,
            'contents_summary' => 'A treatise on the identification, prosecution, and elimination of witchcraft, containing both theological arguments and practical methods.',
            'cursed' => false,
            'sentient' => false,
            'current_owner_id' => null,
            'last_known_location_id' => $obsidianTower->id,
            'danger_level' => DangerLevel::High,
            'artifact_type' => ArtifactType::Treatise,
            'cover_material' => CoverMaterial::RuneInscribedLeather,
            'pages' => 669,
            'illustrated' => false,
            'notable_quotes' => [
                '"No one does more harm to the Catholic faith than midwives."',
                '"All wickedness is but little to the wickedness of a woman."'
            ],
        ]);
    }
}
