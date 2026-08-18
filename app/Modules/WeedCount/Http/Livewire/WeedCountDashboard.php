<?php

namespace App\Modules\WeedCount\Http\Livewire;

use App\Modules\WeedCount\Models\WeedJoint;
use App\Modules\WeedCount\Models\WeedPurchase;
use App\Support\Points\PointsService;
use Illuminate\Support\Carbon;
use Livewire\Component;

class WeedCountDashboard extends Component
{
    // --- modal "ajout a posteriori / edition" ---
    public bool $showJointModal = false;
    public ?int $editingJointId = null;
    public string $jointDate = '';
    public string $jointTime = '';

    // --- modal "achat d'un bout" ---
    public bool $showPurchaseModal = false;
    public string $purchaseLabel = '';
    public float $purchaseWeight = 0;
    public float $purchasePrice = 0;
    public string $purchaseDate = '';

    public function mount(): void
    {
        $this->jointDate = now()->format('Y-m-d');
        $this->jointTime = now()->format('H:i');
        $this->purchaseDate = now()->format('Y-m-d');
    }

    public function getTodayJointsProperty()
    {
        return WeedJoint::where('user_id', auth()->id())
            ->whereBetween('smoked_at', [now()->startOfDay(), now()->endOfDay()])
            ->orderByDesc('smoked_at')
            ->get();
    }

    public function getTodayCountProperty(): int
    {
        return $this->todayJoints->count();
    }

    /** Bouton "+1" */
    public function addOne(): void
    {
        WeedJoint::create([
            'user_id' => auth()->id(),
            'smoked_at' => now(),
            'source' => 'live',
        ]);

        app(PointsService::class)->grantForEvent(auth()->user(), 'weed-count', 'joint_logged');
    }

    /** Ouvre le modal pour ajouter un joint a posteriori (formulaire vide) */
    public function openAddBackdated(): void
    {
        $this->editingJointId = null;
        $this->jointDate = now()->format('Y-m-d');
        $this->jointTime = now()->format('H:i');
        $this->showJointModal = true;
    }

    /** Ouvre le modal pour editer un joint existant */
    public function openEdit(int $jointId): void
    {
        $joint = WeedJoint::where('user_id', auth()->id())->findOrFail($jointId);

        $this->editingJointId = $joint->id;
        $this->jointDate = $joint->smoked_at->format('Y-m-d');
        $this->jointTime = $joint->smoked_at->format('H:i');
        $this->showJointModal = true;
    }

    public function saveJoint(): void
    {
        $this->validate([
            'jointDate' => 'required|date',
            'jointTime' => 'required',
        ]);

        $smokedAt = Carbon::parse("{$this->jointDate} {$this->jointTime}");

        if ($this->editingJointId) {
            $joint = WeedJoint::where('user_id', auth()->id())->findOrFail($this->editingJointId);
            $joint->update(['smoked_at' => $smokedAt, 'source' => 'edited']);
        } else {
            WeedJoint::create([
                'user_id' => auth()->id(),
                'smoked_at' => $smokedAt,
                'source' => 'backdated',
            ]);
            app(PointsService::class)->grantForEvent(auth()->user(), 'weed-count', 'joint_logged');
        }

        $this->showJointModal = false;
    }

    public function deleteJoint(int $jointId): void
    {
        WeedJoint::where('user_id', auth()->id())->findOrFail($jointId)->delete();
    }

    public function openPurchaseModal(): void
    {
        $this->reset(['purchaseLabel', 'purchaseWeight', 'purchasePrice']);
        $this->purchaseDate = now()->format('Y-m-d');
        $this->showPurchaseModal = true;
    }

    public function savePurchase(): void
    {
        $this->validate([
            'purchaseLabel' => 'nullable|string|max:255',
            'purchaseWeight' => 'required|numeric|min:0.01',
            'purchasePrice' => 'required|numeric|min:0',
            'purchaseDate' => 'required|date',
        ]);

        WeedPurchase::create([
            'user_id' => auth()->id(),
            'label' => $this->purchaseLabel,
            'weight_grams' => $this->purchaseWeight,
            'price' => $this->purchasePrice,
            'purchased_at' => Carbon::parse($this->purchaseDate),
        ]);

        app(PointsService::class)->grantForEvent(auth()->user(), 'weed-count', 'purchase_logged');

        $this->showPurchaseModal = false;
    }

    public function render()
    {
        return view('weed-count::dashboard');
    }
}
