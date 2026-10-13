<?php

namespace App\Console\Commands;

use App\Enums\VacancyStatus;
use App\Models\JobVacancy;
use Illuminate\Console\Command;

class ExpireVacancies extends Command
{
    protected $signature = 'vacancies:expire';

    protected $description = 'Turunkan lowongan yang sudah melewati masa tayang 30 hari';

    public function handle(): int
    {
        $expired = JobVacancy::query()
            ->dueForExpiry()
            ->update(['status' => VacancyStatus::Expired]);

        $this->info($expired.' lowongan ditandai kedaluwarsa.');

        return self::SUCCESS;
    }
}
