<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomeImageList extends Model
{
  use HasFactory;

  protected $fillable = [
    'title',
    'torrent_id',
    'image_url',
    'link',
    'quality',
    'order',
    'is_active',
  ];

  public function torrent(): BelongsTo
  {
    return $this->belongsTo(Post::class, 'torrent_id', 'id');
  }


  protected $appends = ['alt_name'];

  public function getAltNameAttribute()
  {
    return $this->torrent ? $this->torrent->title : 'Movie cover';
  }
}
