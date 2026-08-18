<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailTemplate extends Model
{
    protected $fillable = ['key', 'subject', 'body_html', 'variables'];

    protected $casts = [
        'variables' => 'array',
    ];

    /**
     * Remplace les {{variable}} par les valeurs fournies. Simple et suffisant
     * pour ce cas d'usage (pas besoin d'un moteur Blade complet, plus sûr).
     */
    public function render(array $values): array
    {
        $replace = fn (string $text) => preg_replace_callback(
            '/\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}/',
            fn ($m) => data_get($values, $m[1], ''),
            $text
        );

        return [
            'subject' => $replace($this->subject),
            'body_html' => $replace($this->body_html),
        ];
    }
}
