<x-app-layout>
    <x-slot name="header">
        <span>Operations Overview</span>
    </x-slot>

    <div x-data="{ quickTicketModal: false }" class="py-6 px-4 sm:px-8 max-w-7xl mx-auto space-y-6">

        <!-- Top Action & Status Banner -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-zinc-900 tracking-tight">
                    Service Level Agreement & Incident Telemetry
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Pemantauan metrik operasional real-time, distribusi beban teknisi, dan riwayat audit untuk
                    {{ auth()->user()->tenant->name }}.
                </p>
            </div>

            <div class="flex items-center space-x-2.5">
                <a href="{{ route('sla-policies.index') }}"
                    class="px-3 py-1.5 bg-white hover:bg-zinc-50 text-zinc-700 text-xs font-medium rounded-md border border-zinc-200 shadow-2xs transition">
                    Configure SLA Matrix
                </a>
                <button @click="quickTicketModal = true" type="button"
                    class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium rounded-md shadow-xs transition cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Quick Incident Report</span>
                </button>
            </div>
        </div>

        <!-- UNIFIED TELEMETRY STRIP (Gaya Vercel / Linear) -->
        <div
            class="bg-white rounded-xl border border-zinc-200/90 shadow-2xs grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-zinc-200/80 overflow-hidden">

            <!-- KPI 1: SLA Compliance -->
            <div class="p-5 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-zinc-500">SLA Compliance</span>
                    <span
                        class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono font-medium {{ $slaComplianceRate >= 95 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                        Target ≥ 95%
                    </span>
                </div>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span
                        class="text-2xl font-semibold font-mono tracking-tight text-zinc-900">{{ $slaComplianceRate }}%</span>
                    <span class="text-xs text-zinc-400">({{ $onTrackCount }}/{{ $totalTickets }} on track)</span>
                </div>
                <div class="mt-3 w-full bg-zinc-100 h-1.5 rounded-full overflow-hidden">
                    <div class="h-full rounded-full {{ $slaComplianceRate >= 95 ? 'bg-emerald-500' : 'bg-amber-500' }}"
                        style="width: {{ min($slaComplianceRate, 100) }}%"></div>
                </div>
            </div>

            <!-- KPI 2: Mean First Response -->
            <div class="p-5 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-zinc-500">Mean First Response (MTTA)</span>
                    <svg class="w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="mt-3 flex items-baseline space-x-1.5">
                    <span class="text-2xl font-semibold font-mono tracking-tight text-zinc-900">
                        {{ $avgFirstResponseMinutes !== null ? $avgFirstResponseMinutes . 'm' : '0m' }}
                    </span>
                    <span class="text-xs text-zinc-400">avg response</span>
                </div>
                <p class="mt-3 text-[11px] text-zinc-500 truncate">
                    Diukur dari tiket terbit hingga balasan publik pertama
                </p>
            </div>

            <!-- KPI 3: Queue Distribution -->
            <div class="p-5 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-zinc-500">Queue Load</span>
                    <span class="text-[11px] font-mono text-zinc-400">{{ $totalTickets }} total</span>
                </div>
                <div class="mt-3 flex items-baseline space-x-4">
                    <div>
                        <span
                            class="text-2xl font-semibold font-mono tracking-tight text-zinc-900">{{ $activeCount }}</span>
                        <span class="text-xs text-amber-600 font-medium ml-1">Active</span>
                    </div>
                    <div class="h-4 w-px bg-zinc-200"></div>
                    <div>
                        <span
                            class="text-2xl font-semibold font-mono tracking-tight text-zinc-900">{{ $resolvedCount }}</span>
                        <span class="text-xs text-emerald-600 font-medium ml-1">Resolved</span>
                    </div>
                </div>
                <p class="mt-3 text-[11px] text-zinc-500">
                    Distribusi tiket aktif vs terselesaikan
                </p>
            </div>

            <!-- KPI 4: SLA Breaches -->
            <div class="p-5 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-zinc-500">SLA Breaches</span>
                    <span
                        class="w-2 h-2 rounded-full {{ $breachedCount > 0 ? 'bg-red-500 animate-ping' : 'bg-zinc-300' }}"></span>
                </div>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span
                        class="text-2xl font-semibold font-mono tracking-tight {{ $breachedCount > 0 ? 'text-red-600' : 'text-zinc-900' }}">
                        {{ $breachedCount }}
                    </span>
                    <span class="text-xs text-zinc-400">incidents breached</span>
                </div>
                <p class="mt-3 text-[11px] text-zinc-500">
                    Dipantau otomatis setiap menit oleh <code
                        class="font-mono text-[10px] bg-zinc-100 px-1 py-0.5 rounded">sla:check</code>
                </p>
            </div>

        </div>

        <!-- MAIN 2-COLUMN WORKSPACE GRID -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- LEFT COLUMN (8/12): Recent Incident Queue Table -->
            <div
                class="lg:col-span-8 bg-white rounded-xl border border-zinc-200/90 shadow-2xs overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="px-5 py-4 border-b border-zinc-200/80 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <h2 class="text-xs font-semibold uppercase tracking-wider text-zinc-700">Recent Incident
                                Stream</h2>
                            <span
                                class="text-[11px] font-mono px-1.5 py-0.5 rounded bg-zinc-100 text-zinc-600">{{ $recentTickets->count() }}</span>
                        </div>
                        <a href="{{ route('tickets.index') }}"
                            class="text-xs font-medium text-zinc-600 hover:text-zinc-900 flex items-center space-x-1">
                            <span>Open Full Queue</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr
                                    class="border-b border-zinc-200/70 bg-zinc-50/60 text-[11px] font-medium text-zinc-500">
                                    <th class="py-2.5 px-4">ID</th>
                                    <th class="py-2.5 px-4">Subject & Reporter</th>
                                    <th class="py-2.5 px-4">Priority</th>
                                    <th class="py-2.5 px-4">SLA Timer</th>
                                    <th class="py-2.5 px-4">Status</th>
                                    <th class="py-2.5 px-4 text-right">Assignee</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200/60 text-xs">
                                @forelse($recentTickets as $ticket)
                                    @php
                                        $isPaused = $ticket->sla_paused_at !== null;
                                        $isCompleted = in_array(
                                            $ticket->status,
                                            [\App\Enums\TicketStatus::RESOLVED, \App\Enums\TicketStatus::CLOSED],
                                            true,
                                        );
                                        $isOverdue =
                                            $ticket->is_sla_breached ||
                                            (!$isPaused &&
                                                !$isCompleted &&
                                                now()->greaterThan($ticket->sla_deadline_at));
                                    @endphp
                                    <tr class="hover:bg-zinc-50/80 transition group">
                                        <td
                                            class="py-3 px-4 font-mono text-[11px] font-medium text-zinc-500 whitespace-nowrap">
                                            <a href="{{ route('tickets.show', $ticket) }}"
                                                class="hover:text-zinc-900 hover:underline">
                                                {{ $ticket->ticket_number }}
                                            </a>
                                        </td>
                                        <td class="py-3 px-4">
                                            <a href="{{ route('tickets.show', $ticket) }}"
                                                class="font-medium text-zinc-900 group-hover:text-indigo-600 transition block truncate max-w-xs">
                                                {{ $ticket->title }}
                                            </a>
                                            <span class="text-[11px] text-zinc-400">by
                                                {{ $ticket->creator->name }}</span>
                                        </td>
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-0.5 text-[11px] font-medium rounded border {{ $ticket->slaPolicy->priority->badgeColor() }}">
                                                {{ $ticket->slaPolicy->priority->label() }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            <div class="flex items-center space-x-1.5">
                                                @if ($isPaused)
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                    <span class="font-mono text-[11px] text-amber-700">Paused</span>
                                                @elseif($isOverdue)
                                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                                    <span
                                                        class="font-mono text-[11px] text-red-600 font-medium">Breached</span>
                                                @elseif($isCompleted)
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    <span class="font-mono text-[11px] text-zinc-500">Met SLA</span>
                                                @else
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    <span
                                                        class="font-mono text-[11px] text-zinc-600">{{ $ticket->sla_deadline_at->diffForHumans(null, true) }}
                                                        left</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-0.5 text-[11px] font-medium rounded border {{ $ticket->status->badgeColor() }}">
                                                {{ $ticket->status->label() }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-right whitespace-nowrap">
                                            <span
                                                class="text-xs text-zinc-600">{{ $ticket->assignee->name ?? 'Unassigned' }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-12 text-center">
                                            <p class="text-xs text-zinc-500">Belum ada insiden yang tercatat di
                                                workspace ini.</p>
                                            <button @click="quickTicketModal = true"
                                                class="mt-2 text-xs font-medium text-indigo-600 hover:underline">
                                                + Buat tiket pertama sekarang
                                            </button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer Bar Table -->
                <div
                    class="px-5 py-3 bg-zinc-50/70 border-t border-zinc-200/70 flex items-center justify-between text-[11px] text-zinc-500">
                    <span>Menampilkan {{ $recentTickets->count() }} insiden terbaru di workspace</span>
                    <span class="font-mono">Tenant Isolation: Active (#{{ auth()->user()->tenant_id }})</span>
                </div>
            </div>

            <!-- RIGHT COLUMN (4/12): Team Capacity & Live Audit Feed -->
            <div class="lg:col-span-4 space-y-6">

                <!-- Card 1: Team Capacity & Workload -->
                <div class="bg-white rounded-xl border border-zinc-200/90 shadow-2xs p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-700">Team Capacity</h3>
                        <a href="{{ route('team.index') }}"
                            class="text-[11px] font-medium text-zinc-500 hover:text-zinc-900">Manage →</a>
                    </div>

                    <div class="space-y-3.5">
                        @foreach ($agentWorkloads as $agent)
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center space-x-2">
                                        <div
                                            class="w-6 h-6 rounded-full bg-zinc-100 border border-zinc-300 text-zinc-700 flex items-center justify-center text-[10px] font-semibold">
                                            {{ $agent['initials'] }}
                                        </div>
                                        <div>
                                            <span class="font-medium text-zinc-900">{{ $agent['name'] }}</span>
                                            <span
                                                class="text-[10px] text-zinc-400 block leading-none">{{ $agent['role'] }}</span>
                                        </div>
                                    </div>
                                    <div class="font-mono text-[11px] space-x-1.5">
                                        <span
                                            class="px-1.5 py-0.5 rounded bg-zinc-100 text-zinc-700">{{ $agent['active'] }}
                                            open</span>
                                        <span
                                            class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700">{{ $agent['resolved'] }}
                                            done</span>
                                    </div>
                                </div>
                                <div class="w-full bg-zinc-100 h-1 rounded-full overflow-hidden">
                                    <div class="bg-zinc-800 h-full rounded-full"
                                        style="width: {{ $agent['completion_rate'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Card 2: Live Audit Trail Stream -->
                <div class="bg-white rounded-xl border border-zinc-200/90 shadow-2xs p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-700">Live Audit Stream</h3>
                        <span class="text-[10px] font-mono text-zinc-400">Real-time log</span>
                    </div>

                    <ol class="relative border-s border-zinc-200 ms-2 space-y-3.5">
                        @forelse($recentActivities as $activity)
                            <li class="ms-3.5">
                                <div
                                    class="absolute w-2 h-2 bg-zinc-400 rounded-full mt-1.5 -start-1 border border-white">
                                </div>
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="font-mono font-medium text-indigo-600">
                                        {{ $activity->ticket->ticket_number ?? 'SYSTEM' }}
                                    </span>
                                    <span class="text-zinc-400 font-mono text-[10px]">
                                        {{ $activity->created_at->diffForHumans(null, true) }}
                                    </span>
                                </div>
                                <p class="text-xs text-zinc-700 mt-0.5 leading-snug">
                                    {{ $activity->description }}
                                </p>
                                <p class="text-[10px] text-zinc-400 mt-0.5">
                                    Actor: {{ $activity->user->name ?? 'Automated SLA Worker' }}
                                </p>
                            </li>
                        @empty
                            <li class="ms-3.5 text-xs text-zinc-400 py-2">
                                Belum ada aktivitas tercatat.
                            </li>
                        @endforelse
                    </ol>
                </div>

            </div>

        </div>

        <!-- INTERACTIVE MODAL: Quick Create Incident (Alpine.js) -->
        <div x-show="quickTicketModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs">
            <div @click.outside="quickTicketModal = false" x-transition
                class="bg-white rounded-xl border border-zinc-200 shadow-xl max-w-lg w-full overflow-hidden">
                <div class="px-5 py-4 border-b border-zinc-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-900">Report New Operational Incident</h3>
                        <p class="text-[11px] text-zinc-500">Arloji SLA akan langsung berjalan otomatis setelah tiket
                            diterbitkan.</p>
                    </div>
                    <button @click="quickTicketModal = false" class="text-zinc-400 hover:text-zinc-700">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="{{ route('tickets.store') }}" method="POST" class="p-5 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-zinc-700 mb-1">Judul Insiden</label>
                        <input type="text" name="title" required
                            placeholder="Contoh: Webhook Midtrans gagal memvalidasi signature"
                            class="w-full text-xs rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">Kebijakan Prioritas SLA</label>
                            <select name="sla_policy_id" required
                                class="w-full text-xs rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900">
                                @foreach ($policies as $policy)
                                    <option value="{{ $policy->id }}">
                                        {{ $policy->priority->label() }} (Resolusi:
                                        {{ $policy->resolution_time_minutes }}m)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">Tugaskan PIC (Agent)</label>
                            <select name="assigned_to"
                                class="w-full text-xs rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900">
                                <option value="">-- Unassigned --</option>
                                @foreach ($agents as $agent)
                                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-700 mb-1">Kronologi & Dampak Teknis</label>
                        <textarea name="description" rows="4" required
                            placeholder="Jelaskan detail insiden, endpoint yang terdampak, atau langkah reproduksi..."
                            class="w-full text-xs rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"></textarea>
                    </div>

                    <div class="pt-3 border-t border-zinc-100 flex justify-end space-x-2">
                        <button type="button" @click="quickTicketModal = false"
                            class="px-3 py-1.5 text-xs font-medium text-zinc-600 hover:bg-zinc-100 rounded-md">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-1.5 bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium rounded-md shadow-xs">
                            Terbitkan Insiden
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
