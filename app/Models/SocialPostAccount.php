<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialPostAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'social_post_id',
        'social_account_id',
        'platform_post_id',
        'status',
        'error_message',
        'engagement_metrics',
        'published_at',
    ];

    protected $casts = [
        'engagement_metrics' => 'array',
        'published_at' => 'datetime',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(SocialPost::class, 'social_post_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class, 'social_account_id');
    }
}
