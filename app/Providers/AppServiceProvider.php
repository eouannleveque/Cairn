<?php

namespace App\Providers;

use App\Http\Livewire\Profile\RewardsShop;
use App\Http\Livewire\Profile\StatsOverview;
use App\Http\Livewire\Profile\ThemePicker;
use App\Support\Modules\ModuleManager;
use App\Support\Points\PointsService;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleManager::class);
        $this->app->singleton(PointsService::class);
    }

    public function boot(): void
    {
        Livewire::component('profile.theme-picker', ThemePicker::class);
        Livewire::component('profile.stats-overview', StatsOverview::class);
        Livewire::component('profile.rewards-shop', RewardsShop::class);
    }
}
