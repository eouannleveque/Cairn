<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailSetting extends Model
{
    protected $fillable = [
        'driver', 'host', 'port', 'username', 'password', 'encryption', 'from_address', 'from_name',
    ];

    protected $casts = [
        'password' => 'encrypted',
    ];
}
