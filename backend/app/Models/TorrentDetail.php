<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TorrentDetail extends Model
{
    protected $table = 'torrent_detail';

    protected $fillable = [
        'torrent_id',
        'full_description',
        'language',
        'release_date',
        'genre',
        'quality',
        'format',
        'audio_language',
        'subtitle_language',
        'encoder',
        'source',
        'imdb_rating',
        'runtime',
        'director',
        'cast',
        'cover_image',
        'total_files',
        'nfo_content',
        'has_sample',
        'uploader_status',
        'detail_scraped_at',
        'screenshots',
        'media_info',
        'title',
        'size',
        'seeders',
        'leechers',
        'uploaded_at',
        'uploader',
        'magnet_link',
        'category',
        'description',
        'file_count',
        'type',
        'lastchecked',
        'infohash',
        'files',
        'comments',
        'dateuploaded',
        'download_count',
        'trackerlist',
        'poster_alt',
    ];

    protected $dates = [
        'uploaded_at',
    ];

    // Inverse relation: belongs to Torrent
    public function torrent()
    {
        return $this->belongsTo(Torrent::class, 'torrent_id', 'id');
    }
}
