<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SequenceEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'followup_sequence_id',
        'lead_id',
        'current_step_id',
        'status',
        'next_run_at',
        'last_sent_at',
        'stop_reason',
        'metadata',
    ];

    protected $casts = [
        'next_run_at' => 'datetime',
        'last_sent_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(FollowupSequence::class, 'followup_sequence_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function currentStep(): BelongsTo
    {
        return $this->belongsTo(SequenceStep::class, 'current_step_id');
    }

    public function stop(string $reason): void
    {
        $this->update([
            'status' => $reason === 'unsubscribed' ? 'unsubscribed_stopped' : ($reason === 'reply' ? 'replied_stopped' : 'cancelled'),
            'stop_reason' => $reason,
            'next_run_at' => null,
        ]);
    }
}
