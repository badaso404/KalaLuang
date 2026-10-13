<?php

namespace App\Enums;

enum EmploymentType: string
{
    case FullTime = 'full_time';
    case Internship = 'internship';
    case PartTime = 'part_time';

    public function label(): string
    {
        return match ($this) {
            self::FullTime => 'Penuh waktu',
            self::Internship => 'Magang',
            self::PartTime => 'Paruh waktu',
        };
    }
}
