<?php

namespace App\Providers;

use App\Models\MailSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class DynamicMailServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Evite de planter les commandes artisan avant la 1ere migration
        if (! Schema::hasTable('mail_settings')) {
            return;
        }

        $setting = MailSetting::first();

        if (! $setting) {
            return;
        }

        config([
            'mail.default' => $setting->driver,
            'mail.mailers.smtp.host' => $setting->host,
            'mail.mailers.smtp.port' => $setting->port,
            'mail.mailers.smtp.username' => $setting->username,
            'mail.mailers.smtp.password' => $setting->password,
            'mail.mailers.smtp.encryption' => $setting->encryption ?: null,
            'mail.from.address' => $setting->from_address,
            'mail.from.name' => $setting->from_name,
        ]);
    }
}
