<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            CategorySeeder::class,
            SkillSeeder::class,
            CompanySeeder::class,
            JobVacancySeeder::class,
            JobSeekerSeeder::class,
            ApplicationSeeder::class,
        ]);
    }
}
