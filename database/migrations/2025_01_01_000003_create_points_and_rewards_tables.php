<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('points_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('app_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('delta'); // positif = gain, negatif = depense
            $table->string('reason'); // ex: weed_purchase_logged, reward_redeemed
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('points_cost');
            $table->string('type')->default('custom'); // item / perk / custom
            $table->unsignedInteger('stock')->nullable(); // null = illimite
            $table->boolean('is_active')->default(true);
            $table->json('config')->nullable();
            $table->timestamps();
        });

        Schema::create('reward_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reward_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('points_spent');
            $table->string('status')->default('pending'); // pending / approved / rejected
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('redeemed_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_redemptions');
        Schema::dropIfExists('rewards');
        Schema::dropIfExists('points_ledger');
    }
};
