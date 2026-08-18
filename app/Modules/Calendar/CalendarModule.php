<?php

namespace App\Modules\Calendar;

use App\Modules\Calendar\Models\CalendarEvent;
use App\Models\User;
use App\Support\Modules\ModuleContract;
use Carbon\Carbon;

class CalendarModule implements ModuleContract
{
    public function slug(): string
    {
        return 'calendar';
    }

    public function name(): string
    {
        return 'Calendrier';
    }

    public function icon(): string
    {
        return 'calendar';
    }

    public function entryRoute(): string
    {
        return 'apps.calendar.index';
    }

    public function defaultPointsConfig(): array
    {
        return [
            'event_created' => 0,
            'invite_accepted' => 1,
        ];
    }

    /**
     * Nombre d'événements visibles par l'utilisateur (créés ou auxquels il participe,
     * invitation acceptée) sur la période donnée, pour le widget de stats généraliste.
     */
    public function getStats(User $user, string $period, \DateTimeInterface $reference): array
    {
        $ref = Carbon::instance($reference);

        [$start, $end, $divisor, $label] = match ($period) {
            'daily' => [$ref->copy()->startOfDay(), $ref->copy()->endOfDay(), 1, 'Événements ce jour'],
            'weekly' => [$ref->copy()->startOfWeek(), $ref->copy()->endOfWeek(), 7, 'Moyenne / jour'],
            'monthly' => [$ref->copy()->startOfMonth(), $ref->copy()->endOfMonth(), $ref->daysInMonth, 'Moyenne / jour'],
            'yearly' => [$ref->copy()->startOfYear(), $ref->copy()->endOfYear(), 12, 'Moyenne / mois'],
            default => throw new \InvalidArgumentException("Periode inconnue: {$period}"),
        };

        $total = CalendarEvent::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhereHas('attendeeRows', fn ($q2) => $q2->where('user_id', $user->id)->where('status', 'accepted'));
        })
            ->whereBetween('starts_at', [$start, $end])
            ->count();

        return [
            'period' => $period,
            'total' => $total,
            'average' => round($total / $divisor, 2),
            'average_label' => $label,
        ];
    }
}
