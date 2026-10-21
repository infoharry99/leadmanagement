<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email_template_id',
        'status',
        'scheduled_at',
        'completed_at',
        'target_segment_filter',
        'subject',
        'from_name',
        'from_email',
        'reply_to',
        'rate_limit_per_minute',
        'total_recipients',
        'sent_count',
        'delivered_count',
        'bounced_count',
        'complained_count',
        'reply_count',
        'unsubscribe_count',
        'created_by_id',
    ];

    protected $casts = [
        'target_segment_filter' => 'array',
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(EmailMessage::class, 'campaign_id');
    }
}
