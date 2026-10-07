<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Forfait extends Model
{
    use HasFactory;

    protected $table = 'forfait_pros';

    protected $fillable = [
        'nom',
        'duree',
        'prix',
        'reduction_type',
        'reduction',
        'montant_apres_reduction',
        'avantages',
        'statut',
    ];

    protected $casts = [
        'prix' => 'decimal:2',
        'reduction' => 'decimal:2',
        'montant_apres_reduction' => 'decimal:2',
    ];
}
