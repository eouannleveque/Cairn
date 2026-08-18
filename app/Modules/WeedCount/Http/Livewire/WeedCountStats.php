<?php

namespace App\Modules\WeedCount\Http\Livewire;

use App\Modules\WeedCount\WeedCountModule;
use Illuminate\Support\Carbon;
use Livewire\Component;

class WeedCountStats extends Component
{
    public string $period = 'daily'; // daily | weekly | monthly | yearly
    public string $chartType = 'bar'; // bar | line | pie
    public string $reference; // date de reference (YYYY-MM-DD), navigable

    public function mount(): void
    {
        $this->reference = now()->format('Y-m-d');
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;
    }

    public function setChartType(string $type): void
    {
        $this->chartType = $type;
    }

    /** Navigation precedent/suivant selon la periode active */
    public function shift(int $direction): void
    {
        $date = Carbon::parse($this->reference);

        $this->reference = match ($this->period) {
            'daily' => $date->addDays($direction)->format('Y-m-d'),
            'weekly' => $date->addWeeks($direction)->format('Y-m-d'),
            'monthly' => $date->addMonths($direction)->format('Y-m-d'),
            'yearly' => $date->addYears($direction)->format('Y-m-d'),
            default => $this->reference,
        };
    }

    public function getStatsProperty(): array
    {
        /** @var WeedCountModule $module */
        $module = app(WeedCountModule::class);

        return $module->getStats(auth()->user(), $this->period, Carbon::parse($this->reference));
    }

    public function render()
    {
        return view('weed-count::stats', [
            'stats' => $this->stats,
        ]);
    }
}
