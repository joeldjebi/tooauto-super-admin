<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nom',
        'prenoms',
        'email',
        'telephone',
        'password',
        'commercial_id',
        'fcm_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Relation avec les incidents
     */
    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }

    /**
     * Relation avec le commercial
     */
    public function commercial()
    {
        return $this->belongsTo(Commercial::class, 'commercial_id');
    }

    /**
     * Relation avec les abonnements usagers
     */
    public function abonnementUsagers()
    {
        return $this->hasMany(Abonnement_usager::class, 'user_id');
    }
}
