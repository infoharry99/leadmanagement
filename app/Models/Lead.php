<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'assigned_user_id',
        'name',
        'email',
        'phone',
        'website',
        'industry',
        'job_title',
        'country',
        'city',
        'lead_source_id',
        'campaign_id',
        'status',
        'consent_basis',
        'consent_source',
        'consent_at',
        'email_engagement_score',
        'last_contacted_at',
        'next_followup_at',
        'notes',
        'custom_fields',
        'is_archived',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'consent_at' => 'datetime',
        'last_contacted_at' => 'datetime',
        'next_followup_at' => 'datetime',
        'is_archived' => 'boolean',
        'email_engagement_score' => 'integer',
    ];

    // Status definitions
    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_INTERESTED = 'interested';
    public const STATUS_QUALIFIED = 'qualified';
    public const STATUS_CONVERTED = 'converted';
    public const STATUS_NOT_INTERESTED = 'not_interested';
    public const STATUS_LOST = 'lost';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_NEW => 'New',
            self::STATUS_CONTACTED => 'Contacted',
            self::STATUS_INTERESTED => 'Interested',
            self::STATUS_QUALIFIED => 'Qualified',
            self::STATUS_CONVERTED => 'Converted',
            self::STATUS_NOT_INTERESTED => 'Not Interested',
            self::STATUS_LOST => 'Lost',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class, 'lead_source_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(LeadTag::class, 'lead_tag_lead');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class)->latest();
    }

    public function notes(): HasMany
    {
        return $this->hasMany(LeadNote::class)->latest();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(LeadTask::class)->orderBy('due_date');
    }

    public function campaignRecipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class);
    }

    public function sequenceEnrollments(): HasMany
    {
        return $this->hasMany(SequenceEnrollment::class);
    }

    public function inboundReplies(): HasMany
    {
        return $this->hasMany(InboundReply::class)->latest();
    }

    public function handovers(): HasMany
    {
        return $this->hasMany(ManagerHandover::class)->latest();
    }

    public function unsubscribes(): HasMany
    {
        return $this->hasMany(Unsubscribe::class);
    }

    // Check if contact is suppressed or disqualified from marketing outreach
    public function isSuppressed(): bool
    {
        return SuppressionList::isSuppressed($this->email);
    }

    public function isEligibleForMarketing(): bool
    {
        if ($this->is_archived || $this->status === self::STATUS_CONVERTED || $this->status === self::STATUS_NOT_INTERESTED || $this->status === self::STATUS_LOST) {
            return false;
        }

        return !$this->isSuppressed();
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_archived', false);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('is_archived', true);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%")
              ->orWhere('industry', 'like', "%{$term}%")
              ->orWhere('job_title', 'like', "%{$term}%")
              ->orWhereHas('company', function ($cq) use ($term) {
                  $cq->where('name', 'like', "%{$term}%");
              });
        });
    }

    public function recordActivity(string $type, string $description, ?int $userId = null, ?array $metadata = null): LeadActivity
    {
        return $this->activities()->create([
            'user_id' => $userId ?? auth()->id(),
            'type' => $type,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}
