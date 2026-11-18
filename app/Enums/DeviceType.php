<?php

namespace App\Enums;

enum DeviceType: string
{
    case Mobile = 'mobile';
    case Tablet = 'tablet';
    case Desktop = 'desktop';

    public function label(): string
    {
        return match ($this) {
            self::Mobile => 'Ponsel',
            self::Tablet => 'Tablet',
            self::Desktop => 'Desktop',
        };
    }

    /**
     * Classify a visitor by user agent. Deliberately coarse: we only want to
     * know whether the layout is being read on a small screen or a large one.
     */
    public static function fromUserAgent(?string $userAgent): self
    {
        $userAgent = mb_strtolower((string) $userAgent);

        if ($userAgent === '') {
            return self::Desktop;
        }

        if (str_contains($userAgent, 'ipad') || str_contains($userAgent, 'tablet')) {
            return self::Tablet;
        }

        if (preg_match('/android|iphone|ipod|mobile|opera mini|windows phone/', $userAgent) === 1) {
            return self::Mobile;
        }

        return self::Desktop;
    }
}
