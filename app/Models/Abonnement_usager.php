<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Abonnement_usager extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'forfait_id',
        'date_debut',
        'date_fin',
        'statut',
        'is_free'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function forfait_usager()
    {
        return $this->belongsTo(Forfait_usager::class, 'forfait_id');
    }

}