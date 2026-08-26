<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReductionCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'forfait_usager_id',
        'name',
        'discount_type',
        'discount_value',
        'description',
        'statut',
        'created_by',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'statut' => 'integer',
    ];

    public function forfaitUsager()
    {
        return $this->belongsTo(Forfait_usager::class, 'forfait_usager_id');
    }

    public function userCards()
    {
        return $this->hasMany(UserReductionCard::class, 'reduction_card_id');
    }

    public function histories()
    {
        return $this->hasMany(ReductionCardHistory::class, 'reduction_card_id');
    }
}
