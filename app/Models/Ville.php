<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ville extends Model
{
    use HasFactory;
    protected $table = 'villes';

    protected $fillable = [
        'libelle',
        'code',
        'statut',
        'pays_id',
    ];

    /**
     * Relation avec le pays
     */
    public function pays()
    {
        return $this->belongsTo(Pays::class, 'pays_id');
    }

    /**
     * Relation avec les communes
     */
    public function communes()
    {
        return $this->hasMany(Commune::class);
    }

    /**
     * Relation avec les sapeurs-pompiers
     */
    public function sapeurs_pompiers()
    {
        return $this->hasMany(Sapeur_pompier::class);
    }
}