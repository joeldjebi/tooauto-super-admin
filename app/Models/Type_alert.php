<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Type_alert extends Model
{
    use HasFactory;

    protected $table = 'type_alerts';

    protected $fillable = [
        'libelle',
        'description',
        'statut',
    ];

    /**
     * Relation avec les alertes
     */
    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }
}
