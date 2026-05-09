<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Revision_technique extends Model
{
    use HasFactory;

    protected $table = 'revision_techniques';

    protected $fillable = [
        'name',
        'logo',
        'ville_id',
        'commune_id',
        'adresse_map',
        'contact',
        'email',
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