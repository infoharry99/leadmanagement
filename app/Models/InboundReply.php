<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InboundReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'email_message_id',
        'campaign_id',
        'from_email',
        'subject',
        'body',
        'ai_classification',
        'ai_confidence',
        'ai_summary',
        'is_reviewed',
        'reviewed_by_id',
        'status',
        'received_at',
    ];

    protected $casts = [
        'is_reviewed' => 'boolean',
        'ai_confidence' => 'float',
        'received_at' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(EmailMessage::class, 'email_message_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_id');
    }

    public function handover(): HasOne
    {
        return $this->hasOne(ManagerHandover::class);
    }
}
