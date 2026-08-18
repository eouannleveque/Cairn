<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('email');
            $table->string('theme_color', 7)->default('#6366f1')->after('avatar');
            $table->json('theme_settings')->nullable()->after('theme_color');
            $table->integer('points_balance')->default(0)->after('theme_settings');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar', 'theme_color', 'theme_settings', 'points_balance']);
        });
    }
};
