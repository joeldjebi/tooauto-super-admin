<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Type_offre extends Model
{
    use HasFactory;
    protected $table = 'type_offres';
    protected $fillable = ['libelle','commentaire', 'post'];

    public function offres()
    {
        return $this->hasMany(Offre_emploi::class);
    }
}