<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offre_emploi extends Model
{
    use HasFactory;
    protected $table = 'offre_emplois';
    protected $fillable = [
        'description', 'type_offre_id', 'ville_id', 'type_de_contrat_id', 
        'experience', 'salaire', 'competence_requises', 'missions', 
        'profil_rechercher', 'avantages'
    ];

    public function type_contrat()
    {
        return $this->belongsTo(Type_contrat::class, 'type_de_contrat_id');
    }

    public function type_offre()
    {
        return $this->belongsTo(Type_offre::class, 'type_offre_id');
    }

    public function ville()
    {
        return $this->belongsTo(Ville::class, 'ville_id');
    }
    
    /**
     * Relation avec les candidatures
     */
    public function candidatures()
    {
        return $this->hasMany(Form_offre::class, 'offre_emploi_id');
    }
}