<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use App\Models\TorrentScreenshot;
use App\Models\TorrentMediaInfo;

class PostPopular extends Model
{
    use HasFactory;

    //
    protected $table = 'post_populars';

    
}