<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrestataireLavage extends Model
{
    use HasFactory;

    protected $table = 'prestataire_lavages';

    protected $guarded = [];

    public function demandes()
    {
        return $this->hasMany(DemandeLavage::class, 'prestataire_lavage_id');
    }
}
