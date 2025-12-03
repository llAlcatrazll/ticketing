<?php

namespace App\Enums;

use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasDescription, HasLabel
{
    case ROOT = 'root';
    case ADMIN = 'admin';
    case MODERATOR = 'moderator';
    case AUDITOR = 'auditor';
    case AGENT = 'agent';
    case USER = 'user';

    public function getLabel(): ?string
    {
        return match ($this) {
            default => mb_ucfirst($this->value),
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::ROOT => 'User with full access to the system.',
            self::ADMIN => 'User with full access to the organization.',
            self::MODERATOR => 'User with access to moderate incoming requests.',
            self::AGENT => 'User with access to handle incoming requests.',
            self::USER => 'User with standard access to the system.',
            self::AUDITOR => 'User with read-only access to the client feedback.',
            default => '',
        };
    }

    public static function options(bool $root = false): array
    {
        $filtered = array_filter(
            self::cases(),
            fn (self $role) => $root || ! in_array($role, [self::ROOT])
        );

        return array_combine(
            array_map(fn (self $role) => $role->value, $filtered),
            array_map(fn (self $role) => $role->getLabel(), $filtered)
        );
    }
}
