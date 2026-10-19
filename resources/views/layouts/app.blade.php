<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-zinc-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SlaGuard') }} — {{ auth()->user()->tenant->name ?? 'Operations' }}</title>

    <!-- Typography: Inter & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="h-full antialiased text-zinc-900 bg-zinc-50/70 selection:bg-zinc-900 selection:text-white">
    @php
        $userInitials = collect(explode(' ', auth()->user()->name))
            ->map(fn($segment) => strtoupper(substr($segment, 0, 1)))
            ->take(2)
            ->join('');
        $unreadCount = auth()->user()->unreadNotifications->count();
    @endphp

    <div class="min-h-screen flex flex-col lg:flex-row">

        <!-- SIDEBAR: Linear/Vercel Inspired Dark Zinc -->
        <aside
            class="w-full lg:w-60 bg-zinc-950 text-zinc-400 flex-shrink-0 flex flex-col justify-between border-r border-zinc-800/80 select-none">
            <div>
                <!-- Workspace Switcher Header -->
                <div class="h-14 px-4 flex items-center justify-between border-b border-zinc-800/80">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5 group">
                        <div
                            class="w-7 h-7 rounded-md bg-white text-zinc-950 flex items-center justify-center font-bold text-xs tracking-tighter shadow-sm group-hover:scale-105 transition">
                            SG
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs font-semibold text-zinc-100 tracking-tight leading-none">
                                {{ auth()->user()->tenant->name }}
                            </span>
                            <span class="text-[10px] text-zinc-500 font-mono mt-0.5">
                                {{ auth()->user()->tenant->slug }}
                            </span>
                        </div>
                    </a>
                    <span
                        class="text-[10px] font-mono uppercase px-1.5 py-0.5 rounded bg-zinc-900 text-zinc-300 border border-zinc-800">
                        {{ auth()->user()->tenant->plan }}
                    </span>
                </div>

                <!-- Navigation Groups -->
                <div class="p-3 space-y-6">
                    <div>
                        <p class="px-2.5 mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">
                            Operations
                        </p>
                        <nav class="space-y-0.5 text-[13px]">
                            <a href="{{ route('dashboard') }}"
                                class="flex items-center space-x-2.5 px-2.5 py-2 rounded-md font-medium transition {{ request()->routeIs('dashboard') ? 'bg-zinc-800/90 text-white shadow-inner' : 'text-zinc-400 hover:bg-zinc-900 hover:text-zinc-200' }}">
                                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('dashboard') ? 'text-emerald-400' : 'text-zinc-500' }}"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                </svg>
                                <span>Overview</span>
                            </a>

                            <a href="{{ route('tickets.index') }}"
                                class="flex items-center justify-between px-2.5 py-2 rounded-md font-medium transition {{ request()->routeIs('tickets.*') ? 'bg-zinc-800/90 text-white shadow-inner' : 'text-zinc-400 hover:bg-zinc-900 hover:text-zinc-200' }}">
                                <div class="flex items-center space-x-2.5">
                                    <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('tickets.*') ? 'text-emerald-400' : 'text-zinc-500' }}"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                                    </svg>
                                    <span>Incident Queue</span>
                                </div>
                                <span
                                    class="text-[11px] font-mono px-1.5 py-0.2 rounded bg-zinc-900 text-zinc-400 border border-zinc-800">
                                    {{ \App\Models\Ticket::whereIn('status', ['open', 'in_progress'])->count() }}
                                </span>
                            </a>
                        </nav>
                    </div>

                    <div>
                        <p class="px-2.5 mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">
                            Workspace Settings
                        </p>
                        <nav class="space-y-0.5 text-[13px]">
                            <a href="{{ route('team.index') }}"
                                class="flex items-center space-x-2.5 px-2.5 py-2 rounded-md font-medium transition {{ request()->routeIs('team.*') ? 'bg-zinc-800/90 text-white shadow-inner' : 'text-zinc-400 hover:bg-zinc-900 hover:text-zinc-200' }}">
                                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('team.*') ? 'text-emerald-400' : 'text-zinc-500' }}"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                </svg>
                                <span>Members & Roles</span>
                            </a>

                            <a href="{{ route('sla-policies.index') }}"
                                class="flex items-center space-x-2.5 px-2.5 py-2 rounded-md font-medium transition {{ request()->routeIs('sla-policies.*') ? 'bg-zinc-800/90 text-white shadow-inner' : 'text-zinc-400 hover:bg-zinc-900 hover:text-zinc-200' }}">
                                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('sla-policies.*') ? 'text-emerald-400' : 'text-zinc-500' }}"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>SLA Policies</span>
                            </a>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Bottom User Profile & System Status -->
            <div class="p-3 border-t border-zinc-800/80 space-y-2">
                <div class="px-2 py-1.5 flex items-center space-x-2 text-[11px] text-zinc-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>SLA Engine Active</span>
                </div>

                <div class="p-2 rounded-lg bg-zinc-900/90 border border-zinc-800/80 flex items-center justify-between">
                    <div class="flex items-center space-x-2.5 min-w-0">
                        <div
                            class="w-7 h-7 rounded-full bg-zinc-800 border border-zinc-700 text-zinc-200 flex items-center justify-center text-xs font-semibold shrink-0">
                            {{ $userInitials }}
                        </div>
                        <div class="truncate">
                            <p class="text-xs font-medium text-zinc-100 truncate leading-tight">
                                {{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-zinc-500 truncate">{{ auth()->user()->role->label() }}</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Sign out"
                            class="p-1.5 text-zinc-400 hover:text-white hover:bg-zinc-800 rounded-md transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- MAIN WORKSPACE AREA -->
        <div class="flex-1 flex flex-col min-w-0">

            <!-- Top Sticky Utility Bar -->
            <header
                class="h-14 bg-white/80 backdrop-blur-md border-b border-zinc-200/80 sticky top-0 z-20 px-4 sm:px-8 flex items-center justify-between">
                <div class="flex items-center space-x-2 text-xs text-zinc-500">
                    <span class="font-medium text-zinc-400">{{ auth()->user()->tenant->name }}</span>
                    <span>/</span>
                    @isset($header)
                        <div class="text-zinc-900 font-semibold">
                            {{ $header }}
                        </div>
                    @endisset
                </div>

                <div class="flex items-center space-x-3">
                    <!-- Interactive Notification Dropdown (Alpine.js) -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                            class="relative p-1.5 rounded-md text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100 transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                            </svg>
                            @if ($unreadCount > 0)
                                <span
                                    class="absolute top-1 right-1 w-2 h-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                            @endif
                        </button>

                        <div x-show="open" @click.outside="open = false" x-cloak x-transition
                            class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-zinc-200 py-2 z-50">
                            <div class="px-3 py-1.5 border-b border-zinc-100 flex justify-between items-center">
                                <span class="text-xs font-semibold text-zinc-900">SLA Escalation Alerts</span>
                                <span class="text-[11px] font-mono text-zinc-500">{{ $unreadCount }} unread</span>
                            </div>
                            <div class="max-h-64 overflow-y-auto divide-y divide-zinc-100">
                                @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
                                    <div class="px-3 py-2.5 hover:bg-zinc-50 text-xs">
                                        <p class="font-medium text-zinc-800">
                                            {{ $notification->data['message'] ?? 'SLA Notification' }}</p>
                                        <p class="text-[11px] text-zinc-400 mt-0.5 font-mono">
                                            {{ $notification->created_at->diffForHumans() }}</p>
                                    </div>
                                @empty
                                    <div class="px-3 py-6 text-center text-xs text-zinc-400">
                                        Tidak ada peringatan pelanggaran SLA baru.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="h-4 w-px bg-zinc-200"></div>

                    <a href="{{ route('tickets.create') }}"
                        class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium rounded-md shadow-sm transition">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>New Incident</span>
                    </a>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1">
                {{ $slot }}
            </main>
        </div>

    </div>
</body>

</html>
