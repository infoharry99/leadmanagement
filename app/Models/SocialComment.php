<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'social_account_id',
        'platform_post_id',
        'comment_id',
        'author_name',
        'author_id',
        'content',
        'sentiment',
        'status',
        'parent_comment_id',
        'commented_at',
    ];

    protected $casts = [
        'commented_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class, 'social_account_id');
    }
}
