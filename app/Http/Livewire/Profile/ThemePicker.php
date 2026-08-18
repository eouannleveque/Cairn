<?php

namespace App\Http\Livewire\Profile;

use Livewire\Component;

class ThemePicker extends Component
{
    // Palettes proposees par defaut (nom => [primary, secondary, accent])
    public array $presets = [
        'Mousse'   => ['#B9C7A4', '#F5F2E9', '#E8C4A0'],
        'Lavande'  => ['#D8CFEA', '#F5F2E9', '#F0C9A0'],
        'Ciel'     => ['#BFDCE8', '#F5F2E9', '#F2C6B0'],
        'Corail'   => ['#F3C6C0', '#FBEFE3', '#A9C6B8'],
        'Miel'     => ['#F2E1A8', '#F7F2E6', '#C6B8D9'],
        'Menthe'   => ['#B7E0CE', '#F4F1E8', '#F0B8A8'],
        'Prune'    => ['#D9BFC9', '#F1ECE2', '#B9C9A0'],
        'Ocre'     => ['#E8B980', '#F3EFE2', '#A7C4C9'],
    ];

    public string $selectedPreset = 'Mousse';
    public string $primaryColor = '#B9C7A4';
    public string $secondaryColor = '#F5F2E9';
    public string $accentColor = '#E8C4A0';
    public string $mode = 'light'; // light | dark

    public function mount(): void
    {
        $settings = auth()->user()->theme_settings ?? [];

        $this->primaryColor = $settings['primary'] ?? $this->primaryColor;
        $this->secondaryColor = $settings['secondary'] ?? $this->secondaryColor;
        $this->accentColor = $settings['accent'] ?? $this->accentColor;
        $this->mode = $settings['mode'] ?? 'light';
        $this->selectedPreset = $settings['preset'] ?? 'Mousse';
    }

    public function applyPreset(string $name): void
    {
        if (! isset($this->presets[$name])) {
            return;
        }

        [$primary, $secondary, $accent] = $this->presets[$name];

        $this->selectedPreset = $name;
        $this->primaryColor = $primary;
        $this->secondaryColor = $secondary;
        $this->accentColor = $accent;
    }

    public function save(): void
    {
        $this->validate([
            'primaryColor' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondaryColor' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'accentColor' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'mode' => 'required|in:light,dark',
        ]);

        auth()->user()->update([
            'theme_color' => $this->primaryColor,
            'theme_settings' => [
                'preset' => $this->selectedPreset,
                'primary' => $this->primaryColor,
                'secondary' => $this->secondaryColor,
                'accent' => $this->accentColor,
                'mode' => $this->mode,
            ],
        ]);

        $this->dispatch('theme-updated');
    }

    public function render()
    {
        return view('profile.theme-picker');
    }
}
