<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subject',
        'body_html',
        'body_plain',
        'category',
        'variables_schema',
        'created_by_id',
    ];

    protected $casts = [
        'variables_schema' => 'array',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(EmailCampaign::class);
    }

    public function sequenceSteps(): HasMany
    {
        return $this->hasMany(SequenceStep::class);
    }

    public function render(array $data, ?string $unsubscribeUrl = null): array
    {
        $subject = $this->subject;
        $bodyHtml = $this->body_html;
        $bodyPlain = $this->body_plain ?: strip_tags($this->body_html);

        if ($unsubscribeUrl) {
            $data['unsubscribe_url'] = $unsubscribeUrl;
        }

        foreach ($data as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $val = is_scalar($value) ? (string) $value : '';
            $subject = str_replace($placeholder, $val, $subject);
            $bodyHtml = str_replace($placeholder, htmlspecialchars($val, ENT_QUOTES, 'UTF-8'), $bodyHtml);
            $bodyPlain = str_replace($placeholder, $val, $bodyPlain);
        }

        return [
            'subject' => $subject,
            'body_html' => $bodyHtml,
            'body_plain' => $bodyPlain,
        ];
    }
}
