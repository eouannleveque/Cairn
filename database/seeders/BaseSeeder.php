<?php

namespace Database\Seeders;

use App\Models\AppModule;
use App\Models\MailTemplate;
use Illuminate\Database\Seeder;

class BaseSeeder extends Seeder
{
    public function run(): void
    {
        AppModule::firstOrCreate(
            ['slug' => 'weed-count'],
            ['name' => 'Weed Count', 'icon' => 'leaf', 'is_active' => true]
        );

        AppModule::firstOrCreate(
            ['slug' => 'calendar'],
            ['name' => 'Calendrier', 'icon' => 'calendar', 'is_active' => true]
        );

        AppModule::firstOrCreate(
            ['slug' => 'live-location'],
            ['name' => 'Position en direct', 'icon' => 'map-pin', 'is_active' => true]
        );

        MailTemplate::firstOrCreate(['key' => 'welcome'], [
            'subject' => 'Bienvenue sur {{app_name}} !',
            'body_html' => '<p>Salut {{user.name}},</p><p>Ton compte est prêt. À bientôt !</p>',
            'variables' => ['user.name', 'app_name'],
        ]);

        MailTemplate::firstOrCreate(['key' => 'reward_approved'], [
            'subject' => 'Ta récompense a été validée',
            'body_html' => '<p>Salut {{user.name}},</p><p>Ta demande d\'échange a été approuvée par un admin.</p>',
            'variables' => ['user.name'],
        ]);

        MailTemplate::firstOrCreate(['key' => 'reward_rejected'], [
            'subject' => 'Ta demande d\'échange a été refusée',
            'body_html' => '<p>Salut {{user.name}},</p><p>Ta demande a été refusée, tes points ont été recrédités.</p>',
            'variables' => ['user.name'],
        ]);
    }
}
