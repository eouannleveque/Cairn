<?php

namespace App\Modules\Calendar;

use Illuminate\Support\ServiceProvider;

class CalendarServiceProvider extends ServiceProvider
{
    protected string $moduleDir = __DIR__;

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom($this->moduleDir.'/database/migrations');
        $this->loadViewsFrom($this->moduleDir.'/resources/views', 'calendar');
        $this->loadRoutesFrom($this->moduleDir.'/routes.php');
    }
}
