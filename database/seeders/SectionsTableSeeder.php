<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Section;

class SectionsTableSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\Exam::all()->each(function ($exam) {
            Section::factory()->count(3)->create([
                'exam_id' => $exam->id, // Link sections to existing exams
            ]);
        });
    }
}
