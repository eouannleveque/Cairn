<?php

namespace App\Http\Livewire\Profile;

use App\Models\Reward;
use App\Support\Points\PointsService;
use Livewire\Component;

class RewardsShop extends Component
{
    public function redeem(int $rewardId): void
    {
        $reward = Reward::where('is_active', true)->findOrFail($rewardId);
        $user = auth()->user();

        if ($user->points_balance < $reward->points_cost) {
            $this->addError('points', 'Solde de points insuffisant.');

            return;
        }

        if ($reward->stock !== null && $reward->stock <= 0) {
            $this->addError('points', 'Cette récompense n\'est plus en stock.');

            return;
        }

        app(PointsService::class)->apply(
            $user,
            -$reward->points_cost,
            'reward_redeemed',
            null,
            ['reward_id' => $reward->id]
        );

        $reward->redemptions()->create([
            'user_id' => $user->id,
            'points_spent' => $reward->points_cost,
            'status' => 'pending',
        ]);

        if ($reward->stock !== null) {
            $reward->decrement('stock');
        }

        $this->dispatch('reward-redeemed');
    }

    public function render()
    {
        return view('profile.rewards-shop', [
            'rewards' => Reward::where('is_active', true)->orderBy('points_cost')->get(),
            'balance' => auth()->user()->points_balance,
        ]);
    }
}
