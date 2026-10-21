<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'target_platforms',
        'media_urls',
        'status',
        'scheduled_at',
        'published_at',
        'approved_by_id',
        'created_by_id',
        'error_message',
        'hashtags',
    ];

    protected $casts = [
        'target_platforms' => 'array',
        'media_urls' => 'array',
        'hashtags' => 'array',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function postAccounts(): HasMany
    {
        return $this->hasMany(SocialPostAccount::class);
    }
}
