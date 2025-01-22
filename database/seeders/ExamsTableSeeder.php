<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exam;

class ExamsTableSeeder extends Seeder
{
    public function run(): void
    {
        Exam::factory()->count(5)->create(); // Generate 5 sample exams
    }
}
