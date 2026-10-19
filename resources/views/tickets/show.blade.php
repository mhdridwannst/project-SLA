<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-2">
            <a href="{{ route('tickets.index') }}" class="text-zinc-400 hover:text-zinc-800 transition">Incident Queue</a>
            <span class="text-zinc-300">/</span>
            <span class="font-mono text-zinc-900">{{ $ticket->ticket_number }}</span>
        </div>
    </x-slot>

    @php
        $isPaused = $ticket->sla_paused_at !== null;
        $isCompleted = in_array(
            $ticket->status,
            [\App\Enums\TicketStatus::RESOLVED, \App\Enums\TicketStatus::CLOSED],
            true,
        );
        $isOverdue =
            $ticket->is_sla_breached || (!$isPaused && !$isCompleted && now()->greaterThan($ticket->sla_deadline_at));
    @endphp

    <div class="py-6 px-4 sm:px-8 max-w-7xl mx-auto space-y-6">

        @if (session('success'))
            <div
                class="px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-xs font-medium">
                {{ session('success') }}
            </div>
        @endif

        <!-- Top Incident Header Bar -->
        <div
            class="bg-white rounded-xl border border-zinc-200/90 shadow-2xs p-5 flex flex-wrap items-start justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-zinc-100 text-zinc-800 border border-zinc-200">
                        {{ $ticket->ticket_number }}
                    </span>
                    <span
                        class="px-2 py-0.5 text-[11px] font-medium rounded border {{ $ticket->status->badgeColor() }}">
                        {{ $ticket->status->label() }}
                    </span>
                    <span
                        class="px-2 py-0.5 text-[11px] font-medium rounded border {{ $ticket->slaPolicy->priority->badgeColor() }}">
                        {{ $ticket->slaPolicy->priority->label() }} Priority
                    </span>
                </div>
                <h1 class="text-base font-semibold text-zinc-900 tracking-tight pt-1">
                    {{ $ticket->title }}
                </h1>
                <p class="text-xs text-zinc-500">
                    Dilaporkan oleh <span class="font-medium text-zinc-700">{{ $ticket->creator->name }}</span> pada
                    {{ $ticket->created_at->format('d M Y, H:i') }} ({{ $ticket->created_at->diffForHumans() }})
                </p>
            </div>

            <div class="flex items-center space-x-2">
                @if ($isPaused)
                    <span
                        class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-md text-xs font-mono font-medium bg-amber-50 text-amber-800 border border-amber-200">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span>SLA PAUSED</span>
                    </span>
                @elseif($isOverdue)
                    <span
                        class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-md text-xs font-mono font-medium bg-red-50 text-red-700 border border-red-200">
                        <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                        <span>SLA BREACHED</span>
                    </span>
                @elseif($isCompleted)
                    <span
                        class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-md text-xs font-mono font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>SLA COMPLIANT</span>
                    </span>
                @else
                    <span
                        class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-md text-xs font-mono font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>ON TRACK ({{ $ticket->sla_deadline_at->diffForHumans(null, true) }} left)</span>
                    </span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- LEFT WORKSPACE (8/12): Description, Attachment, & Discussion Thread -->
            <div class="lg:col-span-8 space-y-6">

                <!-- Initial Report Box -->
                <div class="bg-white rounded-xl border border-zinc-200/90 shadow-2xs p-5 space-y-4">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Deskripsi Insiden Awal</h2>
                    <div
                        class="text-xs text-zinc-800 whitespace-pre-line leading-relaxed bg-zinc-50/70 p-4 rounded-lg border border-zinc-200/60">
                        {{ $ticket->description }}
                    </div>

                    @if ($ticket->attachment_path)
                        <div
                            class="pt-2 flex items-center justify-between p-3 rounded-lg bg-zinc-50 border border-zinc-200 text-xs">
                            <div class="flex items-center space-x-2 truncate">
                                <svg class="w-4 h-4 text-zinc-500 shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                                </svg>
                                <span class="font-medium text-zinc-700 truncate">{{ $ticket->attachment_name }}</span>
                            </div>
                            <a href="{{ asset('storage/' . $ticket->attachment_path) }}" target="_blank"
                                class="px-2.5 py-1 bg-white hover:bg-zinc-100 text-zinc-800 font-medium rounded border border-zinc-200 shadow-2xs shrink-0">
                                Lihat Bukti ↗
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Communication Thread -->
                <div class="bg-white rounded-xl border border-zinc-200/90 shadow-2xs overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-zinc-200/80 flex items-center justify-between">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-700">Communication &
                            Resolution Thread</h3>
                        <span class="text-[11px] font-mono text-zinc-400">{{ $ticket->messages->count() }}
                            messages</span>
                    </div>

                    <div class="p-5 space-y-4">
                        @forelse($ticket->messages as $message)
                            @if (!$message->is_internal || auth()->user()->isStaff())
                                <div
                                    class="p-4 rounded-lg border {{ $message->is_internal ? 'bg-amber-50/60 border-amber-200/90' : 'bg-white border-zinc-200/80 shadow-2xs' }}">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center space-x-2">
                                            <span
                                                class="text-xs font-semibold text-zinc-900">{{ $message->user->name }}</span>
                                            <span
                                                class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-zinc-100 text-zinc-600 border border-zinc-200">
                                                {{ $message->user->role->label() }}
                                            </span>
                                            @if ($message->is_internal)
                                                <span
                                                    class="text-[10px] font-mono font-semibold px-1.5 py-0.5 rounded bg-amber-100 text-amber-900 border border-amber-300">
                                                    INTERNAL NOTE
                                                </span>
                                            @endif
                                        </div>
                                        <span
                                            class="text-[11px] font-mono text-zinc-400">{{ $message->created_at->format('d M Y, H:i') }}</span>
                                    </div>

                                    <p class="text-xs text-zinc-700 whitespace-pre-line leading-relaxed">
                                        {{ $message->body }}</p>

                                    @if ($message->attachment_path)
                                        <div
                                            class="mt-3 pt-2.5 border-t border-zinc-200/60 flex items-center justify-between text-xs">
                                            <span class="text-[11px] font-mono text-zinc-500 truncate">📎
                                                {{ $message->attachment_name }}</span>
                                            <a href="{{ asset('storage/' . $message->attachment_path) }}"
                                                target="_blank"
                                                class="text-[11px] font-medium text-indigo-600 hover:underline">
                                                Buka Lampiran ↗
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @empty
                            <p class="text-xs text-zinc-400 text-center py-6">
                                Belum ada balasan atau diskusi pada tiket ini.
                            </p>
                        @endforelse
                    </div>

                    <!-- Reply Form with Attachment Upload -->
                    <form action="{{ route('tickets.messages.store', $ticket) }}" method="POST"
                        enctype="multipart/form-data" class="p-5 bg-zinc-50/70 border-t border-zinc-200/80 space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1.5">Tambahkan Respon atau Bukti
                                Penanganan</label>
                            <textarea name="body" rows="3" required
                                placeholder="Tulis pembaruan investigasi, solusi teknis, atau pertanyaan konfirmasi..."
                                class="w-full text-xs rounded-lg border-zinc-300 bg-white focus:border-zinc-900 focus:ring-zinc-900"></textarea>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                            <div class="flex flex-wrap items-center gap-4">
                                <label class="text-xs text-zinc-600 flex items-center space-x-1.5 cursor-pointer">
                                    <input type="file" name="attachment"
                                        class="text-[11px] text-zinc-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border file:border-zinc-200 file:text-[11px] file:font-medium file:bg-white file:text-zinc-700 hover:file:bg-zinc-50">
                                </label>

                                @if (auth()->user()->isStaff())
                                    <label
                                        class="inline-flex items-center space-x-1.5 text-xs text-amber-800 font-medium cursor-pointer">
                                        <input type="checkbox" name="is_internal" value="1"
                                            class="rounded border-amber-400 text-amber-600 focus:ring-amber-500">
                                        <span>Internal Note (Hanya dilihat tim)</span>
                                    </label>
                                @endif
                            </div>

                            <button type="submit"
                                class="px-4 py-1.5 bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium rounded-md shadow-2xs transition">
                                Kirim Balasan
                            </button>
                        </div>
                    </form>
                </div>

            </div>

            <!-- RIGHT WORKSPACE (4/12): SLA Telemetry, Controls, & Audit Log -->
            <div class="lg:col-span-4 space-y-6">

                <!-- SLA Telemetry Card -->
                <div class="bg-white rounded-xl border border-zinc-200/90 shadow-2xs p-5 space-y-4">
                    <h3
                        class="text-xs font-semibold uppercase tracking-wider text-zinc-700 border-b border-zinc-100 pb-2.5">
                        SLA Timers & Telemetry
                    </h3>

                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-zinc-500">First Response Target</p>
                                <p class="font-mono font-medium text-zinc-900 mt-0.5">
                                    {{ $ticket->first_response_deadline_at->format('d M Y, H:i') }}</p>
                            </div>
                            @if ($ticket->first_responded_at)
                                <span
                                    class="px-2 py-0.5 text-[10px] font-mono font-medium rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Met at {{ $ticket->first_responded_at->format('H:i') }}
                                </span>
                            @else
                                <span
                                    class="px-2 py-0.5 text-[10px] font-mono font-medium rounded bg-amber-50 text-amber-700 border border-amber-200">
                                    Pending
                                </span>
                            @endif
                        </div>

                        <div class="pt-3 border-t border-zinc-100 flex justify-between items-start">
                            <div>
                                <p class="text-zinc-500">Resolution Deadline</p>
                                <p class="font-mono font-medium text-zinc-900 mt-0.5">
                                    {{ $ticket->sla_deadline_at->format('d M Y, H:i') }}</p>
                            </div>
                            <span class="font-mono text-[11px] text-zinc-500">
                                {{ $ticket->slaPolicy->resolution_time_minutes }}m policy
                            </span>
                        </div>

                        @if ($ticket->total_paused_minutes > 0)
                            <div
                                class="p-2.5 rounded-lg bg-zinc-50 border border-zinc-200/80 text-[11px] text-zinc-600">
                                Arloji SLA telah diundur otomatis selama <strong
                                    class="font-mono text-zinc-900">{{ $ticket->total_paused_minutes }} menit</strong>
                                (akumulasi waktu tunggu pelanggan).
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Operational Controls (Staff Only) -->
                @if (auth()->user()->isStaff())
                    <div class="bg-white rounded-xl border border-zinc-200/90 shadow-2xs p-5 space-y-4">
                        <h3
                            class="text-xs font-semibold uppercase tracking-wider text-zinc-700 border-b border-zinc-100 pb-2.5">
                            Operational Controls
                        </h3>

                        <form action="{{ route('tickets.update-status', $ticket) }}" method="POST"
                            class="space-y-2">
                            @csrf
                            @method('PATCH')
                            <label class="block text-xs font-medium text-zinc-600">Status Transisi</label>
                            <div class="flex space-x-2">
                                <select name="status"
                                    class="w-full text-xs rounded-md border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900">
                                    @foreach (\App\Enums\TicketStatus::cases() as $statusCase)
                                        <option value="{{ $statusCase->value }}" @selected($ticket->status === $statusCase)>
                                            {{ $statusCase->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit"
                                    class="px-3 py-1.5 bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium rounded-md shrink-0">
                                    Update
                                </button>
                            </div>
                            <p class="text-[11px] text-zinc-400">
                                Status <span class="font-medium text-zinc-600">Waiting on Customer</span> otomatis
                                menjeda arloji SLA.
                            </p>
                        </form>

                        <form action="{{ route('tickets.assign', $ticket) }}" method="POST"
                            class="space-y-2 pt-3 border-t border-zinc-100">
                            @csrf
                            @method('PATCH')
                            <label class="block text-xs font-medium text-zinc-600">Assigned Engineer (PIC)</label>
                            <div class="flex space-x-2">
                                <select name="assigned_to"
                                    class="w-full text-xs rounded-md border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900">
                                    <option value="">-- Unassigned --</option>
                                    @foreach ($agents as $agent)
                                        <option value="{{ $agent->id }}" @selected($ticket->assigned_to === $agent->id)>
                                            {{ $agent->name }} ({{ $agent->role->label() }})
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit"
                                    class="px-3 py-1.5 bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium rounded-md shrink-0">
                                    Assign
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                <!-- Audit Trail Card -->
                <div class="bg-white rounded-xl border border-zinc-200/90 shadow-2xs p-5">
                    <h3
                        class="text-xs font-semibold uppercase tracking-wider text-zinc-700 border-b border-zinc-100 pb-2.5 mb-4">
                        Immutable Audit Trail
                    </h3>
                    <ol class="relative border-s border-zinc-200 ms-2 space-y-3.5">
                        @foreach ($ticket->activities as $activity)
                            <li class="ms-3.5">
                                <div
                                    class="absolute w-2 h-2 bg-zinc-400 rounded-full mt-1.5 -start-1 border border-white">
                                </div>
                                <p class="text-xs text-zinc-800 leading-snug">{{ $activity->description }}</p>
                                <p class="text-[10px] font-mono text-zinc-400 mt-0.5">
                                    {{ $activity->user->name ?? 'System' }} •
                                    {{ $activity->created_at->format('d M H:i') }}
                                </p>
                            </li>
                        @endforeach
                    </ol>
                </div>

            </div>

        </div>

    </div>
</x-app-layout>
