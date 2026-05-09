<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Annonce extends Model
{
    use HasFactory;

    public function etablissements()
    {
        return $this->belongsToMany(Etablissement::class, 'annonce_etablissements', 'annonce_id', 'etablissement_id')
            ->withPivot('is_visible')
            ->withTimestamps();
    }

    public function deletedByUser()
    {
        return $this->belongsTo(User::class, 'deleted_by_user_id');
    }

    public function currentUser()
    {
        return $this->belongsTo(User::class, 'usager_id');
    }

    public function marque()
    {
        return $this->belongsTo(Marque::class, 'marque_id');
    }

    public function type_de_piece()
    {
        return $this->belongsTo(Type_de_piece::class, 'type_de_piece_id');
    }

}
