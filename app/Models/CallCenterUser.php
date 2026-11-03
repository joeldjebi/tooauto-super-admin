<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class CallCenterUser extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_AGENT = 'agent';
    public const ROLE_SUPERVISEUR = 'superviseur';

    public const ROLES = [
        self::ROLE_AGENT => 'Agent',
        self::ROLE_SUPERVISEUR => 'Superviseur',
    ];

    protected $table = 'call_center_users';

    protected $fillable = [
        'nom',
        'prenoms',
        'mobile',
        'email',
        'role',
        'password',
        'statut',
        'created_by_super_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'statut' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    public function isSuperviseur(): bool
    {
        return $this->role === self::ROLE_SUPERVISEUR;
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role ?? self::ROLE_AGENT] ?? self::ROLES[self::ROLE_AGENT];
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->prenoms ?? '') . ' ' . ($this->nom ?? ''));
    }
}
