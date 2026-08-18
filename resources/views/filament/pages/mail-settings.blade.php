<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="[
                \Filament\Actions\Action::make('save')->label('Enregistrer')->submit('save'),
            ]"
        />
    </form>
</x-filament-panels::page>
