<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Professionnel extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenoms',
        'role',
        'email',
        'mobile',
        'created_by',
        'statut',
    ];

    protected $casts = [
        'statut' => 'integer',
    ];

    public function etablissements()
    {
        return $this->hasMany(Etablissement::class, 'professionnel_id');
    }
}
