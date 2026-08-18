<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weed_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->decimal('weight_grams', 8, 2);
            $table->decimal('price', 8, 2);
            $table->dateTime('purchased_at');
            $table->timestamps();

            $table->index(['user_id', 'purchased_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weed_purchases');
    }
};
