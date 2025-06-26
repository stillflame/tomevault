<?php

namespace Database\Seeders;

use App\Models\Character;
use Illuminate\Database\Seeder;

class CharacterSeeder extends Seeder
{
    public function run(): void
    {
        $characters = [
            ['name' => 'Merlin', 'bio' => 'Legendary wizard from Arthurian lore.'],
            ['name' => 'Victor Frankenstein', 'bio' => 'Famous scientist who created the monster.'],
            ['name' => 'Abdul Alhazred', 'bio' => 'The Mad Arab, legendary author of the Necronomicon.'],
            ['name' => 'Morgana Le Fay', 'bio' => 'Powerful enchantress and half-sister to King Arthur.'],
            ['name' => 'Gandalf', 'bio' => 'Wise and powerful wizard from Middle-earth.'],
            ['name' => 'H.P. Lovecraft', 'bio' => 'Author and creator of cosmic horror mythos.'],
            ['name' => 'John Dee', 'bio' => 'Advisor to Queen Elizabeth I and alchemist.'],
            ['name' => 'Aleister Crowley', 'bio' => 'Occultist, ceremonial magician, and writer.'],
            ['name' => 'Grigori Rasputin', 'bio' => 'Mystic and advisor to the last Russian Tsar.'],
            ['name' => 'Eliphas Levi', 'bio' => 'French occult author and magician.'],
            ['name' => 'The Witch of Endor', 'bio' => 'Famous necromancer from biblical lore.'],
            ['name' => 'Medea', 'bio' => 'Sorceress from Greek mythology known for her cunning and magic.'],
            ['name' => 'Baba Yaga', 'bio' => 'Fierce witch from Slavic folklore.'],
            ['name' => 'Salem Witch', 'bio' => 'Accused witch from the Salem witch trials.'],
            ['name' => 'Morgan Pendragon', 'bio' => 'Sorceress with ties to the Arthurian legends.'],
            ['name' => 'Prospero', 'bio' => 'The magician from Shakespeare\'s The Tempest.'],
            ['name' => 'Zatanna', 'bio' => 'Stage magician and real magician from DC Comics.'],
            ['name' => 'John Constantine', 'bio' => 'Occult detective and magician from DC Comics.'],
            ['name' => 'Rasputin', 'bio' => 'Mystic and controversial figure from Russian history.'],
            ['name' => 'Voldemort', 'bio' => 'Dark wizard from the Harry Potter series.'],
            ['name' => 'Circe', 'bio' => 'Enchantress in Greek mythology known for transforming men into animals.'],
            ['name' => 'Nostradamus', 'bio' => 'Famous seer and astrologer from the Renaissance.'],
            ['name' => 'Paracelsus', 'bio' => 'Renaissance physician, alchemist, and astrologer.'],
            ['name' => 'Helena Blavatsky', 'bio' => 'Founder of the Theosophical Society and occult writer.'],
            ['name' => 'John the Baptist', 'bio' => 'Religious figure associated with mysticism and prophecy.'],
            ['name' => 'Faust', 'bio' => 'Legendary scholar who made a pact with the devil.'],
            ['name' => 'Nicholas Flamel', 'bio' => 'Alchemist rumored to have discovered the Philosopher\'s Stone.'],
            ['name' => 'Saruman', 'bio' => 'Corrupted wizard from Middle-earth.'],
            ['name' => 'Theodora', 'bio' => 'Byzantine empress with rumored magical powers.'],
            ['name' => 'Melisandre', 'bio' => 'Red Priestess and sorceress from Game of Thrones.'],
            ['name' => 'Harry Dresden', 'bio' => 'Wizard detective from The Dresden Files.'],
            ['name' => 'Elminster', 'bio' => 'Legendary wizard from the Forgotten Realms.'],
        ];

        foreach ($characters as $character) {
            Character::factory()->create($character);
        }
    }
}
