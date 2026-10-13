<?php

namespace App\Enums;

enum DocumentType: string
{
    case Nib = 'nib';
    case Akta = 'akta';
    case Npwp = 'npwp';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Nib => 'NIB',
            self::Akta => 'Akta Pendirian',
            self::Npwp => 'NPWP',
            self::Other => 'Dokumen Lain',
        };
    }
}
