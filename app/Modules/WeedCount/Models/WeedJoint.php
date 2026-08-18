<?php

namespace App\Modules\WeedCount\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeedJoint extends Model
{
    protected $table = 'weed_joints';

    protected $fillable = ['user_id', 'smoked_at', 'source'];

    protected $casts = [
        'smoked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
