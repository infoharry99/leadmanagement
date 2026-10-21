<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagerHandover extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'inbound_reply_id',
        'assigned_manager_id',
        'handover_reason',
        'urgency',
        'status',
        'handoff_notes',
        'acknowledged_at',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function reply(): BelongsTo
    {
        return $this->belongsTo(InboundReply::class, 'inbound_reply_id');
    }

    public function assignedManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_manager_id');
    }
}
