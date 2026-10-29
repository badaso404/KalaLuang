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
     * Seed sample vacancies across every job field, each with its Rp100.000
     * listing invoice. Only verified companies may post (BR-02). Two of them are
     * deliberately left mid-workflow so the admin queues are not empty.
     */
    public function run(): void
    {
        $admin = User::query()->where('role', UserRole::Admin)->firstOrFail();

        $companies = Company::query()->verified()->pluck('id', 'slug');
        $categories = Category::query()->pluck('id', 'slug');

        foreach ($this->vacancies($companies, $categories) as $data) {
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
     * @param  Collection<string, int>  $companies
     * @param  Collection<string, int>  $categories
     * @return list<array<string, mixed>>
     */
    protected function vacancies(Collection $companies, Collection $categories): array
    {
        $duration = config('kalaluang.listing_duration_days');

        $maju = $companies['pt-contoh-maju'];
        $kreatif = $companies['cv-contoh-kreatif'];
        $sehat = $companies['klinik-contoh-sehat'];
        $logistik = $companies['koperasi-contoh-logistik'];
        $rasa = $companies['pt-contoh-rasa-nusantara'];

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
            [
                'company_id' => $maju,
                'category_id' => $categories['penjualan'],
                'title' => 'Sales Development Trainee',
                'slug' => Str::slug('Sales Development Trainee PT Contoh Maju'),
                'employment_type' => EmploymentType::FullTime,
                'location' => 'Jakarta',
                'is_remote' => false,
                'min_education' => EducationLevel::D3,
                'description' => 'Program pembekalan enam bulan untuk calon tenaga penjualan: mengenali produk, menghubungi calon pelanggan, dan mendampingi senior saat presentasi.',
                'requirements' => "Lulusan D3 atau S1 semua jurusan\nFresh graduate, tidak wajib pengalaman kerja\nPercaya diri berbicara dengan orang baru\nBersedia melakukan kunjungan ke pelanggan di Jabodetabek",
                'how_to_apply' => 'Klik "Lamar Sekarang". Seleksi meliputi wawancara dan simulasi presentasi singkat.',
                'salary_min' => 4_500_000,
                'salary_max' => 6_000_000,
                'status' => VacancyStatus::Active,
                'published_at' => now()->subDays(6),
                'expires_at' => now()->subDays(6)->addDays($duration),
                'payment' => PaymentStatus::Paid,
            ],
            [
                'company_id' => $kreatif,
                'category_id' => $categories['sumber-daya-manusia'],
                'title' => 'HR Administration Intern',
                'slug' => Str::slug('HR Administration Intern CV Contoh Kreatif'),
                'employment_type' => EmploymentType::Internship,
                'location' => 'Bandung',
                'is_remote' => false,
                'min_education' => EducationLevel::D3,
                'description' => 'Membantu tim SDM merapikan berkas karyawan, menjadwalkan wawancara, dan menyiapkan materi orientasi karyawan baru.',
                'requirements' => "Lulusan D3 atau S1 Psikologi/Manajemen/Administrasi\nFresh graduate, tidak wajib pengalaman kerja\nTerbiasa menjaga kerahasiaan data\nTeliti dalam mengelola jadwal",
                'how_to_apply' => 'Klik "Lamar Sekarang" dan cantumkan alasan memilih bidang SDM pada surat lamaran.',
                'salary_min' => 2_000_000,
                'salary_max' => 2_600_000,
                'status' => VacancyStatus::Active,
                'published_at' => now()->subDays(7),
                'expires_at' => now()->subDays(7)->addDays($duration),
                'payment' => PaymentStatus::Paid,
            ],
            [
                'company_id' => $logistik,
                'category_id' => $categories['operasional-logistik'],
                'title' => 'Staf Gudang & Distribusi',
                'slug' => Str::slug('Staf Gudang dan Distribusi Koperasi Contoh Logistik'),
                'employment_type' => EmploymentType::FullTime,
                'location' => 'Semarang',
                'is_remote' => false,
                'min_education' => EducationLevel::D3,
                'description' => 'Mencatat barang masuk dan keluar, menyusun jadwal pengiriman harian, serta memastikan dokumen serah terima lengkap.',
                'requirements' => "Lulusan D3 atau S1 semua jurusan\nFresh graduate, tidak wajib pengalaman kerja\nTerbiasa menggunakan Microsoft Excel\nSiap bekerja di area gudang",
                'how_to_apply' => 'Klik "Lamar Sekarang". Wawancara dilakukan langsung di kantor koperasi.',
                'salary_min' => 3_800_000,
                'salary_max' => 4_500_000,
                'status' => VacancyStatus::Active,
                'published_at' => now()->subDays(8),
                'expires_at' => now()->subDays(8)->addDays($duration),
                'payment' => PaymentStatus::Paid,
            ],
            [
                'company_id' => $maju,
                'category_id' => $categories['produksi-manufaktur'],
                'title' => 'Operator Produksi Junior',
                'slug' => Str::slug('Operator Produksi Junior PT Contoh Maju'),
                'employment_type' => EmploymentType::FullTime,
                'location' => 'Bekasi',
                'is_remote' => false,
                'min_education' => EducationLevel::D3,
                'description' => 'Mengoperasikan mesin produksi sesuai prosedur, mencatat hasil produksi per shift, dan melaporkan kendala mesin ke penanggung jawab lini.',
                'requirements' => "Lulusan D3 atau D4 bidang Teknik\nFresh graduate, tidak wajib pengalaman kerja\nMemahami keselamatan kerja dasar\nBersedia bekerja dalam sistem shift",
                'how_to_apply' => 'Klik "Lamar Sekarang". Pelatihan mesin diberikan selama dua minggu pertama.',
                'salary_min' => 4_200_000,
                'salary_max' => 5_200_000,
                'status' => VacancyStatus::Active,
                'published_at' => now()->subDays(9),
                'expires_at' => now()->subDays(9)->addDays($duration),
                'payment' => PaymentStatus::Paid,
            ],
            [
                'company_id' => $kreatif,
                'category_id' => $categories['pendidikan-pelatihan'],
                'title' => 'Asisten Pengajar Kelas Digital',
                'slug' => Str::slug('Asisten Pengajar Kelas Digital CV Contoh Kreatif'),
                'employment_type' => EmploymentType::PartTime,
                'location' => 'Bandung',
                'is_remote' => false,
                'min_education' => EducationLevel::S1,
                'description' => 'Mendampingi peserta kelas pelatihan digital, menyiapkan materi latihan, dan merekap nilai tugas peserta.',
                'requirements' => "Lulusan S1 semua jurusan\nFresh graduate, tidak wajib pengalaman kerja\nNyaman berbicara di depan kelas kecil\nBersedia mengajar tiga hari per minggu",
                'how_to_apply' => 'Klik "Lamar Sekarang" dan sertakan tautan rekaman presentasi singkat bila ada.',
                'salary_min' => 2_500_000,
                'salary_max' => 3_200_000,
                'status' => VacancyStatus::Active,
                'published_at' => now()->subDays(10),
                'expires_at' => now()->subDays(10)->addDays($duration),
                'payment' => PaymentStatus::Paid,
            ],
            [
                'company_id' => $sehat,
                'category_id' => $categories['kesehatan'],
                'title' => 'Asisten Administrasi Klinik',
                'slug' => Str::slug('Asisten Administrasi Klinik Klinik Contoh Sehat'),
                'employment_type' => EmploymentType::FullTime,
                'location' => 'Yogyakarta',
                'is_remote' => false,
                'min_education' => EducationLevel::D3,
                'description' => 'Melayani pendaftaran pasien, merapikan rekam medis, dan menyiapkan laporan kunjungan harian klinik.',
                'requirements' => "Lulusan D3 atau S1 Rekam Medis/Administrasi Kesehatan\nFresh graduate, tidak wajib pengalaman kerja\nRamah dalam melayani pasien\nBersedia bekerja dalam dua shift",
                'how_to_apply' => 'Klik "Lamar Sekarang". Kandidat terpilih diundang wawancara di klinik.',
                'salary_min' => 3_600_000,
                'salary_max' => 4_300_000,
                'status' => VacancyStatus::Active,
                'published_at' => now()->subDays(11),
                'expires_at' => now()->subDays(11)->addDays($duration),
                'payment' => PaymentStatus::Paid,
            ],
            [
                'company_id' => $maju,
                'category_id' => $categories['hukum'],
                'title' => 'Staf Legal Administratif',
                'slug' => Str::slug('Staf Legal Administratif PT Contoh Maju'),
                'employment_type' => EmploymentType::FullTime,
                'location' => 'Jakarta',
                'is_remote' => false,
                'min_education' => EducationLevel::S1,
                'description' => 'Mengarsipkan kontrak, memeriksa kelengkapan dokumen perizinan, dan membantu penyiapan draf perjanjian standar.',
                'requirements' => "Lulusan S1 Ilmu Hukum\nFresh graduate, tidak wajib pengalaman kerja\nTeliti membaca dokumen panjang\nMemahami dasar hukum perjanjian",
                'how_to_apply' => 'Klik "Lamar Sekarang". Seleksi meliputi tes membaca dokumen kontrak.',
                'salary_min' => 5_000_000,
                'salary_max' => 6_500_000,
                'status' => VacancyStatus::Active,
                'published_at' => now()->subDays(12),
                'expires_at' => now()->subDays(12)->addDays($duration),
                'payment' => PaymentStatus::Paid,
            ],
            [
                'company_id' => $kreatif,
                'category_id' => $categories['media-konten'],
                'title' => 'Content Writer Junior',
                'slug' => Str::slug('Content Writer Junior CV Contoh Kreatif'),
                'employment_type' => EmploymentType::PartTime,
                'location' => null,
                'is_remote' => true,
                'min_education' => EducationLevel::D3,
                'description' => 'Menulis artikel blog dan naskah media sosial untuk klien agensi, lengkap dengan riset kata kunci sederhana.',
                'requirements' => "Lulusan D3 atau S1 semua jurusan\nFresh graduate, tidak wajib pengalaman kerja\nMenulis dalam Bahasa Indonesia yang rapi\nMemiliki contoh tulisan, boleh tugas kuliah",
                'how_to_apply' => 'Klik "Lamar Sekarang" dan cantumkan tautan dua contoh tulisan pada profil Anda.',
                'salary_min' => 2_400_000,
                'salary_max' => 3_500_000,
                'status' => VacancyStatus::Active,
                'published_at' => now()->subDays(13),
                'expires_at' => now()->subDays(13)->addDays($duration),
                'payment' => PaymentStatus::Paid,
            ],
            [
                'company_id' => $logistik,
                'category_id' => $categories['teknik-konstruksi'],
                'title' => 'Drafter Junior',
                'slug' => Str::slug('Drafter Junior Koperasi Contoh Logistik'),
                'employment_type' => EmploymentType::FullTime,
                'location' => 'Semarang',
                'is_remote' => false,
                'min_education' => EducationLevel::D3,
                'description' => 'Menggambar rencana tata letak gudang dan revisi denah sesuai arahan tim teknik.',
                'requirements' => "Lulusan D3 atau D4 Teknik Sipil/Arsitektur\nFresh graduate, tidak wajib pengalaman kerja\nMenguasai AutoCAD dasar\nMampu membaca gambar teknik",
                'how_to_apply' => 'Klik "Lamar Sekarang" dan lampirkan portofolio gambar teknik kuliah.',
                'salary_min' => 4_000_000,
                'salary_max' => 5_000_000,
                'status' => VacancyStatus::Active,
                'published_at' => now()->subDays(14),
                'expires_at' => now()->subDays(14)->addDays($duration),
                'payment' => PaymentStatus::Paid,
            ],
            [
                'company_id' => $kreatif,
                'category_id' => $categories['data-riset'],
                'title' => 'Junior Data Analyst',
                'slug' => Str::slug('Junior Data Analyst CV Contoh Kreatif'),
                'employment_type' => EmploymentType::FullTime,
                'location' => null,
                'is_remote' => true,
                'min_education' => EducationLevel::S1,
                'description' => 'Merapikan data kampanye klien, membuat laporan mingguan, dan menyiapkan visualisasi sederhana untuk presentasi.',
                'requirements' => "Lulusan S1 Statistika/Matematika/Sistem Informasi\nFresh graduate, tidak wajib pengalaman kerja\nMenguasai Excel atau Google Sheets tingkat menengah\nMemahami dasar SQL",
                'how_to_apply' => 'Klik "Lamar Sekarang". Terdapat tes pengolahan data singkat sebelum wawancara.',
                'salary_min' => 5_500_000,
                'salary_max' => 7_000_000,
                'status' => VacancyStatus::Active,
                'published_at' => now()->subDays(15),
                'expires_at' => now()->subDays(15)->addDays($duration),
                'payment' => PaymentStatus::Paid,
            ],
            [
                'company_id' => $rasa,
                'category_id' => $categories['pariwisata-perhotelan'],
                'title' => 'Staf Front Office Hotel',
                'slug' => Str::slug('Staf Front Office Hotel PT Contoh Rasa Nusantara'),
                'employment_type' => EmploymentType::FullTime,
                'location' => 'Denpasar',
                'is_remote' => false,
                'min_education' => EducationLevel::D3,
                'description' => 'Menyambut tamu, mengelola proses check-in dan check-out, serta menjawab pertanyaan tamu melalui telepon dan surel.',
                'requirements' => "Lulusan D3 atau D4 Perhotelan/Pariwisata\nFresh graduate, tidak wajib pengalaman kerja\nMampu berbahasa Inggris percakapan\nBersedia bekerja dalam sistem shift",
                'how_to_apply' => 'Klik "Lamar Sekarang". Wawancara sebagian dilakukan dalam Bahasa Inggris.',
                'salary_min' => 4_000_000,
                'salary_max' => 5_000_000,
                'status' => VacancyStatus::Active,
                'published_at' => now()->subDays(16),
                'expires_at' => now()->subDays(16)->addDays($duration),
                'payment' => PaymentStatus::Paid,
            ],
            [
                'company_id' => $rasa,
                'category_id' => $categories['pertanian-pangan'],
                'title' => 'Asisten Quality Control Pangan',
                'slug' => Str::slug('Asisten Quality Control Pangan PT Contoh Rasa Nusantara'),
                'employment_type' => EmploymentType::FullTime,
                'location' => 'Denpasar',
                'is_remote' => false,
                'min_education' => EducationLevel::D4,
                'description' => 'Mengambil sampel bahan baku, mencatat hasil uji mutu, dan memastikan proses pengolahan sesuai standar keamanan pangan.',
                'requirements' => "Lulusan D4 atau S1 Teknologi Pangan/Pertanian\nFresh graduate, tidak wajib pengalaman kerja\nMemahami dasar keamanan pangan\nTeliti mencatat hasil pengujian",
                'how_to_apply' => 'Klik "Lamar Sekarang". Kandidat terpilih mengikuti peninjauan unit pengolahan.',
                'salary_min' => 4_300_000,
                'salary_max' => 5_400_000,
                'status' => VacancyStatus::Active,
                'published_at' => now()->subDays(17),
                'expires_at' => now()->subDays(17)->addDays($duration),
                'payment' => PaymentStatus::Paid,
            ],
        ];
    }
}
