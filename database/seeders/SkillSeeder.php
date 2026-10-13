<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    /**
     * Seed the skill options offered on the job seeker profile form.
     */
    public function run(): void
    {
        $names = [
            'Microsoft Office',
            'Microsoft Excel',
            'Komunikasi',
            'Copywriting',
            'Social Media Marketing',
            'SEO',
            'Akuntansi Dasar',
            'Pembukuan',
            'Analisis Data',
            'HTML & CSS',
            'JavaScript',
            'PHP',
            'Figma',
            'Adobe Photoshop',
            'Customer Handling',
            'Bahasa Inggris',
        ];

        foreach ($names as $name) {
            Skill::query()->firstOrCreate(['name' => $name]);
        }
    }
}
