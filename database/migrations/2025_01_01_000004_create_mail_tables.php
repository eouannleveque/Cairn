<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_settings', function (Blueprint $table) {
            $table->id();
            $table->string('driver')->default('smtp');
            $table->string('host')->nullable();
            $table->integer('port')->nullable();
            $table->string('username')->nullable();
            $table->text('password')->nullable(); // encrypted via cast
            $table->string('encryption')->nullable();
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();
            $table->timestamps();
        });

        Schema::create('mail_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // welcome, reset_password, reward_redeemed...
            $table->string('subject');
            $table->longText('body_html');
            $table->json('variables')->nullable(); // liste des variables dispo, pour l'UI admin
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_templates');
        Schema::dropIfExists('mail_settings');
    }
};
