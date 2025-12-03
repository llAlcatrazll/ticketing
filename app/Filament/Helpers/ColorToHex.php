<?php

namespace App\Filament\Helpers;

use Filament\Support\Facades\FilamentColor;
use Spatie\Color\Rgb;

class ColorToHex
{
    public function __invoke(string $color = 'primary', int $shade = 500): string
    {
        return static::convert($color, $shade);
    }

    public static function convert(string $color = 'primary', int $shade = 500, string $alpha = "ff"): string
    {
        return Rgb::fromString('rgb('.FilamentColor::getColors()[$color][$shade].')')->toHex($alpha);
    }
}
