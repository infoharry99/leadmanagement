<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform',
        'account_name',
        'account_id',
        'avatar_url',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
        'token_expires_at' => 'datetime',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function postAccounts(): HasMany
    {
        return $this->hasMany(SocialPostAccount::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(SocialComment::class)->latest('commented_at');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SocialMessage::class)->latest('received_at');
    }

    public function isTokenExpired(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->isPast();
    }
}
