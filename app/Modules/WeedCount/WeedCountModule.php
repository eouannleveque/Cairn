<?php

namespace App\Modules\WeedCount;

use App\Modules\WeedCount\Models\WeedJoint;
use App\Modules\WeedCount\Models\WeedPurchase;
use App\Models\User;
use App\Support\Modules\ModuleContract;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class WeedCountModule implements ModuleContract
{
    public function slug(): string
    {
        return 'weed-count';
    }

    public function name(): string
    {
        return 'Weed Count';
    }

    public function icon(): string
    {
        return 'leaf';
    }

    public function entryRoute(): string
    {
        return 'apps.weed-count.index';
    }

    public function defaultPointsConfig(): array
    {
        return [
            'joint_logged' => 0,
            'purchase_logged' => 2,
        ];
    }

    /**
     * Point d'entree generique demande par ModuleContract.
     * Dispatch vers la bonne methode selon la periode.
     */
    public function getStats(User $user, string $period, \DateTimeInterface $reference): array
    {
        $ref = Carbon::instance($reference);

        return match ($period) {
            'daily' => $this->dailyStats($user, $ref),
            'weekly' => $this->weeklyStats($user, $ref),
            'monthly' => $this->monthlyStats($user, $ref),
            'yearly' => $this->yearlyStats($user, $ref),
            default => throw new \InvalidArgumentException("Periode inconnue: {$period}"),
        };
    }

    /**
     * Jour: repartition 00h-23h59 + liste chronologique + moyenne glissante sur 7 jours.
     */
    public function dailyStats(User $user, Carbon $day): array
    {
        $start = $day->copy()->startOfDay();
        $end = $day->copy()->endOfDay();

        $joints = WeedJoint::where('user_id', $user->id)
            ->whereBetween('smoked_at', [$start, $end])
            ->orderBy('smoked_at')
            ->get();

        $byHour = array_fill(0, 24, 0);
        foreach ($joints as $joint) {
            $byHour[(int) $joint->smoked_at->format('G')]++;
        }

        // moyenne glissante sur les 7 derniers jours (dont le jour courant)
        $weekAgo = $start->copy()->subDays(6);
        $rollingTotal = WeedJoint::where('user_id', $user->id)
            ->whereBetween('smoked_at', [$weekAgo, $end])
            ->count();

        return [
            'period' => 'daily',
            'labels' => array_map(fn ($h) => sprintf('%02dh', $h), range(0, 23)),
            'series' => array_values($byHour),
            'items' => $joints->map(fn ($j) => [
                'time' => $j->smoked_at->format('H:i'),
                'source' => $j->source,
            ])->values()->all(),
            'total' => $joints->count(),
            'average' => round($rollingTotal / 7, 2),
            'average_label' => 'Moyenne / jour (7 derniers jours)',
        ];
    }

    /**
     * Semaine: total par jour (lundi-dimanche) + moyenne/jour.
     */
    public function weeklyStats(User $user, Carbon $reference): array
    {
        $start = $reference->copy()->startOfWeek();
        $end = $reference->copy()->endOfWeek();

        $joints = WeedJoint::where('user_id', $user->id)
            ->whereBetween('smoked_at', [$start, $end])
            ->get()
            ->groupBy(fn ($j) => $j->smoked_at->format('Y-m-d'));

        $period = CarbonPeriod::create($start, $end);
        $labels = [];
        $series = [];
        $cumulative = [];
        $running = 0;

        foreach ($period as $date) {
            $count = $joints->get($date->format('Y-m-d'), collect())->count();
            $labels[] = $date->translatedFormat('D d/m');
            $series[] = $count;
            $running += $count;
            $cumulative[] = $running;
        }

        $total = array_sum($series);

        return [
            'period' => 'weekly',
            'labels' => $labels,
            'series' => $series,
            'cumulative' => $cumulative,
            'total' => $total,
            'average' => round($total / 7, 2),
            'average_label' => 'Moyenne / jour',
        ];
    }

    /**
     * Mois: total par jour (format heatmap) + moyenne/jour.
     */
    public function monthlyStats(User $user, Carbon $reference): array
    {
        $start = $reference->copy()->startOfMonth();
        $end = $reference->copy()->endOfMonth();

        $joints = WeedJoint::where('user_id', $user->id)
            ->whereBetween('smoked_at', [$start, $end])
            ->get()
            ->groupBy(fn ($j) => $j->smoked_at->format('Y-m-d'));

        $period = CarbonPeriod::create($start, $end);
        $heatmap = [];
        $labels = [];
        $series = [];

        foreach ($period as $date) {
            $count = $joints->get($date->format('Y-m-d'), collect())->count();
            $heatmap[] = ['date' => $date->format('Y-m-d'), 'count' => $count];
            $labels[] = $date->format('d');
            $series[] = $count;
        }

        $total = array_sum($series);
        $daysInMonth = $start->daysInMonth;

        return [
            'period' => 'monthly',
            'labels' => $labels,
            'series' => $series,
            'heatmap' => $heatmap,
            'total' => $total,
            'average' => round($total / $daysInMonth, 2),
            'average_label' => 'Moyenne / jour',
        ];
    }

    /**
     * Annee: total par mois + moyenne/mois.
     */
    public function yearlyStats(User $user, Carbon $reference): array
    {
        $start = $reference->copy()->startOfYear();
        $end = $reference->copy()->endOfYear();

        $joints = WeedJoint::where('user_id', $user->id)
            ->whereBetween('smoked_at', [$start, $end])
            ->get()
            ->groupBy(fn ($j) => $j->smoked_at->format('Y-m'));

        $labels = [];
        $series = [];

        for ($m = 1; $m <= 12; $m++) {
            $key = $start->copy()->month($m)->format('Y-m');
            $count = $joints->get($key, collect())->count();
            $labels[] = $start->copy()->month($m)->translatedFormat('M');
            $series[] = $count;
        }

        $total = array_sum($series);

        return [
            'period' => 'yearly',
            'labels' => $labels,
            'series' => $series,
            'total' => $total,
            'average' => round($total / 12, 2),
            'average_label' => 'Moyenne / mois',
        ];
    }

    /**
     * Stats croisees achats (montant depense, prix moyen/g) sur une periode [start, end].
     */
    public function purchaseStats(User $user, Carbon $start, Carbon $end): array
    {
        $purchases = WeedPurchase::where('user_id', $user->id)
            ->whereBetween('purchased_at', [$start, $end])
            ->get();

        $totalSpent = (float) $purchases->sum('price');
        $totalWeight = (float) $purchases->sum('weight_grams');

        return [
            'total_spent' => round($totalSpent, 2),
            'total_weight_grams' => round($totalWeight, 2),
            'avg_price_per_gram' => $totalWeight > 0 ? round($totalSpent / $totalWeight, 2) : 0,
            'purchases_count' => $purchases->count(),
        ];
    }
}
