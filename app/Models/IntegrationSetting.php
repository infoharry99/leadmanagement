<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_name',
        'credentials',
        'settings',
        'is_active',
        'health_status',
        'last_health_check_at',
        'last_error_message',
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'last_health_check_at' => 'datetime',
    ];

    public static function getService(string $name): ?self
    {
        return static::where('service_name', $name)->first();
    }
}
