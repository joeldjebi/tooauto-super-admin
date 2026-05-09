<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concessionnaire extends Model
{
    use HasFactory;

    protected $table = 'concessionnaires';

    protected $fillable = [
        'name',
        'email',
        'telephone',
        'adresse',
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
     * Relation avec les véhicules concessionnaires
     */
    public function vehicules()
    {
        return $this->hasMany(VehiculeConcessionnaire::class, 'concessionnaire_id');
    }
}
