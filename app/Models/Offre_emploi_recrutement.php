<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offre_emploi_recrutement extends Model
{
    use HasFactory;

    protected $table = 'offre_emploi_recrutements';

    protected $fillable = [
        'titre',
        'ordre',
        'categorie',
        'description',
        'missions',
        'profil_rechercher',
        'competence_requises',
        'prerequis',
    ];

    protected $casts = [
        'ordre' => 'integer',
    ];
}