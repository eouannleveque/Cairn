<?php

use App\Modules\WeedCount\Http\Livewire\WeedCountDashboard;
use App\Modules\WeedCount\Http\Livewire\WeedCountStats;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'app.access:weed-count'])
    ->prefix('apps/weed-count')
    ->name('apps.weed-count.')
    ->group(function () {
        Route::get('/', WeedCountDashboard::class)->name('index');
        Route::get('/stats', WeedCountStats::class)->name('stats');
    });
