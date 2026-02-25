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

class Torrent extends Model
{
    use HasFactory;

    //
    protected $table = 'torrents';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category_id',
        'subcategory_id',
        'uploader_id',
        'uploader',
        'info_hash',
        'magnet_link',
        'torrent_file_path',
        'size_bytes',
        'size_formatted',
        'files_count',
        'seeders',
        'leechers',
        'completed_downloads',
        'views_count',
        'comments_count',
        'rating',
        'ratings_count',
        'status',
        'is_anonymous',
        'is_featured',
        'is_vip',
        'is_trusted',
        'tags',
        'imdb_id',
        'tmdb_id',
        'approved_at',
        'scraped_at',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'files_count' => 'integer',
            'seeders' => 'integer',
            'leechers' => 'integer',
            'completed_downloads' => 'integer',
            'views_count' => 'integer',
            'comments_count' => 'integer',
            'rating' => 'decimal:2',
            'ratings_count' => 'integer',
            'is_anonymous' => 'boolean',
            'is_featured' => 'boolean',
            'is_vip' => 'boolean',
            'is_trusted' => 'boolean',
            'tags' => 'array',
            'approved_at' => 'datetime',
            'scraped_at' => 'datetime',
        ];
    }

    /**
     * Map scraper data to database columns
     */
    public static function mapFromScraper(array $data): array
    {
        return [
            'name' => $data['name'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'subcategory_id' => $data['subcategory_id'] ?? null,
            'seeders' => (int) ($data['seeds'] ?? 0),
            'leechers' => (int) ($data['leeches'] ?? 0),
            'size_formatted' => $data['size'] ?? null,
            'comments_count' => (int) ($data['comments_count'] ?? 0),
            'approved_at' => $data['date_uploaded'] ?? null,
            'scraped_at' => now(),
            'uploader_id' => null,
            'uploader' => $data['uploader'] ?? null,
            'status' => $data['status'] ?? 'pending', // Add default status
        ];
    }

    /**
     * Boot model to auto-generate slug on create
     */
    protected static function booted(): void
    {
        static::creating(function ($torrent) {
            if (empty($torrent->slug)) {
                $torrent->slug = Str::slug($torrent->name) . '-' . Str::random(8);
            }
        });

        // Also handle updates to regenerate slug if name changes
        static::updating(function ($torrent) {
            if ($torrent->isDirty('name') && empty($torrent->slug)) {
                $torrent->slug = Str::slug($torrent->name) . '-' . Str::random(8);
            }
        });
    }

    // ---------------- Relationships ----------------

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

    public function files(): HasMany
    {
        return $this->hasMany(TorrentFile::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function downloadHistory(): HasMany
    {
        return $this->hasMany(DownloadHistory::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function detail(): HasOne
    {
        return $this->hasOne(TorrentDetail::class);
    }

    // ---------------- Query Scopes ----------------

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePopular($query, $minSeeders = 10)
    {
        return $query->where('seeders', '>=', $minSeeders);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ---------------- Accessors & Mutators ----------------

    protected function health(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function () {
                $total = $this->seeders + $this->leechers;
                if ($total === 0) {
                    return 0;
                }
                return round(($this->seeders / $total) * 100, 2);
            }
        );
    }

    // ---------------- Helper Methods ----------------

    /**
     * Check if torrent is healthy (has good seed/leech ratio)
     */
    public function isHealthy(): bool
    {
        return $this->health > 50 && $this->seeders > 0;
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSize(): string
    {
        if ($this->size_formatted) {
            return $this->size_formatted;
        }

        if (!$this->size_bytes) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = $this->size_bytes;
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }


    public function screenshots()
    {
        return $this->hasMany(TorrentScreenshot::class);
    }

    public function mediaInfo()
    {
        return $this->hasMany(TorrentMediaInfo::class);
    }

    public function scopeWithDetails($query)
    {
        return $query->whereNotNull('detail_scraped_at');
    }

    public function scopeNeedsDetailScraping($query)
    {
        return $query->whereNull('detail_scraped_at')
                    ->whereNotNull('torrent_link');
    }

    // Check if torrent has been scraped for details recently
    public function needsDetailUpdate($hours = 24)
    {
        return !$this->detail_scraped_at || 
               $this->detail_scraped_at->diffInHours(now()) > $hours;
    }
}