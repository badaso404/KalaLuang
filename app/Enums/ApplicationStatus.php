<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case New = 'new';
    case Reviewed = 'reviewed';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Baru',
            self::Reviewed => 'Diproses',
            self::Accepted => 'Diterima',
            self::Rejected => 'Ditolak',
        };
    }

    /**
     * Tailwind classes for the status pill shown in the UI.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::New => 'bg-blue-100 text-blue-800',
            self::Reviewed => 'bg-amber-100 text-amber-800',
            self::Accepted => 'bg-emerald-100 text-emerald-800',
            self::Rejected => 'bg-rose-100 text-rose-800',
        };
    }
}
