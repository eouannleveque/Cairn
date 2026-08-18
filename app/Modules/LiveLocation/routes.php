<?php

use App\Modules\LiveLocation\Http\Livewire\LiveLocationBoard;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'app.access:live-location'])
    ->prefix('apps/live-location')
    ->name('apps.live-location.')
    ->group(function () {
        Route::get('/', LiveLocationBoard::class)->name('index');
    });
