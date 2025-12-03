<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum RequestClass: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case INQUIRY = 'inquiry';
    case SUGGESTION = 'suggestion';
    case TICKET = 'ticket';
    case COMPLAINT = 'complaint';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::INQUIRY => 'success',
            self::SUGGESTION => 'info',
            self::TICKET => 'warning',
            default => null,
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::INQUIRY => 'An inquiry is a request for information or clarification on a topic or service, typically not requiring immediate action e.g. "Can you explain how this feature works?"',
            self::SUGGESTION => 'A suggestion is a request for a new feature or improvement e.g. "I suggest that we work on improving the performance of the application."',
            self::TICKET => 'A ticket is a request for technical support or assistance that needs resolving e.g. "My account is locked, I need help recovering it."',
            default => null,
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::INQUIRY => 'heroicon-o-question-mark-circle',
            self::SUGGESTION => 'heroicon-o-light-bulb',
            self::TICKET => 'heroicon-o-ticket',
            default => 'heroicon-o-lifebuoy',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::INQUIRY => 'Inquiry',
            self::SUGGESTION => 'Suggestion',
            self::TICKET => 'Ticket',
            default => 'Request',
        };
    }
}
