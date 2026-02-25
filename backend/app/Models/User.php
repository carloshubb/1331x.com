<?php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens; // ✅ Import HasApiTokens
class User extends Authenticatable
{
    use HasFactory,HasApiTokens, Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'avatar',
        'bio',
        'role',
        'status',
        'last_seen_at',
        'preferences',
        'upload_count',
        'download_count',
        'ratio',
        'rank',
        'privacy',
        'joindate',
        'birthday',
        'gender',
        'country',        
        'total_uploaded',
        'total_downloaded',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'banned_until' => 'datetime',
        'preferences' => 'array',
        'upload_count' => 'integer',
        'download_count' => 'integer',
        'ratio' => 'decimal:2',
        'total_uploaded' => 'integer',
        'total_downloaded' => 'integer',
    ];

    public function torrents(): HasMany
    {
        return $this->hasMany(Torrent::class, 'uploader_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function downloadHistory(): HasMany
    {
        return $this->hasMany(DownloadHistory::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isModerator(): bool
    {
        return in_array($this->role, ['admin', 'moderator']);
    }

    public function isVip(): bool
    {
        return in_array($this->role, ['admin', 'moderator', 'vip']);
    }
}