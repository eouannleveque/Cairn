<?php

namespace App\Modules\WeedCount;

use Illuminate\Support\ServiceProvider;

class WeedCountServiceProvider extends ServiceProvider
{
    protected string $moduleDir = __DIR__;

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom($this->moduleDir.'/database/migrations');
        $this->loadViewsFrom($this->moduleDir.'/resources/views', 'weed-count');

        $this->loadRoutesFrom($this->moduleDir.'/routes.php');
    }
}
