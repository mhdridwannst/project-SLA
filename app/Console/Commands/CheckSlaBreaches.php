<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\SlaBreachedNotification;
use Illuminate\Console\Command;

class CheckSlaBreaches extends Command
{
    /**
     * Nama perintah yang bisa dijalankan di terminal: php artisan sla:check
     */
    protected $signature = 'sla:check';

    /**
     * Deskripsi command.
     */
    protected $description = 'Memindai seluruh tiket aktif yang melewati batas waktu SLA dan mengirim notifikasi eskalasi';

    public function handle(): void
    {
        // POIN PENTING UNTUK INTERVIEW:
        // Karena robot ini berjalan di background (tanpa user login), kita gunakan
        // withoutGlobalScope('tenant') untuk memeriksa tiket dari SEMUA perusahaan sekaligus.
        $overdueTickets = Ticket::withoutGlobalScope('tenant')
            ->whereIn('status', ['open', 'in_progress'])
            ->where('is_sla_breached', false)
            ->where('sla_deadline_at', '<', now())
            ->get();

        if ($overdueTickets->isEmpty()) {
            $this->info('Semua tiket aman (On Track). Tidak ada pelanggaran SLA baru.');
            return;
        }

        foreach ($overdueTickets as $ticket) {
            // 1. Tandai tiket sebagai melanggar SLA (Breached)
            $ticket->update([
                'is_sla_breached' => true,
            ]);

            // 2. Kirim notifikasi eskalasi ke seluruh Admin dan PIC di perusahaan tersebut
            $recipients = User::where('tenant_id', $ticket->tenant_id)
                ->where(function ($query) use ($ticket) {
                    $query->where('role', 'admin')
                          ->orWhere('id', $ticket->assigned_to);
                })
                ->get();

            foreach ($recipients as $user) {
                $user->notify(new SlaBreachedNotification($ticket));
            }

            $this->warn("Tiket {$ticket->ticket_number} ditandai BREACHED & notifikasi dikirim.");
        }

        $this->info("Selesai! Total {$overdueTickets->count()} tiket ditandai melanggar SLA.");
    }
}