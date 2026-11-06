<?php

namespace Database\Seeders;

use App\Enums\CompanyVerificationStatus;
use App\Enums\DocumentType;
use App\Enums\EducationLevel;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\JobSeekerProfile;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Seed one account per role so a freshly deployed environment can be logged
 * into. Safe to re-run: accounts are matched by email and their password is
 * reset to the current DEMO_*_PASSWORD value each time.
 *
 * Run it on its own, never through DatabaseSeeder:
 *   php artisan db:seed --class=DemoAccountSeeder --force
 */
class DemoAccountSeeder extends Seeder
{
    /**
     * Credential lines collected while seeding, printed once at the end.
     *
     * @var list<string>
     */
    protected array $credentials = [];

    public function run(): void
    {
        $admin = $this->seedAdmin();

        $this->seedCompany($admin);
        $this->seedJobSeeker();

        $this->report();
    }

    /**
     * The super admin. Admins cannot self-register, so this is the only way
     * to get one into a deployed environment.
     */
    protected function seedAdmin(): User
    {
        $email = (string) config('demo.admin_email');
        $password = $this->passwordFor('demo.admin_password');

        $admin = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin',
                'password' => Hash::make($password),
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
            ],
        );

        $this->credentials[] = sprintf('  Admin      : %s / %s', $email, $password);

        return $admin;
    }

    /**
     * A verified company, so it can post vacancies without waiting on manual
     * verification.
     */
    protected function seedCompany(User $admin): void
    {
        $email = (string) config('demo.company_email');
        $password = $this->passwordFor('demo.company_password');

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Rizky Maulana',
                'password' => Hash::make($password),
                'role' => UserRole::Company,
                'email_verified_at' => now(),
            ],
        );

        $company = Company::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => 'PT Kalaluang Demo',
                'slug' => Str::slug('PT Kalaluang Demo'),
                'industry' => 'Teknologi',
                'city' => 'Jakarta',
                'address' => 'Jl. Demo No. 1, Jakarta Pusat',
                'website' => 'https://kalaluang.test',
                'description' => 'Akun perusahaan demo untuk menelusuri alur pasang lowongan dan seleksi pelamar.',
                'verification_status' => CompanyVerificationStatus::Verified,
                'verified_at' => now(),
                'verified_by' => $admin->id,
            ],
        );

        $documentPath = 'company-documents/'.$company->slug.'-nib.pdf';

        $company->documents()->firstOrCreate(
            ['type' => DocumentType::Nib],
            [
                'file_path' => $documentPath,
                'uploaded_at' => now(),
            ],
        );

        $this->writePlaceholderPdf($documentPath, 'NIB - '.$company->name);

        $this->credentials[] = sprintf('  Perusahaan : %s / %s', $email, $password);
    }

    /**
     * A job seeker whose profile already satisfies BR-06, so it can apply to
     * vacancies straight away.
     */
    protected function seedJobSeeker(): void
    {
        $email = (string) config('demo.job_seeker_email');
        $password = $this->passwordFor('demo.job_seeker_password');

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Putri Ananda',
                'password' => Hash::make($password),
                'role' => UserRole::JobSeeker,
                'email_verified_at' => now(),
            ],
        );

        $cvPath = 'cv/putri-ananda.pdf';

        $profile = JobSeekerProfile::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'phone' => '081200000010',
                'city' => 'Jakarta',
                'education_level' => EducationLevel::S1,
                'institution' => 'Universitas Kalaluang',
                'major' => 'Sistem Informasi',
                'graduation_year' => 2025,
                'bio' => 'Akun pelamar demo dengan profil lengkap untuk mencoba alur lamaran.',
                'portfolio_url' => null,
                'cv_path' => $cvPath,
            ],
        );

        $this->writePlaceholderPdf($cvPath, 'CV - '.$user->name);

        $skillIds = collect(['Microsoft Office', 'Komunikasi', 'Bahasa Inggris'])
            ->map(fn (string $name): int => Skill::query()->firstOrCreate(['name' => $name])->id)
            ->all();

        $profile->skills()->sync($skillIds);

        $this->credentials[] = sprintf('  Pelamar    : %s / %s', $email, $password);
    }

    /**
     * Read the password from config, falling back to a freshly generated one
     * so a deployed environment never gets a password that is committed to
     * the repository.
     */
    protected function passwordFor(string $key): string
    {
        $password = config($key);

        return filled($password) ? (string) $password : Str::password(16, symbols: false);
    }

    /**
     * Write a readable placeholder PDF to the private disk the download
     * controllers read from, so "lihat CV" and "lihat dokumen" return a real
     * file instead of a 404. Existing files are left alone.
     */
    protected function writePlaceholderPdf(string $path, string $title): void
    {
        $disk = Storage::disk('local');

        if ($disk->exists($path)) {
            return;
        }

        $disk->put($path, $this->placeholderPdf($title));
    }

    /**
     * Build a single-page PDF containing one line of text. Written by hand to
     * avoid pulling in a PDF library for seed data.
     */
    protected function placeholderPdf(string $title): string
    {
        $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $title);

        $stream = 'BT /F1 16 Tf 72 760 Td ('.$text.") Tj ET\n"
            .'BT /F1 11 Tf 72 730 Td (Dokumen contoh untuk keperluan demo.) Tj ET';

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] '
                .'/Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            '<< /Length '.strlen($stream)." >>\nstream\n".$stream."\nendstream",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $index => $body) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n".$body."\nendobj\n";
        }

        $startXref = strlen($pdf);
        $count = count($objects) + 1;

        $pdf .= "xref\n0 ".$count."\n0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf."trailer\n<< /Size ".$count." /Root 1 0 R >>\n"
            ."startxref\n".$startXref."\n%%EOF\n";
    }

    /**
     * Print the credentials once, since generated passwords are hashed on save
     * and cannot be recovered afterwards.
     */
    protected function report(): void
    {
        $this->command?->newLine();
        $this->command?->info('Akun demo siap dipakai (email / password):');

        foreach ($this->credentials as $line) {
            $this->command?->line($line);
        }

        $this->command?->newLine();
        $this->command?->warn('Simpan sekarang. Password yang digenerate tidak bisa dilihat lagi setelah ini.');
    }
}
