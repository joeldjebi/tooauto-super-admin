<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicule extends Model
{
    use HasFactory;

    protected $table = 'vehicules';

    protected $fillable = [
        'matricule',
        'carte_grise',
        'photos',
        'user_id',
        'type_de_vehicule_id',
        'marque_id',
        'modele',
        'type_de_carburant_id',
        'couleur',
        'statut',
        'gestionnaire_de_flotte_id',
        'couleur_vehicule_id',
        'qrcode_generate_id',
        'created_by',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec les alertes
     */
    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    /**
     * Relation avec le type de véhicule
     */
    public function typeDeVehicule()
    {
        return $this->belongsTo(Type_de_vehicule::class, 'type_de_vehicule_id');
    }

    /**
     * Relation avec la marque
     */
    public function marque()
    {
        return $this->belongsTo(Marque::class, 'marque_id');
    }

    /**
     * Relation avec le type de carburant
     */
    public function typeDeCarburant()
    {
        return $this->belongsTo(Type_de_Carburant::class, 'type_de_carburant_id');
    }
}
