<x-app-layout>
    <x-slot name="header">
        <span>Report New Incident</span>
    </x-slot>

    <div class="py-6 px-4 sm:px-8 max-w-3xl mx-auto">
        <div class="bg-white rounded-xl border border-zinc-200/90 shadow-2xs overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-200/80">
                <h1 class="text-base font-semibold text-zinc-900">Buat Tiket Insiden Operasional</h1>
                <p class="text-xs text-zinc-500 mt-0.5">Arloji SLA untuk First Response dan Resolution akan langsung
                    berdetak begitu tiket diterbitkan.</p>
            </div>

            <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf

                <div>
                    <label for="title" class="block text-xs font-medium text-zinc-700 mb-1">Subjek / Judul
                        Insiden</label>
                    <input id="title" name="title" type="text" value="{{ old('title') }}" required
                        placeholder="Contoh: Callback Pembayaran QRIS Mengembalikan Error 502"
                        class="w-full text-xs rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900">
                    <x-input-error :messages="$errors->get('title')" class="mt-1" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="sla_policy_id" class="block text-xs font-medium text-zinc-700 mb-1">Prioritas &
                            Kebijakan SLA</label>
                        <select id="sla_policy_id" name="sla_policy_id" required
                            class="w-full text-xs rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900">
                            @foreach ($policies as $policy)
                                <option value="{{ $policy->id }}">
                                    [{{ strtoupper($policy->priority->label()) }}] Respon:
                                    {{ $policy->first_response_time_minutes }}m | Resolusi:
                                    {{ $policy->resolution_time_minutes }}m
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if (auth()->user()->isStaff())
                        <div>
                            <label for="assigned_to" class="block text-xs font-medium text-zinc-700 mb-1">Tugaskan
                                Teknisi (PIC)</label>
                            <select id="assigned_to" name="assigned_to"
                                class="w-full text-xs rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900">
                                <option value="">-- Unassigned --</option>
                                @foreach ($agents as $agent)
                                    <option value="{{ $agent->id }}">{{ $agent->name }}
                                        ({{ $agent->role->label() }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                <div>
                    <label for="description" class="block text-xs font-medium text-zinc-700 mb-1">Kronologi & Detail
                        Masalah</label>
                    <textarea id="description" name="description" rows="5" required
                        placeholder="Jelaskan langkah reproduksi masalah, waktu kejadian, dan dampaknya terhadap layanan..."
                        class="w-full text-xs rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900">{{ old('description') }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-1" />
                </div>

                <div>
                    <label for="attachment" class="block text-xs font-medium text-zinc-700 mb-1">Lampiran Bukti
                        Screenshot / Log (Opsional, Maks 5MB)</label>
                    <input id="attachment" name="attachment" type="file"
                        class="w-full text-xs text-zinc-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border file:border-zinc-200 file:text-xs file:font-medium file:bg-zinc-50 file:text-zinc-700 hover:file:bg-zinc-100">
                    <x-input-error :messages="$errors->get('attachment')" class="mt-1" />
                </div>

                <div class="flex justify-end space-x-2.5 pt-4 border-t border-zinc-100">
                    <a href="{{ route('tickets.index') }}"
                        class="px-3.5 py-2 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-md text-xs font-medium">Batal</a>
                    <button type="submit"
                        class="px-4 py-2 bg-zinc-900 hover:bg-zinc-800 text-white rounded-md text-xs font-medium shadow-2xs">
                        Terbitkan Tiket
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
