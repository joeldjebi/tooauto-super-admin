<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisiteTechnique extends Model
{
    use HasFactory;

    protected $table = 'visite_techniques';

    protected $fillable = [
        'logo',
        'ville_id',
        'commune_id',
        'adresse',
        'contacts',
        'email',
        'adresse_map',
        'statut'
    ];

    protected $casts = [
        'statut' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec la ville
     */
    public function ville()
    {
        return $this->belongsTo(Ville::class, 'ville_id');
    }

    /**
     * Relation avec la commune
     */
    public function commune()
    {
        return $this->belongsTo(Commune::class, 'commune_id');
    }
}
