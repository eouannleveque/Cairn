<?php

namespace App\Modules\Calendar\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CalendarEvent extends Model
{
    protected $table = 'calendar_events';

    protected $fillable = [
        'user_id', 'title', 'description', 'location', 'starts_at', 'ends_at', 'all_day', 'color',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'all_day' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attendeeRows(): HasMany
    {
        return $this->hasMany(CalendarEventAttendee::class, 'event_id');
    }

    /** Utilisateurs invités (internes uniquement), avec le statut en pivot */
    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'calendar_event_attendees', 'event_id', 'user_id')
            ->withPivot(['status', 'invited_by', 'responded_at']);
    }

    /** Un user (créateur ou invité) peut-il voir cet événement ? */
    public function isVisibleTo(User $user): bool
    {
        return $this->user_id === $user->id
            || $this->attendeeRows()->where('user_id', $user->id)->exists();
    }
}
