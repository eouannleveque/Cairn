@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-6 space-y-10">
    <div class="flex items-center gap-4">
        <div class="w-16 h-16 rounded-full flex items-center justify-center text-xl font-bold text-white"
             style="background: {{ auth()->user()->theme_color }}">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </div>
        <div>
            <h1 class="text-xl font-bold">{{ auth()->user()->name }}</h1>
            <p class="text-sm text-gray-500">{{ auth()->user()->email }}</p>
        </div>
    </div>

    @livewire('profile.stats-overview')
    <hr class="border-gray-200 dark:border-gray-700">
    @livewire('profile.theme-picker')
    <hr class="border-gray-200 dark:border-gray-700">
    @livewire('profile.rewards-shop')
</div>
@endsection
