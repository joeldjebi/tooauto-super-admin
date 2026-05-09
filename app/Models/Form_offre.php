<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Form_offre extends Model
{
    use HasFactory;

    protected $table = 'form_offres';

    protected $fillable = [
        'offre_emploi_id',
        'nom',
        'prenoms',
        'email',
        'mobile',
        'lm',
        'cv'
    ];

    /**
     * Relation avec l'offre d'emploi
     */
    public function offre_emploi()
    {
        return $this->belongsTo(Offre_emploi::class, 'offre_emploi_id');
    }
}

