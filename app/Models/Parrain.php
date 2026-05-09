<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parrain extends Model
{
    use HasFactory;
    protected $table = 'parrains';

    protected $fillable = [
        'code',
        'commercial_id',
    ];

    /**
     * Relation avec le commercial
     */
    public function commercial()
    {
        return $this->belongsTo(Commercial::class, 'commercial_id');
    }
}