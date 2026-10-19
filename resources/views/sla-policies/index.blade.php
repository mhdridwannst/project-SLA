<x-app-layout>
    <x-slot name="header">
        <span>SLA Policy Matrix</span>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 gap-4">
                @foreach ($policies as $policy)
                    <div
                        class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        <form action="{{ route('sla-policies.update', $policy) }}" method="POST"
                            class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                            @csrf
                            @method('PATCH')

                            <div class="md:col-span-2">
                                <span
                                    class="inline-block px-3 py-1 text-xs font-bold uppercase rounded border {{ $policy->priority->badgeColor() }}">
                                    {{ $policy->priority->label() }}
                                </span>
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Nama
                                    Kebijakan</label>
                                <input type="text" name="name" value="{{ $policy->name }}"
                                    @disabled(!auth()->user()->isAdmin())
                                    class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Respon
                                    Awal (Menit)</label>
                                <input type="number" name="first_response_time_minutes"
                                    value="{{ $policy->first_response_time_minutes }}" @disabled(!auth()->user()->isAdmin())
                                    class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Target
                                    Selesai (Menit)</label>
                                <input type="number" name="resolution_time_minutes"
                                    value="{{ $policy->resolution_time_minutes }}" @disabled(!auth()->user()->isAdmin())
                                    class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>

                            @if (auth()->user()->isAdmin())
                                <div class="md:col-span-2 text-right">
                                    <button type="submit"
                                        class="w-full py-2 px-4 bg-gray-900 dark:bg-indigo-600 hover:bg-gray-700 text-white text-xs font-semibold rounded-lg">
                                        Simpan Aturan
                                    </button>
                                </div>
                            @endif
                        </form>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>
