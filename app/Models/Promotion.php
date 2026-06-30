<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'libelle',
        'mobile',
        'date_debut',
        'date_fin',
        'image',
        'statut',
        'description',
        'etablissement_id',
        'created_by',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];

    /**
     * Relation avec l'établissement
     */
    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class, 'etablissement_id');
    }

    /**
     * Relation avec l'utilisateur créateur
     */
    public function createdBy()
    {
        return $this->belongsTo(Super::class, 'created_by');
    }

    /**
     * Scope pour les promotions actives
     */
    public function scopeActive($query)
    {
        return $query->where('statut', 1);
    }

    /**
     * Scope pour les promotions en cours
     */
    public function scopeEnCours($query)
    {
        return $query->where('date_debut', '<=', now())
                    ->where('date_fin', '>=', now());
    }
}
