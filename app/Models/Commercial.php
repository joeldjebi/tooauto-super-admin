<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commercial extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenoms',
        'mobile',
        'password',
        'super_id',
        'parrain_id',
        'statut',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec les utilisateurs parrainés
     */
    public function users()
    {
        return $this->hasMany(User::class, 'commercial_id');
    }

    /**
     * Relation avec le super admin
     */
    public function super()
    {
        return $this->belongsTo(Super::class, 'super_id');
    }

    /**
     * Relation avec le code de parrainage
     */
    public function parrain()
    {
        return $this->belongsTo(Parrain::class, 'parrain_id');
    }
}