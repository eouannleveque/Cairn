@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6 space-y-6" x-data="{}" x-init="
    let chart = null;
    const renderChart = () => {
        const canvas = document.getElementById('weedChart');
        if (!canvas) return;
        if (chart) chart.destroy();
        const data = @js($stats);
        chart = new Chart(canvas, {
            type: $wire.chartType === 'pie' ? 'pie' : ($wire.chartType === 'line' ? 'line' : 'bar'),
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Joints',
                    data: data.series,
                    backgroundColor: 'rgba(99,102,241,0.6)',
                    borderColor: 'rgba(99,102,241,1)',
                    fill: $wire.chartType === 'line',
                }]
            },
            options: { responsive: true, plugins: { legend: { display: $wire.chartType === 'pie' } } }
        });
    };
    renderChart();
    Livewire.hook('morph.updated', () => renderChart());
">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">📊 Statistiques — Weed Count</h1>
        <a href="{{ route('apps.weed-count.index') }}" class="text-sm underline">Retour</a>
    </div>

    {{-- Sélecteur de période --}}
    <div class="flex gap-2">
        @foreach (['daily' => 'Jour', 'weekly' => 'Semaine', 'monthly' => 'Mois', 'yearly' => 'Année'] as $key => $label)
            <button wire:click="setPeriod('{{ $key }}')"
                    class="px-3 py-1.5 rounded-full text-sm {{ $period === $key ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Navigation temporelle + type de graph --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <button wire:click="shift(-1)" class="text-sm underline">← Précédent</button>
            <span class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($reference)->translatedFormat('d F Y') }}</span>
            <button wire:click="shift(1)" class="text-sm underline">Suivant →</button>
        </div>
        <div class="flex gap-1">
            @foreach (['bar' => '▦', 'line' => '📈', 'pie' => '◔'] as $type => $icon)
                <button wire:click="setChartType('{{ $type }}')"
                        class="px-2 py-1 rounded {{ $chartType === $type ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700' }}">
                    {{ $icon }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Totaux --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="rounded-xl p-4 bg-gray-100 dark:bg-gray-800 text-center">
            <div class="text-3xl font-bold">{{ $stats['total'] }}</div>
            <div class="text-xs text-gray-500 uppercase">Total sur la période</div>
        </div>
        <div class="rounded-xl p-4 bg-gray-100 dark:bg-gray-800 text-center">
            <div class="text-3xl font-bold">{{ $stats['average'] }}</div>
            <div class="text-xs text-gray-500 uppercase">{{ $stats['average_label'] }}</div>
        </div>
    </div>

    {{-- Graph --}}
    <div class="rounded-xl p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
        <canvas id="weedChart" height="120"></canvas>
    </div>

    {{-- Liste chronologique (uniquement en vue journalière) --}}
    @if ($period === 'daily')
        <div>
            <h2 class="font-semibold mb-2">Détail de la journée</h2>
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($stats['items'] as $item)
                    <li class="py-2 flex justify-between text-sm">
                        <span>{{ $item['time'] }}</span>
                        <span class="text-gray-400">{{ $item['source'] }}</span>
                    </li>
                @empty
                    <li class="py-4 text-gray-400 text-sm">Aucun joint ce jour-là.</li>
                @endforelse
            </ul>
        </div>
    @endif

</div>

@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endpush
@endonce
@endsection
