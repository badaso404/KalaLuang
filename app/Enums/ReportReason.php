<?php

namespace App\Enums;

enum ReportReason: string
{
    case AsksFee = 'asks_fee';
    case FakeCompany = 'fake_company';
    case RequiresExperience = 'requires_experience';
    case Misleading = 'misleading';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::AsksFee => 'Meminta biaya',
            self::FakeCompany => 'Perusahaan fiktif',
            self::RequiresExperience => 'Mensyaratkan pengalaman',
            self::Misleading => 'Informasi menyesatkan',
            self::Other => 'Lainnya',
        };
    }
}
