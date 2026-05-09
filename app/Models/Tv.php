<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tv extends Model
{
    use HasFactory;

    protected $table = 'tvs';
    protected $fillable = ['libelle', 'url', 'categorie_tv_id'];

    public function categorie_tv()
    {
        return $this->belongsTo(Categorie_tv::class);
    }
}