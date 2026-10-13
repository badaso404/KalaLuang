<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case WaitingConfirmation = 'waiting_confirmation';
    case Paid = 'paid';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Belum Dibayar',
            self::WaitingConfirmation => 'Menunggu Konfirmasi',
            self::Paid => 'Lunas',
            self::Rejected => 'Ditolak',
        };
    }

    /**
     * Tailwind classes for the status pill shown in the UI.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Unpaid => 'bg-slate-100 text-slate-700',
            self::WaitingConfirmation => 'bg-amber-100 text-amber-800',
            self::Paid => 'bg-emerald-100 text-emerald-800',
            self::Rejected => 'bg-rose-100 text-rose-800',
        };
    }
}
