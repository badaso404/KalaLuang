<?php

namespace Database\Seeders;

use App\Enums\CompanyVerificationStatus;
use App\Enums\DocumentType;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompanySeeder extends Seeder
{
    /**
     * Seed three companies: two verified, one still awaiting verification.
     */
    public function run(): void
    {
        $admin = User::query()->where('role', UserRole::Admin)->firstOrFail();

        $companies = [
            [
                'email' => 'hr@contohmaju.test',
                'contact' => 'Budi Santoso',
                'name' => 'PT Contoh Maju',
                'industry' => 'Manufaktur',
                'city' => 'Jakarta',
                'address' => 'Jl. Gatot Subroto No. 12, Jakarta Selatan',
                'website' => 'https://contohmaju.test',
                'description' => 'Perusahaan manufaktur yang rutin membuka kesempatan bagi lulusan baru di bidang administrasi dan keuangan.',
                'verification_status' => CompanyVerificationStatus::Verified,
            ],
            [
                'email' => 'people@contohkreatif.test',
                'contact' => 'Lestari Dewi',
                'name' => 'CV Contoh Kreatif',
                'industry' => 'Agensi Kreatif',
                'city' => 'Bandung',
                'address' => 'Jl. Dago No. 45, Bandung',
                'website' => 'https://contohkreatif.test',
                'description' => 'Agensi kreatif yang membina fresh graduate melalui program magang pemasaran digital dan web support.',
                'verification_status' => CompanyVerificationStatus::Verified,
            ],
            [
                'email' => 'founder@startupcontoh.test',
                'contact' => 'Arif Nugroho',
                'name' => 'Startup Contoh',
                'industry' => 'Teknologi',
                'city' => 'Remote',
                'address' => null,
                'website' => 'https://startupcontoh.test',
                'description' => 'Startup tahap awal yang bekerja sepenuhnya secara remote.',
                'verification_status' => CompanyVerificationStatus::Pending,
            ],
        ];

        foreach ($companies as $data) {
            $user = User::query()->firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['contact'],
                    'password' => Hash::make('password'),
                    'role' => UserRole::Company,
                    'email_verified_at' => now(),
                ],
            );

            $isVerified = $data['verification_status'] === CompanyVerificationStatus::Verified;

            $company = Company::query()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']),
                    'industry' => $data['industry'],
                    'city' => $data['city'],
                    'address' => $data['address'],
                    'website' => $data['website'],
                    'description' => $data['description'],
                    'verification_status' => $data['verification_status'],
                    'verified_at' => $isVerified ? now()->subMonth() : null,
                    'verified_by' => $isVerified ? $admin->id : null,
                ],
            );

            $company->documents()->firstOrCreate(
                ['type' => DocumentType::Nib],
                [
                    'file_path' => 'company-documents/'.$company->slug.'-nib.pdf',
                    'uploaded_at' => now()->subMonth()->subDay(),
                ],
            );
        }
    }
}
