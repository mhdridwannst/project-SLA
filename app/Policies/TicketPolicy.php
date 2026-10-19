<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    /**
     * Admin & Agent bisa melihat seluruh tiket perusahaan.
     * Customer hanya bisa melihat tiket yang dia buat sendiri.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->tenant_id !== $ticket->tenant_id) {
            return false;
        }

        return $user->isStaff() || $ticket->created_by === $user->id;
    }

    /**
     * Hanya Admin & Agent yang boleh mengubah status, PIC, atau menulis Internal Note.
     */
    public function manage(User $user, Ticket $ticket): bool
    {
        return $user->tenant_id === $ticket->tenant_id && $user->isStaff();
    }
}
