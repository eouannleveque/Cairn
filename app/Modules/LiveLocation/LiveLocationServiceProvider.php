<?php

namespace App\Modules\LiveLocation;

use Illuminate\Support\ServiceProvider;

class LiveLocationServiceProvider extends ServiceProvider
{
    protected string $moduleDir = __DIR__;

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom($this->moduleDir.'/database/migrations');
        $this->loadViewsFrom($this->moduleDir.'/resources/views', 'live-location');
        $this->loadRoutesFrom($this->moduleDir.'/routes.php');
    }
}
