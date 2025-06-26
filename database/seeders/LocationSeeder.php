<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            [
                'name' => 'Camelot',
                'realm' => 'Arthurian Legends',
                'description' => 'The legendary castle and court of King Arthur, center of chivalry and magic.'
            ],
            [
                'name' => 'Frankenstein\'s Laboratory',
                'realm' => 'Gothic Horror',
                'description' => 'The eerie laboratory where Victor Frankenstein created his monstrous creation.'
            ],
            [
                'name' => 'The Black Library',
                'realm' => 'Arcane Knowledge',
                'description' => 'A secret vault of forbidden tomes and lost wisdom, hidden from mortal eyes.'
            ],
            [
                'name' => 'The Tower of Babel',
                'realm' => 'Ancient Mysticism',
                'description' => 'Fabled tower built to reach the heavens, shattered by divine intervention.'
            ],
            [
                'name' => 'Atlantis',
                'realm' => 'Lost Civilizations',
                'description' => 'Mythical sunken island civilization, home to advanced magic and technology.'
            ],
            [
                'name' => 'The Enchanted Forest',
                'realm' => 'Feywild',
                'description' => 'A mystical woodland realm inhabited by faeries, spirits, and ancient magic.'
            ],
            [
                'name' => 'The Great Library of Alexandria',
                'realm' => 'Classical Antiquity',
                'description' => 'Once the largest repository of knowledge in the ancient world, lost to fire.'
            ],
            [
                'name' => 'Ravenloft',
                'realm' => 'Dark Fantasy',
                'description' => 'A mist-shrouded domain ruled by gothic horror and vampire lords.'
            ],
            [
                'name' => 'The Iron Citadel',
                'realm' => 'Steampunk Realms',
                'description' => 'A fortress powered by steam and gears, center of inventors and alchemists.'
            ],
            [
                'name' => 'Eldritch Abyss',
                'realm' => 'Cosmic Horror',
                'description' => 'A dark dimension of madness and otherworldly entities beyond comprehension.'
            ],
            [
                'name' => 'Shambhala',
                'realm' => 'Mythical Kingdoms',
                'description' => 'A hidden spiritual kingdom said to be a place of peace and enlightenment.'
            ],
            [
                'name' => 'Valhalla',
                'realm' => 'Norse Mythology',
                'description' => 'The majestic hall where fallen warriors are received by Odin.'
            ],
            [
                'name' => 'The Labyrinth of Minos',
                'realm' => 'Greek Mythology',
                'description' => 'A vast maze built to contain the Minotaur beneath the palace of Knossos.'
            ],
            [
                'name' => 'The Silent City',
                'realm' => 'Post-Apocalyptic',
                'description' => 'A deserted metropolis where whispers of forgotten magic still linger.'
            ],
            [
                'name' => 'The Celestial Spire',
                'realm' => 'Astral Realms',
                'description' => 'A towering crystal spire connecting the mortal world to the stars.'
            ],
            [
                'name' => 'Isle of the Blessed',
                'realm' => 'Mythic Afterlife',
                'description' => 'A mystical island paradise where heroes rest after death.'
            ],
            [
                'name' => 'Necropolis of Shadows',
                'realm' => 'Dark Fantasy',
                'description' => 'An ancient city of the dead, shrouded in eternal twilight.'
            ],
            [
                'name' => 'Frostfang Mountains',
                'realm' => 'Frozen Wilderness',
                'description' => 'Jagged peaks wrapped in ice and legend, home to ancient secrets.'
            ],
            [
                'name' => 'The Verdant Glade',
                'realm' => 'Enchanted Forest',
                'description' => 'A lush clearing imbued with vibrant, living magic.'
            ],
            [
                'name' => 'The Obsidian Tower',
                'realm' => 'Forbidden Magic',
                'description' => 'A dark fortress of black glass and shadow, rumored to imprison dark spells.'
            ],
        ];


        foreach ($locations as $location) {
            Location::factory()->create($location);
        }
    }
}
