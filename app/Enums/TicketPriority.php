<?php

namespace App\Enums;

enum TicketPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case CRITICAL = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::LOW => 'Low',
            self::MEDIUM => 'Medium',
            self::HIGH => 'High',
            self::CRITICAL => 'Critical',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::LOW => 'bg-slate-100 text-slate-700 border-slate-300',
            self::MEDIUM => 'bg-blue-50 text-blue-700 border-blue-200',
            self::HIGH => 'bg-amber-50 text-amber-700 border-amber-200',
            self::CRITICAL => 'bg-red-50 text-red-700 border-red-200',
        };
    }
}
