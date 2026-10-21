<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SequenceStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'followup_sequence_id',
        'step_order',
        'phase',
        'delay_days',
        'subject',
        'body_template',
        'email_template_id',
    ];

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(FollowupSequence::class, 'followup_sequence_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }
}
