<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PopularTorrent extends Model
{
    //
    protected $table = 'popular_torrents';

    protected $fillable = [
        'subcategory_id',
        'torrent_link',
        'category_title',
        'name',
        'comments_count',
        'seeders',
        'leechers',
        'size_formatted',
        'approved_at',
        'uploader',
    ];

    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class);
    }
}
