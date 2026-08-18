<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Represente une "app" activable dans la table `apps`.
 * Nomme AppModule (et non App) pour ne pas entrer en conflit avec Illuminate\Foundation\Application.
 */
class AppModule extends Model
{
    protected $table = 'apps';

    protected $fillable = [
        'slug', 'name', 'icon', 'description', 'is_active', 'config',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'app_user_access')
            ->withPivot(['granted_by', 'granted_at']);
    }

    /**
     * Recupere l'instance du module (classe PHP) correspondant, via le ModuleManager.
     */
    public function moduleInstance(): ?\App\Support\Modules\ModuleContract
    {
        return app(\App\Support\Modules\ModuleManager::class)->get($this->slug);
    }
}
