<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScriptStatusLog extends Model
{
    protected $fillable = ['status', 'script_url', 'user_agent', 'ip_address'];
}
