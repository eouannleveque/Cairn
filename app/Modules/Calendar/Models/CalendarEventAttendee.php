<?php

namespace App\Modules\Calendar\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarEventAttendee extends Model
{
    protected $table = 'calendar_event_attendees';

    protected $fillable = ['event_id', 'user_id', 'invited_by', 'status', 'responded_at'];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(CalendarEvent::class, 'event_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
