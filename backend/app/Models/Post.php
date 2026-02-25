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
use Carbon\Carbon;

class Post extends Model
{
  use HasFactory;

  //
  protected $table = 'posts';
  protected $fillable = [
    'id',
    'category_id',
    'subcategory_id',
    'language',
    'uploaded_by',
    'size',
    'downloads',
    'seeders',
    'leechers',
    'last_checked',
    'date_uploaded',
    'uploaded_at',
    'magnet_link',
    'infohash',
    'stream_link',
    'cover_image',
    'title',
    'slogan',
    'description',
    'name',
    'content',
    'files',
    'tracker_list',
    'comments_count',
    'submit_flag',
  ];

  public function category(): BelongsTo
  {
    return $this->belongsTo(Category::class);
  }

  public function subcategory(): BelongsTo
  {
    return $this->belongsTo(SubCategory::class);
  }

  public function uploader(): BelongsTo
  {
    return $this->belongsTo(User::class, 'uploader_id');
  }

  public function comments(): HasMany
  {
    return $this->hasMany(Comment::class, 'torrent_id');
  }

  // to change a torrent title as slug title
  protected $appends = ['slugged_title', 'category_name', 'subcategory_name', 'uploaded_ago'];
  public function getSluggedTitleAttribute()
  {
    $slug = $this->name;

    // Convert to ASCII (removes emojis like ⭐)
    $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug);

    // Replace .mp4 explicitly
    $slug = str_ireplace('.mp4', '-mp4', $slug);

    // Replace .mkv explicitly
    $slug = str_ireplace('.mkv', '-mkv', $slug);

    // Remove all non-alphanumeric characters except space and hyphen
    $slug = preg_replace('/[^A-Za-z0-9\s-]/', '', $slug);

    // Replace spaces and slashes with hyphen
    $slug = preg_replace('/[\s\/]+/', '-', $slug);

    // Remove multiple hyphens
    $slug = preg_replace('/-+/', '-', $slug);

    return strtolower(trim($slug, '-'));
  }


  public function getCategoryNameAttribute()
  {
    return $this->category ? $this->category->name : null;
  }

  public function getSubCategoryNameAttribute()
  {
    return $this->subcategory ? $this->subcategory->name : null;
  }

  // uploaded time
  public function getUploadedAgoAttribute()
  {
    //   return Carbon::parse($this->uploaded_at)->diffForHumans();
    return Carbon::parse($this->uploaded_at)->format("M. jS 'y");;
  }
}
