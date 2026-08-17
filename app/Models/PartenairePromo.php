<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartenairePromo extends Model
{
    use HasFactory;

    protected $table = 'partenaires_promo';

    protected $fillable = [
        'nom',
        'email',
        'telephone',
        'adresse',
        'statut',
        'created_by',
    ];

    protected $casts = [
        'statut' => 'integer',
    ];

    public function codePromo()
    {
        return $this->hasOne(CodePromo::class, 'partenaire_promo_id');
    }

    public function codesPromo()
    {
        return $this->hasMany(CodePromo::class, 'partenaire_promo_id');
    }
}
