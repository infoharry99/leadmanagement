<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'social_account_id',
        'sender_name',
        'sender_id',
        'message_text',
        'is_inbound',
        'status',
        'received_at',
    ];

    protected $casts = [
        'is_inbound' => 'boolean',
        'received_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class, 'social_account_id');
    }
}
