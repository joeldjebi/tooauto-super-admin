<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    public function deletedByUser()
    {
        return $this->belongsTo(User::class, 'deleted_by_user_id');
    }

    public function currentUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function vehicule()
    {
        return $this->belongsTo(Vehicule::class, 'vehicule_id');
    }

    public function type_alert()
    {
        return $this->belongsTo(\App\Models\Type_alert::class, 'type_alert_id');
    }
	
	public function user()
    {
        return $this->belongsTo(User::class);
    }
	
	public function type_de_vehicule()
    {
        return $this->belongsTo(Type_de_vehicule::class);
    }
	
	public function marque()
    {
        return $this->belongsTo(Marque::class);
    }
	
	public function type_de_carburant()
    {
        return $this->belongsTo(Type_de_Carburant::class);
    }
}