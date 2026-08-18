<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reward extends Model
{
    protected $fillable = ['name', 'description', 'points_cost', 'type', 'stock', 'is_active', 'config'];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
    ];

    public function redemptions(): HasMany
    {
        return $this->hasMany(RewardRedemption::class);
    }
}
