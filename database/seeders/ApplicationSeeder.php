<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Enums\ReportReason;
use App\Models\Application;
use App\Models\JobVacancy;
use App\Models\SavedVacancy;
use App\Models\User;
use App\Models\VacancyReport;
use Illuminate\Database\Seeder;

class ApplicationSeeder extends Seeder
{
    /**
     * Seed applications for "Staf Administrasi Junior", plus a saved vacancy and
     * a pending report so the company dashboard and admin panel have data.
     */
    public function run(): void
    {
        $vacancy = JobVacancy::query()
            ->where('slug', 'staf-administrasi-junior-pt-contoh-maju')
            ->firstOrFail();

        $applicants = [
            ['email' => 'rina.pratiwi@example.test', 'status' => ApplicationStatus::New],
            ['email' => 'dimas.saputra@example.test', 'status' => ApplicationStatus::Reviewed],
            ['email' => 'sari.wulandari@example.test', 'status' => ApplicationStatus::New],
        ];

        foreach ($applicants as $index => $data) {
            $user = User::query()->where('email', $data['email'])->firstOrFail();

            Application::query()->firstOrCreate(
                [
                    'job_vacancy_id' => $vacancy->id,
                    'user_id' => $user->id,
                ],
                [
                    'cv_path' => $user->jobSeekerProfile->cv_path,
                    'cover_letter' => 'Saya tertarik pada posisi '.$vacancy->title.' dan siap belajar sebagai lulusan baru.',
                    'status' => $data['status'],
                    'status_updated_at' => $data['status'] === ApplicationStatus::New
                        ? null
                        : now()->subDay(),
                    'created_at' => now()->subDays(3 - $index),
                ],
            );
        }

        $fajar = User::query()->where('email', 'fajar.hidayat@example.test')->firstOrFail();

        $remoteVacancy = JobVacancy::query()
            ->where('slug', 'junior-web-support-cv-contoh-kreatif')
            ->firstOrFail();

        SavedVacancy::query()->firstOrCreate([
            'user_id' => $fajar->id,
            'job_vacancy_id' => $remoteVacancy->id,
        ]);

        VacancyReport::query()->firstOrCreate(
            [
                'job_vacancy_id' => $remoteVacancy->id,
                'reporter_id' => $fajar->id,
            ],
            [
                'reason' => ReportReason::Misleading,
                'description' => 'Lokasi disebut remote, tetapi deskripsi menyebut kehadiran di kantor.',
            ],
        );
    }
}
