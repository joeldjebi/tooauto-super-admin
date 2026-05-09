<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehiculeConcessionnaire extends Model
{
    use HasFactory;

    protected $table = 'vehicule_concessionnaires';

    protected $fillable = [
        'name',
        'concessionnaire_id',
        'marque_id',
        'modele',
        'prix',
        'description',
        'photos',
        'fichier',
        'garantie'
    ];

    /**
     * Relation avec le concessionnaire
     */
    public function concessionnaire()
    {
        return $this->belongsTo(Concessionnaire::class, 'concessionnaire_id');
    }

    /**
     * Relation avec la marque
     */
    public function marque()
    {
        return $this->belongsTo(Marque::class, 'marque_id');
    }

    /**
     * Récupérer les photos sous forme de tableau
     */
    public function getPhotosArray()
    {
        if (is_string($this->photos)) {
            return json_decode($this->photos, true) ?: [];
        }
        return is_array($this->photos) ? $this->photos : [];
    }

    /**
     * Récupérer le nombre de photos
     */
    public function getPhotosCount()
    {
        return count($this->getPhotosArray());
    }

    /**
     * Récupérer la première photo
     */
    public function getFirstPhoto()
    {
        $photos = $this->getPhotosArray();
        return !empty($photos) ? $photos[0] : null;
    }

    /**
     * Formater le prix
     */
    public function getFormattedPrice()
    {
        return number_format($this->prix, 0, ',', ' ') . ' FCFA';
    }
}
