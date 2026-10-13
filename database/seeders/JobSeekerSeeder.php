<?php

namespace Database\Seeders;

use App\Enums\EducationLevel;
use App\Enums\UserRole;
use App\Models\JobSeekerProfile;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class JobSeekerSeeder extends Seeder
{
    /**
     * Seed five fresh graduates with complete profiles, so they satisfy BR-06
     * and can apply straight away.
     */
    public function run(): void
    {
        $skills = Skill::query()->pluck('id', 'name');

        foreach ($this->seekers() as $data) {
            $user = User::query()->firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'role' => UserRole::JobSeeker,
                    'email_verified_at' => now(),
                ],
            );

            $profile = JobSeekerProfile::query()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'phone' => $data['phone'],
                    'city' => $data['city'],
                    'education_level' => $data['education_level'],
                    'institution' => $data['institution'],
                    'major' => $data['major'],
                    'graduation_year' => $data['graduation_year'],
                    'bio' => $data['bio'],
                    'portfolio_url' => $data['portfolio_url'],
                    'cv_path' => 'cv/'.str($data['name'])->slug().'.pdf',
                ],
            );

            $profile->skills()->sync($skills->only($data['skills'])->all());
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function seekers(): array
    {
        return [
            [
                'email' => 'rina.pratiwi@example.test',
                'name' => 'Rina Pratiwi',
                'phone' => '081200000001',
                'city' => 'Jakarta',
                'education_level' => EducationLevel::S1,
                'institution' => 'Universitas Contoh Jakarta',
                'major' => 'Manajemen',
                'graduation_year' => 2025,
                'bio' => 'Lulusan Manajemen yang tertarik pada pekerjaan administrasi dan operasional.',
                'portfolio_url' => null,
                'skills' => ['Microsoft Office', 'Komunikasi', 'Bahasa Inggris'],
            ],
            [
                'email' => 'dimas.saputra@example.test',
                'name' => 'Dimas Saputra',
                'phone' => '081200000002',
                'city' => 'Bekasi',
                'education_level' => EducationLevel::D3,
                'institution' => 'Politeknik Contoh Bandung',
                'major' => 'Akuntansi',
                'graduation_year' => 2025,
                'bio' => 'Lulusan D3 Akuntansi dengan minat pada pembukuan dan pelaporan keuangan.',
                'portfolio_url' => null,
                'skills' => ['Microsoft Excel', 'Akuntansi Dasar', 'Pembukuan'],
            ],
            [
                'email' => 'sari.wulandari@example.test',
                'name' => 'Sari Wulandari',
                'phone' => '081200000003',
                'city' => 'Depok',
                'education_level' => EducationLevel::S1,
                'institution' => 'Universitas Contoh Depok',
                'major' => 'Ilmu Komunikasi',
                'graduation_year' => 2024,
                'bio' => 'Lulusan Ilmu Komunikasi yang aktif menulis konten media sosial.',
                'portfolio_url' => 'https://sariwulandari.example.test',
                'skills' => ['Copywriting', 'Social Media Marketing', 'Komunikasi'],
            ],
            [
                'email' => 'anita.rahmawati@example.test',
                'name' => 'Anita Rahmawati',
                'phone' => '081200000004',
                'city' => 'Bandung',
                'education_level' => EducationLevel::D4,
                'institution' => 'Politeknik Contoh Bandung',
                'major' => 'Desain Komunikasi Visual',
                'graduation_year' => 2025,
                'bio' => 'Desainer pemula yang berfokus pada aset visual media sosial.',
                'portfolio_url' => 'https://anitarahmawati.example.test',
                'skills' => ['Figma', 'Adobe Photoshop'],
            ],
            [
                'email' => 'fajar.hidayat@example.test',
                'name' => 'Fajar Hidayat',
                'phone' => '081200000005',
                'city' => 'Yogyakarta',
                'education_level' => EducationLevel::S1,
                'institution' => 'Universitas Contoh Yogyakarta',
                'major' => 'Sistem Informasi',
                'graduation_year' => 2025,
                'bio' => 'Lulusan Sistem Informasi yang tertarik pada web development dan dukungan teknis.',
                'portfolio_url' => 'https://github.com/fajarhidayat-contoh',
                'skills' => ['HTML & CSS', 'JavaScript', 'PHP', 'Analisis Data'],
            ],
        ];
    }
}
