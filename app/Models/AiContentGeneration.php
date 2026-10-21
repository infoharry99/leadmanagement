<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiContentGeneration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'topic',
        'platform',
        'audience',
        'tone',
        'length',
        'service_focus',
        'call_to_action',
        'keywords',
        'prohibited_claims',
        'output_variations',
        'selected_output',
        'provider',
        'metadata',
    ];

    protected $casts = [
        'output_variations' => 'array',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
