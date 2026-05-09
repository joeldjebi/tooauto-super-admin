<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RdvConcessionnaire extends Model
{
    use HasFactory;

    protected $table = 'rdv_concessionnaires';

    protected $fillable = [
        'jour',
        'heure',
        'concessionnaire_id',
        'user_id',
        'gestionnaire_de_flotte_id',
        'statut',
        'reponse_concessionnaire'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relation avec le concessionnaire
     */
    public function concessionnaire()
    {
        return $this->belongsTo(Concessionnaire::class, 'concessionnaire_id');
    }

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relation avec le gestionnaire de flotte
     */
    public function gestionnaireDeFlotte()
    {
        return $this->belongsTo(User::class, 'gestionnaire_de_flotte_id');
    }

    /**
     * Accessor pour obtenir le statut formaté
     */
    public function getStatutFormateAttribute()
    {
        switch ($this->statut) {
            case 0:
                return ['text' => 'En attente', 'class' => 'badge-warning'];
            case 1:
                return ['text' => 'Accepté', 'class' => 'badge-success'];
            case 2:
                return ['text' => 'Annulé', 'class' => 'badge-danger'];
            case 3:
                return ['text' => 'Indisponible', 'class' => 'badge-secondary'];
            default:
                return ['text' => 'Inconnu', 'class' => 'badge-light'];
        }
    }

    /**
     * Accessor pour obtenir la date et heure formatées
     */
    public function getDateTimeFormateAttribute()
    {
        return $this->jour . ' à ' . $this->heure;
    }

    /**
     * Scope pour filtrer par statut
     */
    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    /**
     * Scope pour les RDV en attente
     */
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 0);
    }

    /**
     * Scope pour les RDV acceptés
     */
    public function scopeAcceptes($query)
    {
        return $query->where('statut', 1);
    }
}
