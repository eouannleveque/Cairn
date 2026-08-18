<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_event_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('calendar_events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // toujours un user interne
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending'); // pending / accepted / declined
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_event_attendees');
    }
};
