<?php

namespace App\Models;

use App\Enums\TicketStatus;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use BelongsToTenant;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class, // Otomatis mengubah string database menjadi Objek Enum!
            'first_response_deadline_at' => 'datetime',
            'first_responded_at' => 'datetime',
            'sla_deadline_at' => 'datetime',
            'resolved_at' => 'datetime',
            'sla_paused_at' => 'datetime',
            'is_sla_breached' => 'boolean',
        ];
    }

    public function slaPolicy()
    {
        return $this->belongsTo(SlaPolicy::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages()
    {
        return $this->hasMany(TicketMessage::class)->oldest();
    }

    public function activities()
    {
        return $this->hasMany(TicketActivity::class)->latest();
    }
}
