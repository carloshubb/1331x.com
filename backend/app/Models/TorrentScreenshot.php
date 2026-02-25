<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TorrentScreenshot extends Model
{
    protected $fillable = [
        'torrent_id',
        'image_url',
        'thumbnail_url',
        'caption',
        'order'
    ];

    public function torrent()
    {
        return $this->belongsTo(Torrent::class);
    }
}