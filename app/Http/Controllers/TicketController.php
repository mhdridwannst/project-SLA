<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    public function __construct(protected TicketService $ticketService) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $query = Ticket::with(['slaPolicy', 'creator', 'assignee'])
            ->when(! $user->isStaff(), fn($q) => $q->where('created_by', $user->id))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('ticket_number', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%");
                });
            })
            ->latest();

        $tickets = $query->paginate(10)->withQueryString();

        // Statistik keseluruhan berdasarkan hak akses user
        $baseStatsQuery = Ticket::query()->when(! $user->isStaff(), fn($q) => $q->where('created_by', $user->id));

        $stats = [
            'total' => (clone $baseStatsQuery)->count(),
            'open' => (clone $baseStatsQuery)->whereIn('status', [
                TicketStatus::OPEN->value,
                TicketStatus::IN_PROGRESS->value,
                TicketStatus::WAITING_ON_CUSTOMER->value,
            ])->count(),
            'resolved' => (clone $baseStatsQuery)->whereIn('status', [
                TicketStatus::RESOLVED->value,
                TicketStatus::CLOSED->value,
            ])->count(),
            'breached' => (clone $baseStatsQuery)->where('is_sla_breached', true)->count(),
        ];

        return view('tickets.index', compact('tickets', 'stats'));
    }

    public function create()
    {
        $policies = SlaPolicy::all();
        $agents = User::where('tenant_id', auth()->user()->tenant_id)
            ->whereIn('role', ['admin', 'agent'])
            ->get();

        return view('tickets.create', compact('policies', 'agents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'sla_policy_id' => ['required', 'exists:sla_policies,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf,txt,log,zip', 'max:5120'],
        ]);

        $ticket = $this->ticketService->createTicket(
            $request->user(),
            $validated,
            $request->file('attachment')
        );

        return redirect()->route('tickets.show', $ticket)
            ->with('success', "Insiden {$ticket->ticket_number} berhasil diterbitkan.");
    }

    public function show(Ticket $ticket)
    {
        Gate::authorize('view', $ticket);

        $ticket->load(['slaPolicy', 'creator', 'assignee', 'messages.user', 'activities.user']);
        $agents = User::where('tenant_id', auth()->user()->tenant_id)
            ->whereIn('role', ['admin', 'agent'])
            ->get();

        return view('tickets.show', compact('ticket', 'agents'));
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        Gate::authorize('manage', $ticket);

        $request->validate([
            'status' => ['required', Rule::enum(TicketStatus::class)],
        ]);

        $newStatus = TicketStatus::from($request->status);
        $this->ticketService->updateStatus($ticket, $request->user(), $newStatus);

        return back()->with('success', "Status tiket diperbarui ke {$newStatus->label()}.");
    }

    public function assign(Request $request, Ticket $ticket)
    {
        Gate::authorize('manage', $ticket);

        $request->validate([
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        // CEGAH LOG REDUNDAN: Jika PIC yang dipilih sama dengan PIC saat ini, langsung batalkan
        if ((int) $ticket->assigned_to === (int) $request->assigned_to) {
            return back()->with('success', 'PIC tidak mengalami perubahan.');
        }

        $oldAssignee = $ticket->assignee?->name ?? 'Belum ditugaskan';
        $ticket->update(['assigned_to' => $request->assigned_to]);
        $newAssignee = $ticket->fresh()->assignee?->name ?? 'Belum ditugaskan';

        $this->ticketService->logActivity(
            $ticket,
            $request->user(),
            'assigned',
            "PIC diubah dari [{$oldAssignee}] ke [{$newAssignee}]."
        );

        return back()->with('success', 'Penugasan PIC berhasil diperbarui.');
    }

    public function storeMessage(Request $request, Ticket $ticket)
    {
        Gate::authorize('view', $ticket);

        $request->validate([
            'body' => ['required', 'string', 'min:2'],
            'is_internal' => ['nullable', 'boolean'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf,txt,log,zip', 'max:5120'],
        ]);

        $this->ticketService->addMessage(
            $ticket,
            $request->user(),
            $request->body,
            $request->boolean('is_internal'),
            $request->file('attachment')
        );

        return back()->with('success', 'Pesan berhasil dikirim ke ruang diskusi.');
    }

    public function exportCsv(Request $request)
    {
        $user = $request->user();

        $tickets = Ticket::with(['slaPolicy', 'creator', 'assignee'])
            ->when(! $user->isStaff(), fn($q) => $q->where('created_by', $user->id))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->latest()
            ->get();

        $filename = 'sla-audit-report-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($tickets) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Ticket ID',
                'Subject',
                'Priority',
                'Status',
                'Reporter',
                'Assignee',
                'First Response At',
                'Resolution Deadline',
                'Paused Minutes',
                'Resolved At',
                'SLA Status',
            ]);

            foreach ($tickets as $ticket) {
                fputcsv($handle, [
                    $ticket->ticket_number,
                    $ticket->title,
                    $ticket->slaPolicy->priority->label(),
                    $ticket->status->label(),
                    $ticket->creator->name,
                    $ticket->assignee->name ?? 'Unassigned',
                    $ticket->first_responded_at?->format('Y-m-d H:i:s') ?? 'Pending',
                    $ticket->sla_deadline_at->format('Y-m-d H:i:s'),
                    $ticket->total_paused_minutes,
                    $ticket->resolved_at?->format('Y-m-d H:i:s') ?? '-',
                    $ticket->is_sla_breached ? 'BREACHED' : 'COMPLIANT',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function markNotificationsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Seluruh notifikasi eskalasi telah ditandai dibaca.');
    }
}
