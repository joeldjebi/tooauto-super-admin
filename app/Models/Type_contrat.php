<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Offre;

class Type_contrat extends Model
{
    use HasFactory;
    protected $table = 'type_de_contrats';
    protected $fillable = ['libelle'];

    public function offres()
    {
        return $this->hasMany(Offre::class);
    }
}