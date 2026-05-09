<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sapeur_pompier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'password',
        'ville_id',
        'commune_id',
        'adresse',
        'adresse_map',
        'statut',
    ];

    /**
     * Relation avec les incidents
     */
    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }

    /**
     * Relation avec la ville
     */
    public function ville()
    {
        return $this->belongsTo(Ville::class);
    }

    /**
     * Relation avec la commune
     */
    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }
}
