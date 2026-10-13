<?php

namespace Database\Seeders;

use App\Enums\EducationLevel;
use App\Enums\EmploymentType;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Enums\VacancyStatus;
use App\Models\Category;
use App\Models\Company;
use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class JobVacancySeeder extends Seeder
{
    /**
     * Seed six sample vacancies for the two verified companies, each with its
     * Rp100.000 listing invoice. Only verified companies may post (BR-02).
     */
    public function run(): void
    {
        $admin = User::query()->where('role', UserRole::Admin)->firstOrFail();

        $companies = Company::query()->verified()->pluck('id', 'slug');
        $categories = Category::query()->pluck('id', 'slug');

        $maju = $companies['pt-contoh-maju'];
        $kreatif = $companies['cv-contoh-kreatif'];

        foreach ($this->vacancies($maju, $kreatif, $categories) as $data) {
            $payment = $data['payment'];
            unset($data['payment']);

            $vacancy = JobVacancy::query()->firstOrCreate(
                ['slug' => $data['slug']],
                $data,
            );

            $isPaid = $payment === PaymentStatus::Paid;

            $vacancy->payments()->firstOrCreate(
                ['invoice_number' => 'INV-'.now()->format('Ym').'-'.str_pad((string) $vacancy->id, 4, '0', STR_PAD_LEFT)],
                [
                    'company_id' => $vacancy->company_id,
                    'amount' => config('kalaluang.listing_price'),
                    'status' => $payment,
                    'proof_path' => $payment === PaymentStatus::Unpaid ? null : 'payment-proofs/'.$vacancy->slug.'.jpg',
                    'paid_at' => $isPaid ? $vacancy->published_at ?? now()->subDays(3) : null,
                    'confirmed_by' => $isPaid ? $admin->id : null,
                ],
            );
        }
    }

    /**
     * @param  Collection<string, int>  $categories
     * @return list<array<string, mixed>>
     */
    protected function vacancies(int $maju, int $kreatif, Collection $categories): array
    {
        $duration = config('kalaluang.listing_duration_days');

        return [
            [
                'company_id' => $maju,
                'category_id' => $categories['administrasi'],
                'title' => 'Staf Administrasi Junior',
                'slug' => Str::slug('Staf Administrasi Junior PT Contoh Maju'),
                'employment_type' => EmploymentType::FullTime,
                'location' => 'Jakarta',
                'is_remote' => false,
                'min_education' => EducationLevel::D3,
                'description' => 'Mendukung kegiatan administrasi harian kantor: pengarsipan dokumen, input data, dan penyiapan laporan rutin untuk tim operasional.',
                'requirements' => "Lulusan D3 atau S1 semua jurusan\nFresh graduate, tidak wajib pengalaman kerja\nMampu mengoperasikan Microsoft Office\nTeliti dan terbiasa bekerja dengan dokumen",
                'how_to_apply' => 'Klik "Lamar Sekarang" dan pastikan CV pada profil Anda sudah terunggah.',
                'salary_min' => 4_200_000,
                'salary_max' => 5_000_000,
                'status' => VacancyStatus::Active,
                'published_at' => now()->subDays(5),
                'expires_at' => now()->subDays(5)->addDays($duration),
                'payment' => PaymentStatus::Paid,
            ],
            [
                'company_id' => $kreatif,
                'category_id' => $categories['pemasaran'],
                'title' => 'Digital Marketing Trainee',
                'slug' => Str::slug('Digital Marketing Trainee CV Contoh Kreatif'),
                'employment_type' => EmploymentType::Internship,
                'location' => 'Bandung',
                'is_remote' => false,
                'min_education' => EducationLevel::D3,
                'description' => 'Program magang enam bulan untuk mempelajari pengelolaan media sosial, penulisan konten, dan pelaporan performa kampanye.',
                'requirements' => "Lulusan D3 atau S1 semua jurusan\nFresh graduate, tidak wajib pengalaman kerja\nAktif menggunakan media sosial\nBersedia magang penuh waktu selama 6 bulan",
                'how_to_apply' => 'Lamar melalui tombol "Lamar Sekarang" dan sertakan tautan portofolio bila ada.',
                'salary_min' => 2_000_000,
                'salary_max' => 2_500_000,
                'status' => VacancyStatus::Active,
                'published_at' => now()->subDays(4),
                'expires_at' => now()->subDays(4)->addDays($duration),
                'payment' => PaymentStatus::Paid,
            ],
            [
                'company_id' => $maju,
                'category_id' => $categories['keuangan'],
                'title' => 'Asisten Keuangan',
                'slug' => Str::slug('Asisten Keuangan PT Contoh Maju'),
                'employment_type' => EmploymentType::FullTime,
                'location' => 'Surabaya',
                'is_remote' => false,
                'min_education' => EducationLevel::D3,
                'description' => 'Membantu tim keuangan dalam pencatatan transaksi, rekonsiliasi bank sederhana, dan penyiapan dokumen penagihan.',
                'requirements' => "Lulusan D3 atau S1 Akuntansi/Keuangan/Manajemen\nFresh graduate, tidak wajib pengalaman kerja\nMemahami pembukuan dasar\nMampu menggunakan Microsoft Excel",
                'how_to_apply' => 'Klik "Lamar Sekarang". Seleksi dilakukan melalui wawancara daring.',
                'salary_min' => 4_500_000,
                'salary_max' => 5_500_000,
                'status' => VacancyStatus::Active,
                'published_at' => now()->subDays(3),
                'expires_at' => now()->subDays(3)->addDays($duration),
                'payment' => PaymentStatus::Paid,
            ],
            [
                'company_id' => $kreatif,
                'category_id' => $categories['teknologi-informasi'],
                'title' => 'Junior Web Support',
                'slug' => Str::slug('Junior Web Support CV Contoh Kreatif'),
                'employment_type' => EmploymentType::Internship,
                'location' => null,
                'is_remote' => true,
                'min_education' => EducationLevel::D3,
                'description' => 'Membantu pemeliharaan situs klien: pembaruan konten, perbaikan tampilan ringan, dan pemantauan laporan error.',
                'requirements' => "Lulusan D3 atau S1 bidang Informatika/Sistem Informasi\nFresh graduate, tidak wajib pengalaman kerja\nMemahami dasar HTML dan CSS\nMemiliki koneksi internet yang memadai untuk kerja remote",
                'how_to_apply' => 'Klik "Lamar Sekarang" dan lampirkan tautan proyek kuliah atau portofolio.',
                'salary_min' => 2_200_000,
                'salary_max' => 3_000_000,
                'status' => VacancyStatus::Active,
                'published_at' => now()->subDays(2),
                'expires_at' => now()->subDays(2)->addDays($duration),
                'payment' => PaymentStatus::Paid,
            ],
            [
                'company_id' => $maju,
                'category_id' => $categories['customer-service'],
                'title' => 'Customer Service Officer',
                'slug' => Str::slug('Customer Service Officer PT Contoh Maju'),
                'employment_type' => EmploymentType::FullTime,
                'location' => 'Jakarta',
                'is_remote' => false,
                'min_education' => EducationLevel::D3,
                'description' => 'Menangani pertanyaan pelanggan melalui telepon dan surel, serta mencatat keluhan ke dalam sistem tiket.',
                'requirements' => "Lulusan D3 atau S1 semua jurusan\nFresh graduate, tidak wajib pengalaman kerja\nKomunikasi lisan dan tulisan yang baik\nBersedia bekerja dalam sistem shift",
                'how_to_apply' => 'Klik "Lamar Sekarang". Kandidat terpilih akan dihubungi melalui surel.',
                'salary_min' => 4_000_000,
                'salary_max' => 4_800_000,
                'status' => VacancyStatus::UnderReview,
                'published_at' => null,
                'expires_at' => null,
                'payment' => PaymentStatus::Paid,
            ],
            [
                'company_id' => $kreatif,
                'category_id' => $categories['desain'],
                'title' => 'Junior Graphic Designer',
                'slug' => Str::slug('Junior Graphic Designer CV Contoh Kreatif'),
                'employment_type' => EmploymentType::PartTime,
                'location' => 'Bandung',
                'is_remote' => false,
                'min_education' => EducationLevel::D3,
                'description' => 'Menyiapkan aset visual untuk kebutuhan media sosial dan materi promosi klien agensi.',
                'requirements' => "Lulusan D3 atau S1 Desain Komunikasi Visual atau sejenis\nFresh graduate, tidak wajib pengalaman kerja\nMenguasai Figma atau Adobe Photoshop\nMemiliki portofolio karya kuliah",
                'how_to_apply' => 'Klik "Lamar Sekarang" dan cantumkan tautan portofolio pada profil Anda.',
                'salary_min' => null,
                'salary_max' => null,
                'status' => VacancyStatus::PendingPayment,
                'published_at' => null,
                'expires_at' => null,
                'payment' => PaymentStatus::WaitingConfirmation,
            ],
        ];
    }
}
