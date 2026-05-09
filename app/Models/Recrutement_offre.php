<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recrutement_offre extends Model
{
    use HasFactory;

    protected $table = 'recrutement_offres';

    public function offre()
    {
        return $this->belongsTo(Offre_emploi_recrutement::class, 'offre_id');
    }
}