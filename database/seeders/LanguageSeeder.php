<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            ['name' => 'Ancient Greek', 'notes' => 'Used in many old manuscripts.'],
            ['name' => 'Latin', 'notes' => 'Classical language of scholars.'],
            ['name' => 'Old English', 'notes' => 'Language of early medieval England.'],
            ['name' => 'Sanskrit', 'notes' => 'Classical language of ancient India, used in sacred texts.'],
            ['name' => 'Hebrew', 'notes' => 'Language of the Hebrew Bible and Jewish liturgy.'],
            ['name' => 'Aramaic', 'notes' => 'Ancient Semitic language used in Near East scriptures.'],
            ['name' => 'Old Norse', 'notes' => 'Language of the Viking Age Scandinavia.'],
            ['name' => 'Middle English', 'notes' => 'Spoken in England between the 12th and 15th centuries.'],
            ['name' => 'Classical Chinese', 'notes' => 'Literary form of Chinese used in ancient texts.'],
            ['name' => 'Coptic', 'notes' => 'Egyptian language used by early Christians.'],
            ['name' => 'Gothic', 'notes' => 'Extinct East Germanic language.'],
            ['name' => 'Akkadian', 'notes' => 'Ancient Semitic language of Mesopotamia.'],
            ['name' => 'Phoenician', 'notes' => 'Ancient language of the Mediterranean coast.'],
            ['name' => 'Old Church Slavonic', 'notes' => 'First Slavic literary language.'],
            ['name' => 'Arabic', 'notes' => 'Classical and Quranic Arabic, language of many ancient manuscripts.'],
            ['name' => 'Persian', 'notes' => 'Language of many historic Middle Eastern texts.'],
            ['name' => 'Sumerian', 'notes' => 'Language of the earliest known civilization.'],
            ['name' => 'Runic', 'notes' => 'Scripts used by Germanic peoples before adoption of Latin alphabet.'],
        ];

        foreach ($languages as $language) {
            Language::factory()->create($language);
        }
    }
}
