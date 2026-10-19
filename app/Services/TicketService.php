<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketService
{
    public function createTicket(User $creator, array $data, ?UploadedFile $attachment = null): Ticket
    {
        return DB::transaction(function () use ($creator, $data, $attachment) {
            $policy = SlaPolicy::findOrFail($data['sla_policy_id']);
            $now = now();

            $attachmentPath = null;
            $attachmentName = null;

            if ($attachment) {
                $attachmentName = $attachment->getClientOriginalName();
                $attachmentPath = $attachment->store("tenants/{$creator->tenant_id}/tickets", 'public');
            }

            $ticket = Ticket::create([
                'ticket_number' => 'TKT-' . $now->format('Ym') . '-' . strtoupper(Str::random(5)),
                'created_by' => $creator->id,
                'assigned_to' => $data['assigned_to'] ?? null,
                'sla_policy_id' => $policy->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'attachment_path' => $attachmentPath,
                'attachment_name' => $attachmentName,
                'status' => TicketStatus::OPEN,
                'first_response_deadline_at' => $now->copy()->addMinutes($policy->first_response_time_minutes),
                'sla_deadline_at' => $now->copy()->addMinutes($policy->resolution_time_minutes),
                'is_sla_breached' => false,
            ]);

            $this->logActivity(
                $ticket,
                $creator,
                'ticket_created',
                "Tiket diterbitkan dengan kebijakan {$policy->name} (Target respon: {$policy->first_response_time_minutes}m, Resolusi: {$policy->resolution_time_minutes}m)."
            );

            return $ticket;
        });
    }

    public function updateStatus(Ticket $ticket, User $actor, TicketStatus $newStatus): Ticket
    {
        $oldStatus = $ticket->status;

        if ($oldStatus === $newStatus) {
            return $ticket;
        }

        return DB::transaction(function () use ($ticket, $actor, $oldStatus, $newStatus) {
            $now = now();

            if ($newStatus === TicketStatus::WAITING_ON_CUSTOMER && $ticket->sla_paused_at === null) {
                $ticket->sla_paused_at = $now;
                $this->logActivity($ticket, $actor, 'sla_paused', 'Arloji SLA dijeda karena menunggu konfirmasi pelanggan.');
            }

            if ($oldStatus === TicketStatus::WAITING_ON_CUSTOMER && $newStatus !== TicketStatus::WAITING_ON_CUSTOMER && $ticket->sla_paused_at !== null) {
                $pausedMinutes = (int) max(1, $ticket->sla_paused_at->diffInMinutes($now));
                $ticket->sla_deadline_at = $ticket->sla_deadline_at->addMinutes($pausedMinutes);
                $ticket->total_paused_minutes += $pausedMinutes;
                $ticket->sla_paused_at = null;

                $this->logActivity(
                    $ticket,
                    $actor,
                    'sla_resumed',
                    "Arloji SLA dilanjutkan (Total jeda: {$pausedMinutes} menit). Tenggat penyelesaian disesuaikan."
                );
            }

            if ($newStatus === TicketStatus::RESOLVED && $ticket->resolved_at === null) {
                $ticket->resolved_at = $now;
                if ($now->greaterThan($ticket->sla_deadline_at)) {
                    $ticket->is_sla_breached = true;
                }
            }

            $ticket->status = $newStatus;
            $ticket->save();

            $this->logActivity(
                $ticket,
                $actor,
                'status_changed',
                "Status diubah dari [{$oldStatus->label()}] menjadi [{$newStatus->label()}].",
                ['old' => $oldStatus->value, 'new' => $newStatus->value]
            );

            return $ticket;
        });
    }

    public function addMessage(Ticket $ticket, User $sender, string $body, bool $isInternal = false, ?UploadedFile $attachment = null): TicketMessage
    {
        return DB::transaction(function () use ($ticket, $sender, $body, $isInternal, $attachment) {
            if (! $sender->isStaff()) {
                $isInternal = false;
            }

            $attachmentPath = null;
            $attachmentName = null;

            if ($attachment) {
                $attachmentName = $attachment->getClientOriginalName();
                $attachmentPath = $attachment->store("tenants/{$sender->tenant_id}/attachments", 'public');
            }

            $message = TicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => $sender->id,
                'body' => $body,
                'attachment_path' => $attachmentPath,
                'attachment_name' => $attachmentName,
                'is_internal' => $isInternal,
            ]);

            if ($sender->isStaff() && ! $isInternal && $ticket->first_responded_at === null) {
                $ticket->update(['first_responded_at' => now()]);
                $this->logActivity($ticket, $sender, 'first_response', 'Respons publik pertama telah diberikan oleh tim teknis.');
            }

            if (! $sender->isStaff() && $ticket->status === TicketStatus::WAITING_ON_CUSTOMER) {
                $this->updateStatus($ticket, $sender, TicketStatus::IN_PROGRESS);
            }

            $noteType = $isInternal ? 'Catatan Internal (Rahasia Tim)' : 'Balasan Publik';
            $this->logActivity($ticket, $sender, 'message_added', "Menambahkan {$noteType}" . ($attachmentName ? " beserta lampiran [{$attachmentName}]." : '.'));

            return $message;
        });
    }

    public function logActivity(Ticket $ticket, ?User $actor, string $action, string $description, ?array $properties = null): void
    {
        TicketActivity::create([
            'tenant_id' => $ticket->tenant_id,
            'ticket_id' => $ticket->id,
            'user_id' => $actor?->id,
            'action' => $action,
            'description' => $description,
            'properties' => $properties,
        ]);
    }
}
