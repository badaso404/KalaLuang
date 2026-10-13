<?php

namespace App\Enums;

enum VacancyStatus: string
{
    case Draft = 'draft';
    case PendingPayment = 'pending_payment';
    case UnderReview = 'under_review';
    case Active = 'active';
    case Rejected = 'rejected';
    case Expired = 'expired';
    case Closed = 'closed';
    case TakenDown = 'taken_down';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::PendingPayment => 'Menunggu Pembayaran',
            self::UnderReview => 'Sedang Ditinjau',
            self::Active => 'Tayang',
            self::Rejected => 'Ditolak',
            self::Expired => 'Kedaluwarsa',
            self::Closed => 'Ditutup',
            self::TakenDown => 'Diturunkan',
        };
    }

    /**
     * Tailwind classes for the status pill shown in the UI.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Draft => 'bg-slate-100 text-slate-700',
            self::PendingPayment => 'bg-amber-100 text-amber-800',
            self::UnderReview => 'bg-blue-100 text-blue-800',
            self::Active => 'bg-emerald-100 text-emerald-800',
            self::Rejected => 'bg-rose-100 text-rose-800',
            self::Expired => 'bg-slate-100 text-slate-600',
            self::Closed => 'bg-slate-200 text-slate-700',
            self::TakenDown => 'bg-rose-100 text-rose-800',
        };
    }
}
