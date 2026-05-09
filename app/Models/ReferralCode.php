<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralCode extends Model
{
    use HasFactory;
	protected $fillable = [
        'user_id',
        'code',
        // ajoute d'autres champs si nécessaire
    ];
}