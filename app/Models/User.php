<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'avatar', 'theme_color', 'theme_settings',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'theme_settings' => 'array',
    ];

    // Alias pratique utilise par ModuleManager / policies
    public function getIsAdminAttribute(): bool
    {
        return $this->hasRole('admin');
    }

    public function pointsLedger(): HasMany
    {
        return $this->hasMany(PointsLedgerEntry::class);
    }

    public function apps(): BelongsToMany
    {
        return $this->belongsToMany(AppModule::class, 'app_user_access')
            ->withPivot(['granted_by', 'granted_at']);
    }

    public function rewardRedemptions(): HasMany
    {
        return $this->hasMany(RewardRedemption::class);
    }
}
