<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForfaitAvantageUsager extends Model
{
    use HasFactory;

    protected $table = 'forfait_avantage_usagers';

    protected $fillable = [
        'avantages',
        'available',
    ];

    public function forfaitUsagers()
    {
        return $this->hasMany(Forfait_usager::class, 'forfait_avantage_usager_id');
    }
}
