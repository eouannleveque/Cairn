<?php

namespace App\Modules\WeedCount\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeedPurchase extends Model
{
    protected $table = 'weed_purchases';

    protected $fillable = ['user_id', 'label', 'weight_grams', 'price', 'purchased_at'];

    protected $casts = [
        'purchased_at' => 'datetime',
        'weight_grams' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pricePerGram(): float
    {
        return $this->weight_grams > 0 ? round((float) $this->price / (float) $this->weight_grams, 2) : 0.0;
    }
}
