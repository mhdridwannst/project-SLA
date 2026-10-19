<x-app-layout>
    <x-slot name="header">
        <span>Members & Roles</span>
    </x-slot>

    <div class="py-6 px-4 sm:px-8 max-w-7xl mx-auto space-y-6">

        @if (session('success'))
            <div
                class="px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-xs font-medium">
                {{ session('success') }}
            </div>
        @endif

        <div>
            <h1 class="text-lg font-semibold text-zinc-900 tracking-tight">Workspace Members & Role-Based Access</h1>
            <p class="text-xs text-zinc-500 mt-0.5">
                Kelola akun Administrator, Support Agent (Teknisi), dan Customer di dalam ruang kerja
                {{ auth()->user()->tenant->name }}.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            @if (auth()->user()->isAdmin())
                <div class="lg:col-span-4 bg-white p-5 rounded-xl border border-zinc-200/90 shadow-2xs h-fit">
                    <h2
                        class="text-xs font-semibold uppercase tracking-wider text-zinc-700 border-b border-zinc-100 pb-3 mb-4">
                        Provision New Member
                    </h2>
                    <form action="{{ route('team.store') }}" method="POST" class="space-y-3.5">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">Nama Lengkap</label>
                            <input name="name" type="text" required placeholder="Contoh: Budi Santoso"
                                class="w-full text-xs rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900">
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">Email Kerja</label>
                            <input name="email" type="email" required placeholder="budi@perusahaan.com"
                                class="w-full text-xs rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900">
                            <x-input-error :messages="$errors->get('email')" class="mt-1" />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">Hak Akses (Role)</label>
                            <select name="role"
                                class="w-full text-xs rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900">
                                @foreach (\App\Enums\UserRole::cases() as $roleCase)
                                    <option value="{{ $roleCase->value }}">{{ $roleCase->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">Password Awal</label>
                            <input name="password" type="password" required placeholder="Minimal 8 karakter"
                                class="w-full text-xs rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900">
                            <x-input-error :messages="$errors->get('password')" class="mt-1" />
                        </div>

                        <button type="submit"
                            class="w-full py-2 px-4 bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium rounded-lg shadow-2xs transition">
                            Tambahkan ke Workspace
                        </button>
                    </form>
                </div>
            @endif

            <div
                class="{{ auth()->user()->isAdmin() ? 'lg:col-span-8' : 'lg:col-span-12' }} bg-white rounded-xl border border-zinc-200/90 shadow-2xs overflow-hidden h-fit">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-zinc-200/70 bg-zinc-50/60 text-[11px] font-medium text-zinc-500">
                            <th class="py-3 px-4">Member Name</th>
                            <th class="py-3 px-4">Email Address</th>
                            <th class="py-3 px-4">Role Access</th>
                            <th class="py-3 px-4 text-right">Joined</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200/60 text-xs">
                        @foreach ($members as $member)
                            <tr class="hover:bg-zinc-50/70">
                                <td class="py-3.5 px-4 font-medium text-zinc-900">{{ $member->name }}</td>
                                <td class="py-3.5 px-4 text-zinc-500 font-mono text-[11px]">{{ $member->email }}</td>
                                <td class="py-3.5 px-4">
                                    <span
                                        class="px-2 py-0.5 text-[11px] font-medium rounded bg-zinc-100 text-zinc-800 border border-zinc-200">
                                        {{ $member->role->label() }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono text-[11px] text-zinc-400">
                                    {{ $member->created_at->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
