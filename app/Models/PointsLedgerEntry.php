<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointsLedgerEntry extends Model
{
    protected $table = 'points_ledger';

    protected $fillable = ['app_id', 'delta', 'reason', 'meta'];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function app(): BelongsTo
    {
        return $this->belongsTo(AppModule::class, 'app_id');
    }
}
