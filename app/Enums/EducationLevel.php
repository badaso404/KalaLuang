<?php

namespace App\Enums;

enum EducationLevel: string
{
    case D3 = 'D3';
    case D4 = 'D4';
    case S1 = 'S1';
    case S2 = 'S2';

    public function label(): string
    {
        return match ($this) {
            self::D3 => 'D3',
            self::D4 => 'D4',
            self::S1 => 'S1',
            self::S2 => 'S2',
        };
    }
}
