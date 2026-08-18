@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto p-6 space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">🌿 Weed Count</h1>
        <a href="{{ route('apps.weed-count.stats') }}" class="text-sm underline">Voir les stats</a>
    </div>

    {{-- Compteur du jour + bouton +1 --}}
    <div class="rounded-2xl p-8 text-center" style="background: var(--theme-color, #6366f1); color: white;">
        <div class="text-6xl font-black">{{ $this->todayCount }}</div>
        <div class="uppercase tracking-wide text-sm opacity-80">joints aujourd'hui</div>

        <button wire:click="addOne"
                class="mt-6 rounded-full w-20 h-20 text-3xl font-bold bg-white/20 hover:bg-white/30 transition mx-auto flex items-center justify-center">
            +1
        </button>
    </div>

    {{-- Actions secondaires --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <button wire:click="openAddBackdated" class="btn-secondary">
            ➕ Ajouter a posteriori
        </button>
        <button wire:click="openPurchaseModal" class="btn-secondary">
            💰 J'ai achete un bout
        </button>
        <a href="{{ route('apps.weed-count.stats') }}" class="btn-secondary text-center">
            📊 Statistiques
        </a>
    </div>

    {{-- Liste des joints du jour, avec edition/suppression --}}
    <div>
        <h2 class="font-semibold mb-2">Aujourd'hui</h2>
        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($this->todayJoints as $joint)
                <li class="py-2 flex items-center justify-between">
                    <span>
                        {{ $joint->smoked_at->format('H:i') }}
                        <span class="text-xs text-gray-400">({{ $joint->source }})</span>
                    </span>
                    <span class="space-x-2">
                        <button wire:click="openEdit({{ $joint->id }})" class="text-sm underline">Modifier</button>
                        <button wire:click="deleteJoint({{ $joint->id }})"
                                wire:confirm="Supprimer ce joint ?"
                                class="text-sm underline text-red-500">Supprimer</button>
                    </span>
                </li>
            @empty
                <li class="py-4 text-gray-400 text-sm">Rien enregistre pour l'instant aujourd'hui.</li>
            @endforelse
        </ul>
    </div>

    {{-- Modal ajout / edition joint --}}
    @if ($showJointModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" wire:click.self="$set('showJointModal', false)">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-sm space-y-4">
                <h3 class="font-semibold">{{ $editingJointId ? 'Modifier le joint' : 'Ajouter un joint a posteriori' }}</h3>
                <div>
                    <label class="block text-sm mb-1">Date</label>
                    <input type="date" wire:model="jointDate" class="w-full rounded border-gray-300">
                </div>
                <div>
                    <label class="block text-sm mb-1">Heure</label>
                    <input type="time" wire:model="jointTime" class="w-full rounded border-gray-300">
                </div>
                <div class="flex justify-end gap-2">
                    <button wire:click="$set('showJointModal', false)" class="btn-secondary">Annuler</button>
                    <button wire:click="saveJoint" class="btn-primary">Enregistrer</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal achat --}}
    @if ($showPurchaseModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" wire:click.self="$set('showPurchaseModal', false)">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-sm space-y-4">
                <h3 class="font-semibold">Nouvel achat</h3>
                <div>
                    <label class="block text-sm mb-1">Nom / description</label>
                    <input type="text" wire:model="purchaseLabel" class="w-full rounded border-gray-300" placeholder="ex: Amnesia">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm mb-1">Poids (g)</label>
                        <input type="number" step="0.01" wire:model="purchaseWeight" class="w-full rounded border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Prix (€)</label>
                        <input type="number" step="0.01" wire:model="purchasePrice" class="w-full rounded border-gray-300">
                    </div>
                </div>
                <div>
                    <label class="block text-sm mb-1">Date d'achat</label>
                    <input type="date" wire:model="purchaseDate" class="w-full rounded border-gray-300">
                </div>
                <div class="flex justify-end gap-2">
                    <button wire:click="$set('showPurchaseModal', false)" class="btn-secondary">Annuler</button>
                    <button wire:click="savePurchase" class="btn-primary">Enregistrer</button>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
