<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RewardResource\Pages;
use App\Models\Reward;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RewardResource extends Resource
{
    protected static ?string $model = Reward::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationLabel = 'Récompenses';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\Textarea::make('description')->rows(2),
            Forms\Components\TextInput::make('points_cost')->numeric()->required()->label('Coût en points'),
            Forms\Components\Select::make('type')->options([
                'item' => 'Objet physique',
                'perk' => 'Avantage / privilège',
                'custom' => 'Autre',
            ])->default('custom')->required(),
            Forms\Components\TextInput::make('stock')->numeric()->nullable()
                ->helperText('Laisser vide = stock illimité'),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('points_cost')->label('Coût')->sortable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('stock')->placeholder('Illimité'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRewards::route('/'),
            'create' => Pages\CreateReward::route('/create'),
            'edit' => Pages\EditReward::route('/{record}/edit'),
        ];
    }
}
