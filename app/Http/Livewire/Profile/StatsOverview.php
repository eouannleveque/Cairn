<?php

namespace App\Http\Livewire\Profile;

use App\Support\Modules\ModuleManager;
use Illuminate\Support\Carbon;
use Livewire\Component;

class StatsOverview extends Component
{
    public string $period = 'daily';

    public function setPeriod(string $period): void
    {
        $this->period = $period;
    }

    public function getStatsByAppProperty(): array
    {
        $user = auth()->user();
        $modules = app(ModuleManager::class)->all();

        $stats = [];

        foreach ($user->apps as $appModule) {
            $instance = $modules->get($appModule->slug);

            if (! $instance) {
                continue;
            }

            $stats[] = [
                'app' => $appModule,
                'stats' => $instance->getStats($user, $this->period, Carbon::now()),
            ];
        }

        return $stats;
    }

    public function render()
    {
        return view('profile.stats-overview', [
            'statsByApp' => $this->statsByApp,
        ]);
    }
}
