<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SlaBreachedNotification extends Notification
{
    use Queueable;

    public function __construct(public Ticket $ticket) {}

    /**
     * Simpan notifikasi ke dalam database agar muncul di aplikasi.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Format pesan peringatan eskalasi SLA.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title' => $this->ticket->title,
            'deadline' => $this->ticket->sla_deadline_at->format('d M Y, H:i'),
            'message' => "PERINGATAN SLA: Tiket {$this->ticket->ticket_number} ({$this->ticket->title}) telah melewati batas waktu penyelesaian!",
        ];
    }
}
