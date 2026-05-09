<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marque extends Model
{
    use HasFactory;

    protected $table = 'marques';

    protected $fillable = [
        'libelle',
        'description',
        'logo',
        'statut',
    ];

    /**
     * Relation avec les annonces concessionnaires
     */
    public function annonceConcessionnaires()
    {
        return $this->hasMany(AnnonceConcessionnaire::class);
    }

    /**
     * Relation avec les véhicules
     */
    public function vehicules()
    {
        return $this->hasMany(Vehicule::class);
    }
}
