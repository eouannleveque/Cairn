<?php

namespace App\Modules\LiveLocation\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationShare extends Model
{
    protected $table = 'location_shares';

    protected $fillable = ['user_id', 'shared_with_id', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function sharer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function viewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_with_id');
    }
}
