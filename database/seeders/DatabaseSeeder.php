<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ExamsTableSeeder::class,
            SectionsTableSeeder::class,
            QuestionsTableSeeder::class,
            OptionsTableSeeder::class,
        ]);
    }
}
