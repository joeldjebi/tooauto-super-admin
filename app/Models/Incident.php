<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sapeur_pompier_id',
        'photos',
        'longitude',
        'latitude',
    ];

    // Pas de cast pour photos car nous gérons le JSON manuellement

    /**
     * Obtenir les photos sous forme d'array
     */
    public function getPhotosArray()
    {
        if (is_string($this->photos)) {
            return json_decode($this->photos, true) ?: [];
        }
        return is_array($this->photos) ? $this->photos : [];
    }

    /**
     * Obtenir le nombre de photos
     */
    public function getPhotosCount()
    {
        return count($this->getPhotosArray());
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sapeur_pompier()
    {
        return $this->belongsTo(Sapeur_pompier::class);
    }
}