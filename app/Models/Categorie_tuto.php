<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorie_tuto extends Model
{
    use HasFactory;

    protected $table = 'categorie_tutos';

    protected $fillable = [
        'libelle',
    ];

    public function tutos()
    {
        return $this->hasMany(Tuto::class, 'categorie_tuto_id');
    }
}
