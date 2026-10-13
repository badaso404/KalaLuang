<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Notifications\Notification;

class ApplicationStatusChanged extends Notification
{
    public function __construct(public Application $application) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $vacancy = $this->application->jobVacancy;

        return [
            'application_id' => $this->application->id,
            'job_vacancy_id' => $vacancy->id,
            'vacancy_title' => $vacancy->title,
            'company_name' => $vacancy->company->name,
            'status' => $this->application->status->value,
            'message' => 'Status lamaran Anda untuk "'.$vacancy->title.'" kini: '.$this->application->status->label().'.',
        ];
    }
}
