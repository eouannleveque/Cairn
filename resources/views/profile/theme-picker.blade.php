<div class="space-y-6">
    <h2 class="text-lg font-semibold">Personnaliser ma page</h2>

    {{-- Presets --}}
    <div>
        <p class="text-sm text-gray-500 mb-2">Palettes suggérées</p>
        <div class="grid grid-cols-4 gap-3">
            @foreach ($presets as $name => [$primary, $secondary, $accent])
                <button wire:click="applyPreset('{{ $name }}')"
                        class="rounded-xl p-2 border-2 {{ $selectedPreset === $name ? 'border-gray-900 dark:border-white' : 'border-transparent' }}">
                    <div class="h-8 rounded-md mb-1" style="background: {{ $primary }}"></div>
                    <div class="flex gap-1 mb-1">
                        <div class="flex-1 h-3 rounded" style="background: {{ $secondary }}"></div>
                        <div class="flex-1 h-3 rounded" style="background: {{ $accent }}"></div>
                    </div>
                    <span class="text-xs">{{ $name }}</span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- Personnalisation fine --}}
    <div class="grid grid-cols-3 gap-4">
        <div>
            <label class="block text-sm mb-1">Couleur principale</label>
            <input type="color" wire:model="primaryColor" class="w-full h-10 rounded">
        </div>
        <div>
            <label class="block text-sm mb-1">Couleur secondaire</label>
            <input type="color" wire:model="secondaryColor" class="w-full h-10 rounded">
        </div>
        <div>
            <label class="block text-sm mb-1">Couleur d'accent</label>
            <input type="color" wire:model="accentColor" class="w-full h-10 rounded">
        </div>
    </div>

    <div>
        <label class="block text-sm mb-1">Mode</label>
        <select wire:model="mode" class="rounded border-gray-300">
            <option value="light">Clair</option>
            <option value="dark">Sombre</option>
        </select>
    </div>

    <button wire:click="save" class="btn-primary">Enregistrer</button>
</div>
