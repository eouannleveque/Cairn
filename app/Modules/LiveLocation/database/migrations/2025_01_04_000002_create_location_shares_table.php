<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('location_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();       // celui qui partage
            $table->foreignId('shared_with_id')->constrained('users')->cascadeOnDelete(); // celui qui peut voir
            $table->boolean('is_active')->default(true); // revocable a tout moment, sans supprimer la ligne
            $table->timestamps();

            $table->unique(['user_id', 'shared_with_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_shares');
    }
};
