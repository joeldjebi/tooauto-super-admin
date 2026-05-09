<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class CallCenterUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'call_center_users';

    protected $fillable = [
        'nom',
        'prenoms',
        'mobile',
        'email',
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

    public function getFullNameAttribute(): string
    {
        return trim(($this->prenoms ?? '') . ' ' . ($this->nom ?? ''));
    }
}
