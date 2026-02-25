<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScriptSetting extends Model
{
    protected $fillable = ['name', 'is_enabled', 'script_url'];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];
}
