<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentCalendar extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'scheduled_date',
        'scheduled_time',
        'target_audience',
        'industry',
        'campaign_goal',
        'objective',
        'platform',
        'content_type',
        'content_draft',
        'media_assets',
        'status',
        'assigned_user_id',
        'social_post_id',
        'metadata',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'media_assets' => 'array',
        'metadata' => 'array',
    ];

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function socialPost(): BelongsTo
    {
        return $this->belongsTo(SocialPost::class, 'social_post_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(ContentApproval::class);
    }
}
