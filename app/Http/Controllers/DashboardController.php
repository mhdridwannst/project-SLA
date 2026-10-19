<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $tickets = Ticket::with(['slaPolicy', 'creator', 'assignee'])
            ->when(! $user->isStaff(), fn($q) => $q->where('created_by', $user->id))
            ->latest()
            ->get();

        $totalTickets = $tickets->count();
        $breachedCount = $tickets->where('is_sla_breached', true)->count();
        $onTrackCount = $totalTickets - $breachedCount;

        $activeCount = $tickets->whereIn('status', [
            TicketStatus::OPEN,
            TicketStatus::IN_PROGRESS,
            TicketStatus::WAITING_ON_CUSTOMER,
        ])->count();

        $resolvedCount = $tickets->whereIn('status', [
            TicketStatus::RESOLVED,
            TicketStatus::CLOSED,
        ])->count();

        $slaComplianceRate = $totalTickets > 0
            ? round(($onTrackCount / $totalTickets) * 100, 1)
            : 100.0;

        $respondedTickets = $tickets->whereNotNull('first_responded_at');
        $avgFirstResponseMinutes = $respondedTickets->isNotEmpty()
            ? (int) round($respondedTickets->avg(fn($t) => $t->created_at->diffInMinutes($t->first_responded_at)))
            : null;

        // Ambil 6 tiket terbaru (baik aktif maupun selesai agar tabel tidak kosong)
        $recentTickets = $tickets->take(6);

        // Beban kerja tim dilengkapi persentase kapasitas
        $agentWorkloads = User::where('tenant_id', $user->tenant_id)
            ->whereIn('role', [UserRole::ADMIN->value, UserRole::AGENT->value])
            ->get()
            ->map(function ($agent) use ($tickets) {
                $active = $tickets->where('assigned_to', $agent->id)
                    ->whereIn('status', [TicketStatus::OPEN, TicketStatus::IN_PROGRESS, TicketStatus::WAITING_ON_CUSTOMER])
                    ->count();

                $resolved = $tickets->where('assigned_to', $agent->id)
                    ->whereIn('status', [TicketStatus::RESOLVED, TicketStatus::CLOSED])
                    ->count();

                $totalAssigned = $active + $resolved;

                return [
                    'id' => $agent->id,
                    'name' => $agent->name,
                    'email' => $agent->email,
                    'initials' => collect(explode(' ', $agent->name))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join(''),
                    'role' => $agent->role->label(),
                    'active' => $active,
                    'resolved' => $resolved,
                    'completion_rate' => $totalAssigned > 0 ? round(($resolved / $totalAssigned) * 100) : 0,
                ];
            });

        // Live Audit Stream (5 aktivitas terakhir di perusahaan ini)
        $recentActivities = TicketActivity::with(['user', 'ticket'])
            ->latest()
            ->take(6)
            ->get();

        $policies = SlaPolicy::orderBy('resolution_time_minutes', 'asc')->get();
        $agents = User::where('tenant_id', $user->tenant_id)
            ->whereIn('role', [UserRole::ADMIN->value, UserRole::AGENT->value])
            ->get();

        return view('dashboard', compact(
            'totalTickets',
            'activeCount',
            'resolvedCount',
            'breachedCount',
            'onTrackCount',
            'slaComplianceRate',
            'avgFirstResponseMinutes',
            'recentTickets',
            'agentWorkloads',
            'recentActivities',
            'policies',
            'agents'
        ));
    }
}
