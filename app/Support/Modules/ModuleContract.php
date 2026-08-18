<?php

namespace App\Support\Modules;

use App\Models\User;

interface ModuleContract
{
    /** Slug unique, doit matcher la colonne `apps.slug` */
    public function slug(): string;

    public function name(): string;

    public function icon(): string;

    /** Route (name) vers l'ecran principal du module */
    public function entryRoute(): string;

    /**
     * Liste des evenements qui rapportent des points, avec leur valeur par defaut.
     * Ex: ['purchase_logged' => 2, 'daily_streak' => 5]
     * Les valeurs reelles utilisees sont celles stockees dans apps.config (modifiables en admin),
     * ce tableau ne sert que de valeur par defaut / documentation dans l'UI admin.
     */
    public function defaultPointsConfig(): array;

    /**
     * Renvoie les stats agregees pour un user sur une periode donnee.
     * $period = 'daily' | 'weekly' | 'monthly' | 'yearly'
     * Retourne un tableau pret a etre consomme par Chart.js (labels/series) + des totaux/moyennes.
     */
    public function getStats(User $user, string $period, \DateTimeInterface $reference): array;
}
