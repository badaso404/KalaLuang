<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Pending = 'pending';
    case Valid = 'valid';
    case Dismissed = 'dismissed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Tinjauan',
            self::Valid => 'Terbukti',
            self::Dismissed => 'Tidak Terbukti',
        };
    }

    /**
     * Tailwind classes for the status pill shown in the UI.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-100 text-amber-800',
            self::Valid => 'bg-rose-100 text-rose-800',
            self::Dismissed => 'bg-slate-100 text-slate-700',
        };
    }
}
