<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weed_joints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->dateTime('smoked_at');
            $table->string('source')->default('live'); // live / backdated / edited
            $table->timestamps();

            $table->index(['user_id', 'smoked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weed_joints');
    }
};
