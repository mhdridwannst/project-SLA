<x-app-layout>
    <x-slot name="header">
        <span>Incident Queue</span>
    </x-slot>

    <div class="py-6 px-4 sm:px-8 max-w-7xl mx-auto space-y-6">

        @if (session('success'))
            <div
                class="px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-xs font-medium">
                {{ session('success') }}
            </div>
        @endif

        <!-- Header Halaman & Aksi -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-zinc-900 tracking-tight">Incident Queue & SLA Monitor</h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Daftar seluruh laporan insiden operasional, filter status penyelesaian, dan kepatuhan tenggat waktu.
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('tickets.export', request()->query()) }}"
                    class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white hover:bg-zinc-50 text-zinc-700 text-xs font-medium rounded-md border border-zinc-200 shadow-2xs transition">
                    <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    <span>Export CSV</span>
                </a>
            </div>
        </div>

        <!-- Strip Metrik Ringkas -->
        <div
            class="bg-white rounded-xl border border-zinc-200/90 shadow-2xs grid grid-cols-2 lg:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-zinc-200/80">
            <div class="p-4">
                <p class="text-xs font-medium text-zinc-500">Total Incidents</p>
                <p class="text-2xl font-semibold font-mono text-zinc-900 mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="p-4">
                <p class="text-xs font-medium text-amber-600">Active Queue</p>
                <p class="text-2xl font-semibold font-mono text-zinc-900 mt-1">{{ $stats['open'] }}</p>
            </div>
            <div class="p-4">
                <p class="text-xs font-medium text-emerald-600">Resolved</p>
                <p class="text-2xl font-semibold font-mono text-zinc-900 mt-1">{{ $stats['resolved'] }}</p>
            </div>
            <div class="p-4">
                <p class="text-xs font-medium text-red-600">SLA Breached</p>
                <p class="text-2xl font-semibold font-mono text-red-600 mt-1">{{ $stats['breached'] }}</p>
            </div>
        </div>

        <!-- Bar Pencarian & Filter + Tabel -->
        <div class="bg-white rounded-xl border border-zinc-200/90 shadow-2xs overflow-hidden">
            <div class="p-4 border-b border-zinc-200/80 bg-zinc-50/40">
                <form method="GET" action="{{ route('tickets.index') }}" class="flex flex-wrap items-center gap-2.5">
                    <div class="flex-1 min-w-[240px]">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari ID tiket (TKT-...) atau subjek insiden..."
                            class="w-full text-xs rounded-md border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900">
                    </div>
                    <select name="status"
                        class="text-xs rounded-md border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900">
                        <option value="">Semua Status</option>
                        @foreach (\App\Enums\TicketStatus::cases() as $statusOption)
                            <option value="{{ $statusOption->value }}" @selected(request('status') === $statusOption->value)>
                                {{ $statusOption->label() }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit"
                        class="px-3.5 py-2 bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium rounded-md transition">
                        Filter
                    </button>
                    @if (request()->hasAny(['search', 'status']))
                        <a href="{{ route('tickets.index') }}"
                            class="px-2.5 py-2 text-xs text-zinc-500 hover:text-zinc-900">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-zinc-200/70 bg-zinc-50/60 text-[11px] font-medium text-zinc-500">
                            <th class="py-2.5 px-4">ID</th>
                            <th class="py-2.5 px-4">Subject & Reporter</th>
                            <th class="py-2.5 px-4">Priority</th>
                            <th class="py-2.5 px-4">Resolution Deadline</th>
                            <th class="py-2.5 px-4">SLA Health</th>
                            <th class="py-2.5 px-4">Status</th>
                            <th class="py-2.5 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200/60 text-xs">
                        @forelse($tickets as $ticket)
                            @php
                                $isPaused = $ticket->sla_paused_at !== null;
                                $isCompleted = in_array(
                                    $ticket->status,
                                    [\App\Enums\TicketStatus::RESOLVED, \App\Enums\TicketStatus::CLOSED],
                                    true,
                                );
                                $isOverdue =
                                    $ticket->is_sla_breached ||
                                    (!$isPaused && !$isCompleted && now()->greaterThan($ticket->sla_deadline_at));
                            @endphp
                            <tr class="hover:bg-zinc-50/80 transition">
                                <td class="py-3.5 px-4 font-mono text-[11px] font-medium text-zinc-500">
                                    <a href="{{ route('tickets.show', $ticket) }}"
                                        class="hover:text-zinc-900 hover:underline">
                                        {{ $ticket->ticket_number }}
                                    </a>
                                </td>
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('tickets.show', $ticket) }}"
                                        class="font-medium text-zinc-900 hover:text-indigo-600 block">
                                        {{ $ticket->title }}
                                    </a>
                                    <span class="text-[11px] text-zinc-400">
                                        Reporter: {{ $ticket->creator->name }} • Assignee:
                                        {{ $ticket->assignee->name ?? 'Unassigned' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span
                                        class="px-2 py-0.5 text-[11px] font-medium rounded border {{ $ticket->slaPolicy->priority->badgeColor() }}">
                                        {{ $ticket->slaPolicy->priority->label() }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-[11px]">
                                    <span
                                        class="text-zinc-800 block">{{ $ticket->sla_deadline_at->format('d M Y, H:i') }}</span>
                                    <span class="text-zinc-400 text-[10px]">
                                        {{ $isCompleted ? 'Completed' : $ticket->sla_deadline_at->diffForHumans() }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center space-x-1.5">
                                        @if ($isPaused)
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            <span class="font-mono text-[11px] text-amber-700">Paused</span>
                                        @elseif($isOverdue)
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                            <span class="font-mono text-[11px] text-red-600 font-medium">Breached</span>
                                        @elseif($isCompleted)
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span class="font-mono text-[11px] text-emerald-700">Met SLA</span>
                                        @else
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span class="font-mono text-[11px] text-zinc-700">On Track</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span
                                        class="px-2 py-0.5 text-[11px] font-medium rounded border {{ $ticket->status->badgeColor() }}">
                                        {{ $ticket->status->label() }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ route('tickets.show', $ticket) }}"
                                        class="px-2.5 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-800 text-[11px] font-medium rounded-md transition">
                                        Inspect →
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-xs text-zinc-400">
                                    Tidak ada insiden yang sesuai dengan filter pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($tickets->hasPages())
                <div class="p-4 border-t border-zinc-200/70">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
