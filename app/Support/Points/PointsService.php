<?php

namespace App\Support\Points;

use App\Models\AppModule;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PointsService
{
    /**
     * Credite/debite des points a un user, log dans points_ledger, et met a jour le solde denormalise.
     * $delta positif = gain, negatif = depense (ex: reward).
     */
    public function apply(User $user, int $delta, string $reason, ?AppModule $app = null, array $meta = []): void
    {
        DB::transaction(function () use ($user, $delta, $reason, $app, $meta) {
            $user->pointsLedger()->create([
                'app_id' => $app?->id,
                'delta' => $delta,
                'reason' => $reason,
                'meta' => $meta,
            ]);

            $user->increment('points_balance', $delta);
        });
    }

    /**
     * Recupere la valeur en points configuree pour un evenement d'une app (fallback sur defaultPointsConfig).
     */
    public function valueFor(AppModule $appModule, string $event): int
    {
        $configured = $appModule->config['points'][$event] ?? null;

        if ($configured !== null) {
            return (int) $configured;
        }

        return (int) ($appModule->moduleInstance()?->defaultPointsConfig()[$event] ?? 0);
    }

    public function grantForEvent(User $user, string $appSlug, string $event, array $meta = []): void
    {
        $appModule = AppModule::where('slug', $appSlug)->first();

        if (! $appModule) {
            return;
        }

        $value = $this->valueFor($appModule, $event);

        if ($value !== 0) {
            $this->apply($user, $value, $event, $appModule, $meta);
        }
    }
}
