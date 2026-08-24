<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandeLavage extends Model
{
    use HasFactory;

    protected $table = 'demande_lavages';

    protected $guarded = [];

    public function prestataire()
    {
        return $this->belongsTo(PrestataireLavage::class, 'prestataire_lavage_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function commercial()
    {
        return $this->belongsTo(Commercial::class, 'commercial_id');
    }
}
