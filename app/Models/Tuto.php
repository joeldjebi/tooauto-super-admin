<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tuto extends Model
{
    use HasFactory;

    protected $table = 'tutos';

    protected $fillable = [
        'libelle',
        'url_tuto',
        'categorie_tuto_id',
    ];

    public function categorie_tuto()
    {
        return $this->belongsTo(Categorie_tuto::class, 'categorie_tuto_id');
    }
}
