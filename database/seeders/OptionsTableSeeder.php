<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Option;

class OptionsTableSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\Question::all()->each(function ($question) {
            Option::factory()->count(4)->create([
                'question_id' => $question->id, // Link options to existing questions
                'is_correct' => rand(0, 1), // Randomly mark options as correct
            ]);
        });
    }
}
