<?php

namespace App\Enums;

enum UserRole: string
{
    case JobSeeker = 'job_seeker';
    case Company = 'company';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::JobSeeker => 'Pencari Kerja',
            self::Company => 'Perusahaan',
            self::Admin => 'Admin',
        };
    }
}
