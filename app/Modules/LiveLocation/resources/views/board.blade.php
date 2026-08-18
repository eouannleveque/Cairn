@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-6 space-y-6"
     x-data="liveLocation(@js($sharingEnabled))"
     x-init="init()">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">📍 Position en direct</h1>
    </div>

    <p class="text-sm text-gray-500">
        Ta position n'est jamais visible par défaut : elle n'est envoyée qu'aux personnes que tu
        autorises explicitement ci-dessous, et seulement tant que le partage est actif.
    </p>

    {{-- Activation du partage --}}
    <div class="rounded-xl p-5 border border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <div>
            <p class="font-medium">Partager ma position</p>
            <p class="text-xs text-gray-500" x-show="!sharing">Désactivé — personne ne peut te voir.</p>
            <p class="text-xs text-green-600" x-show="sharing">Actif — visible par les personnes autorisées.</p>
        </div>
        <button @click="toggle()"
                class="relative w-14 h-8 rounded-full transition"
                :class="sharing ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'">
            <span class="absolute top-1 left-1 w-6 h-6 bg-white rounded-full transition"
                  :class="sharing ? 'translate-x-6' : ''"></span>
        </button>
    </div>

    @if ($lastError)
        <p class="text-sm text-red-500">{{ $lastError }}</p>
    @endif

    {{-- Gestion des autorisations --}}
    <div class="rounded-xl p-5 border border-gray-200 dark:border-gray-700 space-y-3">
        <h2 class="font-semibold text-sm">Qui peut voir ma position</h2>

        <input type="text" wire:model.live.debounce.300ms="searchTerm"
               placeholder="Rechercher un utilisateur à autoriser..." class="w-full rounded border-gray-300">

        @if ($searchTerm)
            <div class="border rounded max-h-32 overflow-y-auto">
                @foreach ($this->searchableUsers as $u)
                    <div class="px-2 py-1 text-sm flex items-center justify-between hover:bg-gray-100 dark:hover:bg-gray-700">
                        {{ $u->name }}
                        <button wire:click="grantAccess({{ $u->id }})" class="text-xs underline text-green-600">Autoriser</button>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($this->myShares as $share)
                <div class="py-2 flex items-center justify-between text-sm">
                    <span>{{ $share->viewer->name }}</span>
                    @if ($share->is_active)
                        <button wire:click="revokeAccess({{ $share->id }})" class="text-xs underline text-red-500">Révoquer</button>
                    @else
                        <span class="flex items-center gap-2">
                            <span class="text-xs text-gray-400">Révoqué</span>
                            <button wire:click="reactivateAccess({{ $share->id }})" class="text-xs underline text-green-600">Réautoriser</button>
                        </span>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-400 py-2">Tu n'as autorisé personne pour l'instant.</p>
            @endforelse
        </div>
    </div>

    {{-- Carte des positions reçues --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden" wire:poll.10s>
        <div class="p-3 border-b border-gray-200 dark:border-gray-700 text-sm font-semibold">
            Positions partagées avec moi
        </div>
        <div id="liveMap" style="height: 400px;"></div>
        <div class="p-3 space-y-1">
            @forelse ($this->visiblePositions as $pos)
                <div class="text-xs flex items-center justify-between">
                    <span>{{ $pos['user']->name }}</span>
                    <span class="{{ $pos['is_stale'] ? 'text-amber-500' : 'text-green-600' }}">
                        {{ $pos['is_stale'] ? 'Position ancienne' : 'En direct' }}
                        — {{ $pos['recorded_at']->diffForHumans() }}
                    </span>
                </div>
            @empty
                <p class="text-xs text-gray-400">Personne ne partage sa position avec toi pour l'instant.</p>
            @endforelse
        </div>
    </div>

</div>

@once
    @push('scripts')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
        <script>
            function liveLocation(initialSharing) {
                return {
                    sharing: initialSharing,
                    watchId: null,
                    map: null,
                    markers: {},

                    init() {
                        this.map = L.map('liveMap').setView([46.6, 2.2], 5); // centre France par defaut
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap contributors'
                        }).addTo(this.map);

                        this.renderPositions(@js($this->visiblePositions->values()));

                        Livewire.on('theme-updated', () => {}); // noop, evite warning si event global present

                        if (this.sharing) this.startWatching();

                        // Re-render la carte a chaque poll (positions rafraichies par Livewire)
                        Livewire.hook('morph.updated', () => {
                            this.renderPositions(@js($this->visiblePositions->values()));
                        });
                    },

                    toggle() {
                        this.sharing = !this.sharing;
                        $wire.toggleSharing();
                        if (this.sharing) {
                            this.startWatching();
                        } else {
                            this.stopWatching();
                        }
                    },

                    startWatching() {
                        if (!navigator.geolocation) {
                            $wire.reportError("Ton navigateur ne supporte pas la géolocalisation.");
                            this.sharing = false;
                            return;
                        }

                        this.watchId = navigator.geolocation.watchPosition(
                            (pos) => {
                                $wire.updatePosition(
                                    pos.coords.latitude,
                                    pos.coords.longitude,
                                    Math.round(pos.coords.accuracy)
                                );
                            },
                            (err) => {
                                $wire.reportError("Géolocalisation refusée ou indisponible : " + err.message);
                                this.sharing = false;
                                this.stopWatching();
                            },
                            { enableHighAccuracy: true, maximumAge: 10000, timeout: 15000 }
                        );
                    },

                    stopWatching() {
                        if (this.watchId !== null) {
                            navigator.geolocation.clearWatch(this.watchId);
                            this.watchId = null;
                        }
                    },

                    renderPositions(positions) {
                        // Nettoie les anciens marqueurs
                        Object.values(this.markers).forEach(m => this.map.removeLayer(m));
                        this.markers = {};

                        if (!positions.length) return;

                        positions.forEach(p => {
                            const marker = L.marker([p.lat, p.lng])
                                .addTo(this.map)
                                .bindPopup(p.user.name + (p.is_stale ? ' (position ancienne)' : ' (en direct)'));
                            this.markers[p.user.id] = marker;
                        });

                        const group = new L.featureGroup(Object.values(this.markers));
                        this.map.fitBounds(group.getBounds().pad(0.3));
                    },
                }
            }
        </script>
    @endpush
@endonce
@endsection
