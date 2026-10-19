<?php

namespace App\Enums;

enum TicketStatus: string
{
    case OPEN = 'open';
    case IN_PROGRESS = 'in_progress';
    case WAITING_ON_CUSTOMER = 'waiting_on_customer';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Open',
            self::IN_PROGRESS => 'In Progress',
            self::WAITING_ON_CUSTOMER => 'Waiting on Customer',
            self::RESOLVED => 'Resolved',
            self::CLOSED => 'Closed',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::OPEN => 'bg-sky-50 text-sky-700 border-sky-200',
            self::IN_PROGRESS => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            self::WAITING_ON_CUSTOMER => 'bg-amber-50 text-amber-800 border-amber-200',
            self::RESOLVED => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::CLOSED => 'bg-slate-100 text-slate-600 border-slate-300',
        };
    }
}
