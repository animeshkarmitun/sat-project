<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;

class QuestionsTableSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\Section::all()->each(function ($section) {
            Question::factory()->count(10)->create([
                'section_id' => $section->id, // Link questions to existing sections
            ]);
        });
    }
}
