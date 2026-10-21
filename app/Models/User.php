<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'status',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return in_array($this->role, ['admin', 'sales_manager']);
    }

    public function isSalesRep(): bool
    {
        return $this->role === 'sales_rep';
    }

    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    public function canAssignLeads(): bool
    {
        return $this->isManager();
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_user_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(LeadTask::class, 'assigned_user_id');
    }

    public function handovers(): HasMany
    {
        return $this->hasMany(ManagerHandover::class, 'assigned_manager_id');
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(AppNotification::class)->latest();
    }
}
