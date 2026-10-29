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
            'Penjualan',
            'Sumber Daya Manusia',
            'Operasional & Logistik',
            'Produksi & Manufaktur',
            'Pendidikan & Pelatihan',
            'Kesehatan',
            'Hukum',
            'Media & Konten',
            'Teknik & Konstruksi',
            'Data & Riset',
            'Pariwisata & Perhotelan',
            'Pertanian & Pangan',
        ];

        foreach ($names as $name) {
            Category::query()->firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );
        }
    }
}
