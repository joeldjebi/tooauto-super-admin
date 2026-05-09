<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnonceConcessionnaire extends Model
{
    use HasFactory;

    protected $table = 'annonce_concessionnaires';

    protected $fillable = [
        'type_de_demande_id',
        'type_de_vehicule_id',
        'type_de_piece_id',
        'gestionnaire_de_flotte_id',
        'marque_id',
        'modele',
        'user_id',
        'concessionaire_id',
        'statut',
    ];

    /**
     * Relation avec le type de demande
     */
    public function typeDeDemande()
    {
        return $this->belongsTo(Type_de_demande::class, 'type_de_demande_id');
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
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec le concessionnaire
     */
    public function concessionnaire()
    {
        return $this->belongsTo(Concessionnaire::class, 'concessionaire_id');
    }

    /**
     * Relation avec le type de pièce
     */
    public function typeDePiece()
    {
        return $this->belongsTo(Type_de_piece::class, 'type_de_piece_id');
    }

    /**
     * Relation avec le gestionnaire de flotte
     */
    public function gestionnaireDeFlotte()
    {
        return $this->belongsTo(GestionnaireDeFlotte::class, 'gestionnaire_de_flotte_id');
    }
}
