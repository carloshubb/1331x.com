<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TorrentFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'torrent_id',
        'filename',
        'path',
        'size_bytes',
        'size_formatted',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
    ];

    public function torrent(): BelongsTo
    {
        return $this->belongsTo(Torrent::class);
    }
}
