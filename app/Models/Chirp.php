<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chirp extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'message',
        'visible',
        'image'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function commentsCount(): int
    {
        return $this->comments->count();
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function likesCount(): int
    {
        return $this->likes->count();
    }

    public function isLikedByUser($userId): bool
    {
        return $this->likes()->where('user_id', $userId)->where('chirp_id', $this->id)->exists();
    }
}
