<?php

namespace Database\Seeders;

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        Language::factory()->create(['name' => 'Ancient Greek', 'notes' => 'Used in many old manuscripts.']);
        Language::factory()->create(['name' => 'Latin', 'notes' => 'Classical language of scholars.']);
    }
}
