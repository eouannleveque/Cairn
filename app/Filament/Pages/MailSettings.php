<?php

namespace App\Filament\Pages;

use App\Models\MailSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class MailSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Configuration mail';

    protected static string $view = 'filament.pages.mail-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $setting = MailSetting::first();
        $this->form->fill($setting?->toArray() ?? []);
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('driver')->options(['smtp' => 'SMTP'])->default('smtp')->required(),
            Forms\Components\TextInput::make('host')->required(),
            Forms\Components\TextInput::make('port')->numeric()->required(),
            Forms\Components\TextInput::make('username'),
            Forms\Components\TextInput::make('password')->password()->revealable()
                ->dehydrated(fn ($state) => filled($state)) // ne pas ecraser si laisse vide
                ->helperText('Laisser vide pour conserver le mot de passe actuel'),
            Forms\Components\Select::make('encryption')->options([
                'tls' => 'TLS', 'ssl' => 'SSL', '' => 'Aucun',
            ]),
            Forms\Components\TextInput::make('from_address')->email()->required()->label('Adresse d\'expédition'),
            Forms\Components\TextInput::make('from_name')->required()->label('Nom d\'expédition'),
        ])->statePath('data');
    }

    public function save(): void
    {
        $setting = MailSetting::first() ?? new MailSetting();
        $setting->fill($this->form->getState());
        $setting->save();

        Notification::make()->title('Configuration mail enregistrée')->success()->send();
    }
}
