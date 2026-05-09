<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CabinetExpertise extends Model
{
    use HasFactory;

    protected $table = 'cabinet_expertises';

    protected $fillable = [
        'nom',
        'prenoms',
        'mobile',
        'mobile_secondaire',
        'email',
        'ville_id',
        'commune_id',
        'adresse',
        'longitude',
        'latitude',
        'photo',
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