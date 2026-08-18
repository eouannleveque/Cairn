@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto p-6 space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">📅 Calendrier</h1>
        <button wire:click="openCreate" class="btn-primary">+ Nouvel événement</button>
    </div>

    {{-- Invitations en attente --}}
    @if ($this->pendingInvites->isNotEmpty())
        <div class="rounded-xl p-4 border border-amber-300 bg-amber-50 dark:bg-amber-900/20 space-y-2">
            <h2 class="font-semibold text-sm">Invitations en attente</h2>
            @foreach ($this->pendingInvites as $invite)
                <div class="flex items-center justify-between text-sm">
                    <span>
                        <strong>{{ $invite->title }}</strong>
                        — {{ $invite->starts_at->translatedFormat('d M H:i') }}
                        <span class="text-gray-500">par {{ $invite->creator->name }}</span>
                    </span>
                    <span class="space-x-2">
                        <button wire:click="respond({{ $invite->id }}, 'accepted')" class="text-green-600 underline">Accepter</button>
                        <button wire:click="respond({{ $invite->id }}, 'declined')" class="text-red-500 underline">Refuser</button>
                    </span>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Navigation mois --}}
    <div class="flex items-center justify-between">
        <button wire:click="prevMonth" class="text-sm underline">← Mois précédent</button>
        <span class="font-medium">{{ \Carbon\Carbon::parse($month.'-01')->translatedFormat('F Y') }}</span>
        <button wire:click="nextMonth" class="text-sm underline">Mois suivant →</button>
    </div>

    {{-- Grille du mois --}}
    @php
        $start = \Carbon\Carbon::parse($month.'-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $firstWeekday = $start->copy()->startOfWeek(); // lundi
        $lastWeekday = $end->copy()->endOfWeek();
        $eventsByDay = $this->eventsByDay;
    @endphp

    <div class="grid grid-cols-7 gap-1 text-xs text-center text-gray-500 mb-1">
        @foreach (['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'] as $d)
            <div>{{ $d }}</div>
        @endforeach
    </div>

    <div class="grid grid-cols-7 gap-1">
        @php $cursor = $firstWeekday->copy(); @endphp
        @while ($cursor <= $lastWeekday)
            @php
                $key = $cursor->format('Y-m-d');
                $inMonth = $cursor->month === $start->month;
                $dayEvents = $eventsByDay->get($key, collect());
            @endphp
            <div wire:click="openCreate('{{ $key }}')"
                 class="min-h-[90px] rounded-lg p-1.5 border border-gray-200 dark:border-gray-700 cursor-pointer hover:border-gray-400 {{ $inMonth ? '' : 'opacity-40' }}">
                <div class="text-xs text-gray-500">{{ $cursor->day }}</div>
                <div class="space-y-0.5 mt-1">
                    @foreach ($dayEvents->take(3) as $event)
                        <div wire:click.stop="openEdit({{ $event->id }})"
                             class="text-[11px] truncate rounded px-1 py-0.5 text-white"
                             style="background: {{ $event->color ?? '#6366f1' }}">
                            {{ $event->title }}
                        </div>
                    @endforeach
                    @if ($dayEvents->count() > 3)
                        <div class="text-[10px] text-gray-400">+{{ $dayEvents->count() - 3 }} autre(s)</div>
                    @endif
                </div>
            </div>
            @php $cursor->addDay(); @endphp
        @endwhile
    </div>

    {{-- Modal creation/edition --}}
    @if ($showEventModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" wire:click.self="$set('showEventModal', false)">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-md space-y-4 max-h-[90vh] overflow-y-auto">
                <h3 class="font-semibold">{{ $editingEventId ? 'Modifier l\'événement' : 'Nouvel événement' }}</h3>

                <div>
                    <label class="block text-sm mb-1">Titre</label>
                    <input type="text" wire:model="title" class="w-full rounded border-gray-300">
                    @error('title') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm mb-1">Description</label>
                    <textarea wire:model="description" rows="2" class="w-full rounded border-gray-300"></textarea>
                </div>

                <div>
                    <label class="block text-sm mb-1">Lieu</label>
                    <input type="text" wire:model="location" class="w-full rounded border-gray-300">
                </div>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" wire:model="allDay"> Journée entière
                </label>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm mb-1">Début</label>
                        <input type="date" wire:model="startDate" class="w-full rounded border-gray-300 mb-1">
                        @unless ($allDay)
                            <input type="time" wire:model="startTime" class="w-full rounded border-gray-300">
                        @endunless
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Fin</label>
                        <input type="date" wire:model="endDate" class="w-full rounded border-gray-300 mb-1">
                        @unless ($allDay)
                            <input type="time" wire:model="endTime" class="w-full rounded border-gray-300">
                        @endunless
                    </div>
                </div>

                {{-- Invitation interne uniquement: recherche parmi les users de la plateforme --}}
                <div>
                    <label class="block text-sm mb-1">Inviter des personnes (interne uniquement)</label>
                    <input type="text" wire:model.live.debounce.300ms="attendeeSearch"
                           placeholder="Rechercher un utilisateur..." class="w-full rounded border-gray-300 mb-2">

                    @if (!empty($attendeeIds))
                        <div class="flex flex-wrap gap-1 mb-2">
                            @foreach (\App\Models\User::whereIn('id', $attendeeIds)->get() as $selected)
                                <span class="text-xs bg-indigo-100 dark:bg-indigo-900 rounded-full px-2 py-1 flex items-center gap-1">
                                    {{ $selected->name }}
                                    <button wire:click="toggleAttendee({{ $selected->id }})" class="font-bold">×</button>
                                </span>
                            @endforeach
                        </div>
                    @endif

                    @if ($attendeeSearch)
                        <div class="border rounded max-h-32 overflow-y-auto">
                            @foreach ($this->searchableUsers as $u)
                                <div wire:click="toggleAttendee({{ $u->id }})"
                                     class="px-2 py-1 text-sm cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between">
                                    {{ $u->name }}
                                    @if (in_array($u->id, $attendeeIds)) <span class="text-green-600">✓</span> @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex justify-between items-center pt-2">
                    @if ($editingEventId)
                        <button wire:click="delete({{ $editingEventId }})"
                                wire:confirm="Supprimer cet événement ?"
                                class="text-sm text-red-500 underline">Supprimer</button>
                    @else
                        <span></span>
                    @endif
                    <div class="space-x-2">
                        <button wire:click="$set('showEventModal', false)" class="btn-secondary">Annuler</button>
                        <button wire:click="save" class="btn-primary">Enregistrer</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
