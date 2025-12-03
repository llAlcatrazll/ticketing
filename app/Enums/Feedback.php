<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

enum Feedback: string implements HasLabel, HasDescription, HasColor
{
    //Standardization
    case ISO = 'iso';
    case ARTA = 'arta';

    //Service Type
    case INTERNAL = 'internal';
    case EXTERNAL = 'external';

    //Client Type
    case CITIZEN = 'citizen';
    case BUSINESS = 'business';
    case GOVERNMENT = 'government';

    case NONE = '';

    public function getLabel(): ?string
    {
        return ucfirst(match($this) {
            //Standardization
            self::ISO => 'ISO',
            self::ARTA => 'ARTA',

            //Service Type
            self::INTERNAL => 'Internal',
            self::EXTERNAL => 'External',

            //Client Type
            self::CITIZEN => 'citizen',
            self::BUSINESS => 'business',
            self::GOVERNMENT => 'government or another agency',
            default => null,
        });
    }

    public function getColor(): ?string
    {
        return match ($this) {
            //Standardization
            self::ISO => 'success',
            self::ARTA => 'warning',

            //Service Type
            self::INTERNAL => 'success',
            self::EXTERNAL => 'danger',

            //Client Type
            self::CITIZEN => 'info',
            self::BUSINESS => 'warning',
            self::GOVERNMENT => 'danger',
            default => null,
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            //Standardization
            self::ISO => 'International Organization for Standardization',
            self::ARTA => 'Anti-Red Tape Act',

            //Service Type
            self::INTERNAL => 'Services offered within the organization',
            self::EXTERNAL => 'Services offered to clients or customers',

            //Client Type
            self::CITIZEN => 'Individual receiving services for personal matters',
            self::BUSINESS => 'Company or organization receiving services for commercial purposes',
            self::GOVERNMENT => 'Government entity or another agency receiving services for official functions',
            default => null,
        };
    }

    public static function clientTypesDescription(): array
    {
        return [
            self::CITIZEN->value => self::CITIZEN->getDescription(),
            self::BUSINESS->value => self::BUSINESS->getDescription(),
            self::GOVERNMENT->value => self::GOVERNMENT->getDescription(),
        ];
    }

    public static function serviceTypesDescription(): array
    {
        return [
            self::INTERNAL->value => self::INTERNAL->getDescription(),
            self::EXTERNAL->value => self::EXTERNAL->getDescription(),
        ];
    }

    public static function standardizationsDescription(): array
    {
        return [
            self::ISO->value => self::ISO->getDescription(),
            self::ARTA->value => self::ARTA->getDescription(),
        ];
    }

    public static function clientTypesLabel(): array
    {
        return [
            self::CITIZEN->value => self::CITIZEN->getLabel(),
            self::BUSINESS->value => self::BUSINESS->getLabel(),
            self::GOVERNMENT->value => self::GOVERNMENT->getLabel(),
        ];
    }

    public static function serviceTypesLabel(): array
    {
        return [
            self::INTERNAL->value => self::INTERNAL->getLabel(),
            self::EXTERNAL->value => self::EXTERNAL->getLabel(),
        ];
    }

    public static function standardizationsLabel(): array
    {
        return [
            self::ISO->value => self::ISO->getLabel(),
            self::ARTA->value => self::ARTA->getLabel(),
        ];
    }
}
