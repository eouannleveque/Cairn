<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RewardRedemptionResource\Pages;
use App\Models\RewardRedemption;
use App\Support\Points\PointsService;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RewardRedemptionResource extends Resource
{
    protected static ?string $model = RewardRedemption::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationLabel = 'Demandes d\'échange';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Utilisateur'),
                Tables\Columns\TextColumn::make('reward.name')->label('Récompense'),
                Tables\Columns\TextColumn::make('points_spent')->label('Points'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    'pending' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                }),
                Tables\Columns\TextColumn::make('redeemed_at')->dateTime('d/m/Y H:i'),
            ])
            ->defaultSort('redeemed_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending' => 'En attente',
                    'approved' => 'Approuvée',
                    'rejected' => 'Refusée',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approuver')
                    ->color('success')
                    ->visible(fn (RewardRedemption $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(fn (RewardRedemption $record) => static::approve($record)),

                Tables\Actions\Action::make('reject')
                    ->label('Refuser')
                    ->color('danger')
                    ->visible(fn (RewardRedemption $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(fn (RewardRedemption $record) => static::reject($record)),
            ]);
    }

    /**
     * Refus = remboursement des points au user (les points avaient ete debites a la demande).
     */
    protected static function reject(RewardRedemption $record): void
    {
        $record->update(['status' => 'rejected', 'handled_by' => auth()->id()]);

        app(PointsService::class)->apply(
            $record->user,
            $record->points_spent,
            'reward_redemption_rejected',
            null,
            ['redemption_id' => $record->id]
        );

        // TODO: envoyer un mail via TemplatedMail::make('reward_rejected', $record->user, [...])
    }

    protected static function approve(RewardRedemption $record): void
    {
        $record->update(['status' => 'approved', 'handled_by' => auth()->id()]);

        // TODO: envoyer un mail via TemplatedMail::make('reward_approved', $record->user, [...])
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRewardRedemptions::route('/'),
        ];
    }
}
