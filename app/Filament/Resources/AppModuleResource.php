<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppModuleResource\Pages;
use App\Models\AppModule;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AppModuleResource extends Resource
{
    protected static ?string $model = AppModule::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Apps';

    protected static ?string $modelLabel = 'App';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true)
                ->helperText('Doit correspondre au slug du module.json (ex: weed-count)'),
            Forms\Components\TextInput::make('icon')->helperText('nom d\'icone (heroicon ou tabler)'),
            Forms\Components\Textarea::make('description')->rows(2),
            Forms\Components\Toggle::make('is_active')->label('Active (visible dans la plateforme)')->default(true),

            Forms\Components\Section::make('Points attribués par cette app')
                ->description('Surcharge la config par défaut du module (module.json). Laisse vide pour garder les valeurs par défaut.')
                ->schema([
                    Forms\Components\KeyValue::make('config.points')
                        ->label('Événement => valeur en points')
                        ->keyLabel('Événement')
                        ->valueLabel('Points'),
                ]),

            Forms\Components\Section::make('Accès utilisateurs')
                ->schema([
                    Forms\Components\Select::make('users')
                        ->relationship('users', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->label('Utilisateurs autorisés'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('slug')->badge(),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
                Tables\Columns\TextColumn::make('users_count')->counts('users')->label('Utilisateurs autorisés'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppModules::route('/'),
            'edit' => Pages\EditAppModule::route('/{record}/edit'),
        ];
    }
}
