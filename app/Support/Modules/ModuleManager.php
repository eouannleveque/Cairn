<?php

namespace App\Support\Modules;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class ModuleManager
{
    /** @var Collection<string, ModuleContract> indexee par slug */
    protected Collection $modules;

    public function __construct()
    {
        $this->modules = collect();
        $this->discover();
    }

    protected function discover(): void
    {
        $base = app_path('Modules');

        if (! File::isDirectory($base)) {
            return;
        }

        foreach (File::directories($base) as $moduleDir) {
            $manifest = $moduleDir.'/module.json';

            if (! File::exists($manifest)) {
                continue;
            }

            $meta = json_decode(File::get($manifest), true);
            $providerClass = $meta['provider'] ?? null;
            $instanceClass = $meta['instance'] ?? null;

            // La classe qui implemente ModuleContract (stats, points, meta)
            if ($instanceClass && class_exists($instanceClass)) {
                /** @var ModuleContract $instance */
                $instance = app($instanceClass);
                $this->modules->put($instance->slug(), $instance);
            }
        }
    }

    public function all(): Collection
    {
        return $this->modules;
    }

    public function get(string $slug): ?ModuleContract
    {
        return $this->modules->get($slug);
    }

    /**
     * Verifie si un user a acces a une app (active + autorisee).
     */
    public function userHasAccess(\App\Models\User $user, string $slug): bool
    {
        $appModule = \App\Models\AppModule::where('slug', $slug)->first();

        if (! $appModule || ! $appModule->is_active) {
            return false;
        }

        if ($user->is_admin) {
            return true;
        }

        return $appModule->users()->where('user_id', $user->id)->exists();
    }
}
