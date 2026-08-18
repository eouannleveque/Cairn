<?php

namespace App\Modules\Calendar\Http\Livewire;

use App\Models\User;
use App\Modules\Calendar\Models\CalendarEvent;
use App\Support\Points\PointsService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CalendarBoard extends Component
{
    public string $month; // format Y-m, mois affiché

    // --- modal creation/edition ---
    public bool $showEventModal = false;
    public ?int $editingEventId = null;
    public string $title = '';
    public string $description = '';
    public string $location = '';
    public string $startDate = '';
    public string $startTime = '';
    public string $endDate = '';
    public string $endTime = '';
    public bool $allDay = false;
    /** @var int[] ids des users invites (recherche interne uniquement) */
    public array $attendeeIds = [];
    public string $attendeeSearch = '';

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
    }

    public function prevMonth(): void
    {
        $this->month = Carbon::parse($this->month.'-01')->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->month = Carbon::parse($this->month.'-01')->addMonth()->format('Y-m');
    }

    /**
     * Evenements visibles par l'utilisateur (crees par lui, ou invitation en attente/acceptee)
     * sur le mois affiche, groupes par jour (Y-m-d).
     */
    public function getEventsByDayProperty()
    {
        $start = Carbon::parse($this->month.'-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $user = auth()->user();

        $events = CalendarEvent::with(['creator', 'attendees'])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('attendeeRows', fn ($q2) => $q2->where('user_id', $user->id));
            })
            ->whereBetween('starts_at', [$start, $end])
            ->orderBy('starts_at')
            ->get();

        return $events->groupBy(fn ($e) => $e->starts_at->format('Y-m-d'));
    }

    /** Invitations en attente de reponse pour l'utilisateur connecte */
    public function getPendingInvitesProperty()
    {
        return CalendarEvent::with('creator')
            ->whereHas('attendeeRows', fn ($q) => $q->where('user_id', auth()->id())->where('status', 'pending'))
            ->orderBy('starts_at')
            ->get();
    }

    /** Liste des utilisateurs internes recherchables pour l'invitation (jamais d'email externe) */
    public function getSearchableUsersProperty()
    {
        return User::where('id', '!=', auth()->id())
            ->when($this->attendeeSearch, fn ($q) => $q->where('name', 'like', "%{$this->attendeeSearch}%"))
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    public function toggleAttendee(int $userId): void
    {
        if (in_array($userId, $this->attendeeIds, true)) {
            $this->attendeeIds = array_values(array_diff($this->attendeeIds, [$userId]));
        } else {
            $this->attendeeIds[] = $userId;
        }
    }

    public function openCreate(?string $date = null): void
    {
        $this->reset(['editingEventId', 'title', 'description', 'location', 'attendeeIds', 'attendeeSearch']);
        $this->allDay = false;

        $day = $date ? Carbon::parse($date) : now();
        $this->startDate = $day->format('Y-m-d');
        $this->endDate = $day->format('Y-m-d');
        $this->startTime = '09:00';
        $this->endTime = '10:00';

        $this->showEventModal = true;
    }

    public function openEdit(int $eventId): void
    {
        $event = CalendarEvent::with('attendees')->findOrFail($eventId);

        abort_unless($event->isVisibleTo(auth()->user()), 403);

        $this->editingEventId = $event->id;
        $this->title = $event->title;
        $this->description = (string) $event->description;
        $this->location = (string) $event->location;
        $this->startDate = $event->starts_at->format('Y-m-d');
        $this->startTime = $event->starts_at->format('H:i');
        $this->endDate = $event->ends_at->format('Y-m-d');
        $this->endTime = $event->ends_at->format('H:i');
        $this->allDay = $event->all_day;
        $this->attendeeIds = $event->attendees->pluck('id')->all();

        $this->showEventModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'startDate' => 'required|date',
            'endDate' => 'required|date',
        ]);

        $startsAt = Carbon::parse("{$this->startDate} ".($this->allDay ? '00:00' : $this->startTime));
        $endsAt = Carbon::parse("{$this->endDate} ".($this->allDay ? '23:59' : $this->endTime));

        DB::transaction(function () use ($startsAt, $endsAt) {
            $data = [
                'user_id' => auth()->id(),
                'title' => $this->title,
                'description' => $this->description,
                'location' => $this->location,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'all_day' => $this->allDay,
                'color' => auth()->user()->theme_color,
            ];

            if ($this->editingEventId) {
                $event = CalendarEvent::findOrFail($this->editingEventId);
                abort_unless($event->user_id === auth()->id(), 403, 'Seul le créateur peut modifier l\'événement.');
                $event->update($data);
            } else {
                $event = CalendarEvent::create($data);
                app(PointsService::class)->grantForEvent(auth()->user(), 'calendar', 'event_created');
            }

            // Resynchronise les invites internes: garde les statuts existants, ajoute les nouveaux en pending
            $existingIds = $event->attendeeRows()->pluck('user_id')->all();
            $toAdd = array_diff($this->attendeeIds, $existingIds);
            $toRemove = array_diff($existingIds, $this->attendeeIds);

            foreach ($toAdd as $userId) {
                $event->attendeeRows()->create([
                    'user_id' => $userId,
                    'invited_by' => auth()->id(),
                    'status' => 'pending',
                ]);
            }

            if (! empty($toRemove)) {
                $event->attendeeRows()->whereIn('user_id', $toRemove)->delete();
            }
        });

        $this->showEventModal = false;
    }

    public function delete(int $eventId): void
    {
        $event = CalendarEvent::findOrFail($eventId);
        abort_unless($event->user_id === auth()->id(), 403, 'Seul le créateur peut supprimer l\'événement.');
        $event->delete();
    }

    /** Reponse a une invitation (par l'invite lui-meme uniquement) */
    public function respond(int $eventId, string $status): void
    {
        abort_unless(in_array($status, ['accepted', 'declined'], true), 400);

        $event = CalendarEvent::findOrFail($eventId);

        $row = $event->attendeeRows()->where('user_id', auth()->id())->firstOrFail();
        $row->update(['status' => $status, 'responded_at' => now()]);

        if ($status === 'accepted') {
            app(PointsService::class)->grantForEvent(auth()->user(), 'calendar', 'invite_accepted');
        }
    }

    public function render()
    {
        return view('calendar::board');
    }
}
