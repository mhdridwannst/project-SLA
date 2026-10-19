<?php

namespace App\Models;

use App\Enums\TicketPriority;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class SlaPolicy extends Model
{
    use BelongsToTenant;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'priority' => TicketPriority::class,
        ];
    }
}
