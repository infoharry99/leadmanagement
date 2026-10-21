<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuppressionList extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'suppression_list';

    protected $fillable = [
        'email',
        'reason',
        'source',
        'token',
        'ip_address',
        'user_agent',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public static function isSuppressed(string $email): bool
    {
        return static::where('email', strtolower(trim($email)))->exists();
    }

    public static function suppress(
        string $email,
        string $reason = 'unsubscribed',
        string $source = 'system',
        ?string $token = null,
        ?string $notes = null
    ): self {
        $email = strtolower(trim($email));

        return static::firstOrCreate(
            ['email' => $email],
            [
                'reason' => $reason,
                'source' => $source,
                'token' => $token ?? bin2hex(random_bytes(16)),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'notes' => $notes,
                'created_at' => now(),
            ]
        );
    }
}
