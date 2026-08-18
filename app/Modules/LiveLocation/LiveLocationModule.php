<?php

namespace App\Modules\LiveLocation;

use App\Modules\LiveLocation\Models\LocationShare;
use App\Models\User;
use App\Support\Modules\ModuleContract;
use Carbon\Carbon;

class LiveLocationModule implements ModuleContract
{
    public function slug(): string
    {
        return 'live-location';
    }

    public function name(): string
    {
        return 'Position en direct';
    }

    public function icon(): string
    {
        return 'map-pin';
    }

    public function entryRoute(): string
    {
        return 'apps.live-location.index';
    }

    public function defaultPointsConfig(): array
    {
        return [
            'share_enabled' => 0,
        ];
    }

    /**
     * Pas de journal d'evenements pour ce module (par design: on ne conserve pas
     * d'historique de deplacement, seulement la derniere position connue).
     * La "stat" renvoyee est le nombre de personnes actuellement autorisees a voir
     * la position de l'utilisateur, ce qui reste pertinent dans le widget generaliste.
     */
    public function getStats(User $user, string $period, \DateTimeInterface $reference): array
    {
        $activeShares = LocationShare::where('user_id', $user->id)->where('is_active', true)->count();

        return [
            'period' => $period,
            'total' => $activeShares,
            'average' => $activeShares,
            'average_label' => 'Personnes autorisées à voir ma position',
        ];
    }
}
