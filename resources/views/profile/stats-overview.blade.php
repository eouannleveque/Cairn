<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold">Mes statistiques</h2>
        <div class="flex gap-2">
            @foreach (['daily' => 'Jour', 'weekly' => 'Semaine', 'monthly' => 'Mois', 'yearly' => 'Année'] as $key => $label)
                <button wire:click="setPeriod('{{ $key }}')"
                        class="px-3 py-1 rounded-full text-xs {{ $period === $key ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @forelse ($statsByApp as $entry)
            <div class="rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-medium">{{ $entry['app']->name }}</span>
                    <a href="{{ route($entry['app']->moduleInstance()->entryRoute()) }}" class="text-xs underline">Ouvrir</a>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-bold">{{ $entry['stats']['total'] }}</span>
                    <span class="text-xs text-gray-500">{{ $entry['stats']['average_label'] }} : {{ $entry['stats']['average'] }}</span>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400">Aucune app active pour l'instant. Demande l'accès à un admin.</p>
        @endforelse
    </div>
</div>
