<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete(); // 1 ligne = derniere position
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedInteger('accuracy')->nullable(); // precision en metres, renvoyee par l'API navigateur
            $table->timestamp('recorded_at'); // horodatage de la mesure GPS (peut differer de updated_at)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_locations');
    }
};
