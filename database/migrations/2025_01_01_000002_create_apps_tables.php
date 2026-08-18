<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apps', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // ex: weed-count, matches module.json
            $table->string('name');
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('config')->nullable(); // valeurs de points, reglages du module
            $table->timestamps();
        });

        Schema::create('app_user_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('app_id')->constrained()->cascadeOnDelete();
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('granted_at')->useCurrent();
            $table->unique(['user_id', 'app_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_user_access');
        Schema::dropIfExists('apps');
    }
};
