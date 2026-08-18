<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MailTemplateResource\Pages;
use App\Models\MailTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MailTemplateResource extends Resource
{
    protected static ?string $model = MailTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Templates mail';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('key')->required()->unique(ignoreRecord: true)
                ->helperText('Identifiant technique, ex: welcome, reward_approved, reset_password'),
            Forms\Components\TextInput::make('subject')->required()->label('Objet du mail'),
            Forms\Components\Textarea::make('variables')
                ->label('Variables disponibles (une par ligne, ex: user.name)')
                ->formatStateUsing(fn ($state) => is_array($state) ? implode("\n", $state) : $state)
                ->dehydrateStateUsing(fn ($state) => array_filter(array_map('trim', explode("\n", $state ?? '')))),
            Forms\Components\Textarea::make('body_html')
                ->required()
                ->rows(12)
                ->label('Corps du mail (HTML, utiliser {{variable}})')
                ->helperText('Exemple: Bonjour {{user.name}}, vous avez reçu {{points}} points.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')->badge(),
                Tables\Columns\TextColumn::make('subject')->limit(50),
                Tables\Columns\TextColumn::make('updated_at')->dateTime('d/m/Y H:i'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMailTemplates::route('/'),
            'create' => Pages\CreateMailTemplate::route('/create'),
            'edit' => Pages\EditMailTemplate::route('/{record}/edit'),
        ];
    }
}
