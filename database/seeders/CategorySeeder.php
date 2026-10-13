<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Seed the job fields ("bidang") shown on the home page and catalogue filter.
     */
    public function run(): void
    {
        $names = [
            'Administrasi',
            'Pemasaran',
            'Teknologi Informasi',
            'Keuangan',
            'Desain',
            'Customer Service',
        ];

        foreach ($names as $name) {
            Category::query()->firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );
        }
    }
}
