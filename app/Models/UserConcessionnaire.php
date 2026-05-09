<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserConcessionnaire extends Model
{
    use HasFactory;

    protected $table = 'userconcessionnaires';

    protected $fillable = [
        'nom',
        'prenoms',
        'indicatif',
        'mobile',
        'email',
        'password',
        'statut'
    ];

    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relation avec les concessionnaires
     */
    public function concessionnaires()
    {
        return $this->hasMany(Concessionnaire::class, 'userconcessionnaire_id');
    }

    /**
     * Accessor pour obtenir le nom complet
     */
    public function getNomCompletAttribute()
    {
        return $this->prenoms . ' ' . $this->nom;
    }

    /**
     * Accessor pour obtenir le téléphone complet
     */
    public function getTelephoneCompletAttribute()
    {
        return $this->indicatif . ' ' . $this->mobile;
    }

    /**
     * Accessor pour obtenir le statut formaté
     */
    public function getStatutFormateAttribute()
    {
        return $this->statut == 1 ? 'Actif' : 'Inactif';
    }

    /**
     * Scope pour les utilisateurs actifs
     */
    public function scopeActifs($query)
    {
        return $query->where('statut', 1);
    }
}
