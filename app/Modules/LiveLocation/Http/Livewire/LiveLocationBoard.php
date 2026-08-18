<?php

namespace App\Modules\LiveLocation\Http\Livewire;

use App\Models\User;
use App\Modules\LiveLocation\Models\LiveLocation;
use App\Modules\LiveLocation\Models\LocationShare;
use App\Support\Points\PointsService;
use Livewire\Component;

class LiveLocationBoard extends Component
{
    public bool $sharingEnabled = false;
    public string $searchTerm = '';
    public ?string $lastError = null;

    /**
     * Duree au dela de laquelle une position n'est plus consideree comme "en direct"
     * (l'utilisateur a ferme l'onglet, perdu le reseau, etc.). Purement visuel,
     * la ligne n'est jamais supprimee automatiquement de la DB.
     */
    protected int $staleAfterSeconds = 120;

    public function mount(): void
    {
        $this->sharingEnabled = LiveLocation::where('user_id', auth()->id())
            ->where('recorded_at', '>=', now()->subSeconds($this->staleAfterSeconds))
            ->exists();
    }

    /** Activer/desactiver le partage. La desactivation ne supprime pas l'historique des autorisations. */
    public function toggleSharing(): void
    {
        $this->sharingEnabled = ! $this->sharingEnabled;

        if (! $this->sharingEnabled) {
            // On efface la derniere position connue pour ne pas laisser un point "fantome" visible.
            LiveLocation::where('user_id', auth()->id())->delete();
        } else {
            app(PointsService::class)->grantForEvent(auth()->user(), 'live-location', 'share_enabled');
        }
    }

    /** Appelee depuis le JS (navigator.geolocation.watchPosition) tant que sharingEnabled est actif. */
    public function updatePosition(float $lat, float $lng, ?int $accuracy = null): void
    {
        if (! $this->sharingEnabled) {
            return;
        }

        LiveLocation::updateOrCreate(
            ['user_id' => auth()->id()],
            ['latitude' => $lat, 'longitude' => $lng, 'accuracy' => $accuracy, 'recorded_at' => now()]
        );
    }

    public function reportError(string $message): void
    {
        $this->lastError = $message;
        $this->sharingEnabled = false;
    }

    /** Utilisateurs internes que je peux ajouter à la liste des destinataires autorisés. */
    public function getSearchableUsersProperty()
    {
        return User::where('id', '!=', auth()->id())
            ->when($this->searchTerm, fn ($q) => $q->where('name', 'like', "%{$this->searchTerm}%"))
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    /** Liste des autorisations que j'ai données (actives ou non). */
    public function getMySharesProperty()
    {
        return LocationShare::with('viewer')->where('user_id', auth()->id())->get();
    }

    public function grantAccess(int $userId): void
    {
        LocationShare::updateOrCreate(
            ['user_id' => auth()->id(), 'shared_with_id' => $userId],
            ['is_active' => true]
        );
    }

    public function revokeAccess(int $shareId): void
    {
        $share = LocationShare::where('user_id', auth()->id())->findOrFail($shareId);
        $share->update(['is_active' => false]);
    }

    public function reactivateAccess(int $shareId): void
    {
        $share = LocationShare::where('user_id', auth()->id())->findOrFail($shareId);
        $share->update(['is_active' => true]);
    }

    /**
     * Positions des utilisateurs qui m'ont autorisé à les voir (partage actif),
     * avec leur dernière position connue si disponible.
     */
    public function getVisiblePositionsProperty()
    {
        $sharerIds = LocationShare::where('shared_with_id', auth()->id())
            ->where('is_active', true)
            ->pluck('user_id');

        return LiveLocation::with('user')
            ->whereIn('user_id', $sharerIds)
            ->get()
            ->map(fn ($loc) => [
                'user' => $loc->user,
                'lat' => (float) $loc->latitude,
                'lng' => (float) $loc->longitude,
                'accuracy' => $loc->accuracy,
                'is_stale' => $loc->isStale($this->staleAfterSeconds),
                'recorded_at' => $loc->recorded_at,
            ]);
    }

    public function render()
    {
        return view('live-location::board');
    }
}
