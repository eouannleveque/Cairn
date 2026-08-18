<div class="space-y-4" x-data x-on:reward-redeemed.window="$dispatch('notify', {message: 'Demande envoyée, en attente de validation admin.'})">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold">Récompenses</h2>
        <span class="text-sm font-medium">Solde : <span class="font-bold">{{ $balance }}</span> pts</span>
    </div>

    @error('points')
        <p class="text-sm text-red-500">{{ $message }}</p>
    @enderror

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @forelse ($rewards as $reward)
            <div class="rounded-xl p-4 border border-gray-200 dark:border-gray-700 flex flex-col justify-between">
                <div>
                    <p class="font-medium">{{ $reward->name }}</p>
                    @if ($reward->description)
                        <p class="text-xs text-gray-500 mt-1">{{ $reward->description }}</p>
                    @endif
                    @if ($reward->stock !== null)
                        <p class="text-xs text-gray-400 mt-1">Stock : {{ $reward->stock }}</p>
                    @endif
                </div>
                <div class="mt-4 flex items-center justify-between">
                    <span class="font-bold">{{ $reward->points_cost }} pts</span>
                    <button wire:click="redeem({{ $reward->id }})"
                            wire:confirm="Échanger {{ $reward->points_cost }} points contre « {{ $reward->name }} » ?"
                            class="btn-primary text-sm"
                            @disabled($balance < $reward->points_cost)>
                        Échanger
                    </button>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400">Aucune récompense disponible pour le moment.</p>
        @endforelse
    </div>
</div>
