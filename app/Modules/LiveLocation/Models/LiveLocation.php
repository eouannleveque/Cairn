<?php

namespace App\Modules\LiveLocation\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveLocation extends Model
{
    protected $table = 'live_locations';

    protected $fillable = ['user_id', 'latitude', 'longitude', 'accuracy', 'recorded_at'];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'recorded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isStale(int $maxAgeSeconds = 120): bool
    {
        return $this->recorded_at->diffInSeconds(now()) > $maxAgeSeconds;
    }
}
