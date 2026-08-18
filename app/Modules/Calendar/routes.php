<?php

use App\Modules\Calendar\Http\Livewire\CalendarBoard;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'app.access:calendar'])
    ->prefix('apps/calendar')
    ->name('apps.calendar.')
    ->group(function () {
        Route::get('/', CalendarBoard::class)->name('index');
    });
