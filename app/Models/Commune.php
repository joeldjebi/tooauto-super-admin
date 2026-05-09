<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commune extends Model
{
    use HasFactory;

    protected $fillable = [
        'libelle',
        'ville_id',
        'code',
        'statut',
    ];

    /**
     * Relation avec la ville
     */
    public function ville()
    {
        return $this->belongsTo(Ville::class);
    }

    /**
     * Relation avec les sapeurs-pompiers
     */
    public function sapeurs_pompiers()
    {
        return $this->hasMany(Sapeur_pompier::class);
    }
}
