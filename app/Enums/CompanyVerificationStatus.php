<?php

namespace App\Enums;

enum CompanyVerificationStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Verifikasi',
            self::Verified => 'Terverifikasi',
            self::Rejected => 'Ditolak',
            self::Suspended => 'Ditangguhkan',
        };
    }

    /**
     * Tailwind classes for the status pill shown in the UI.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-100 text-amber-800',
            self::Verified => 'bg-emerald-100 text-emerald-800',
            self::Rejected => 'bg-rose-100 text-rose-800',
            self::Suspended => 'bg-rose-100 text-rose-800',
        };
    }
}
