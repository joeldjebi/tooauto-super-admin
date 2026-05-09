<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Type_de_demande extends Model
{
    use HasFactory;

    protected $table = 'type_de_demandes';

    protected $fillable = [
        'libelle',
        'description',
        'statut',
    ];

    /**
     * Relation avec les annonces concessionnaires
     */
    public function annonceConcessionnaires()
    {
        return $this->hasMany(AnnonceConcessionnaire::class);
    }
}
