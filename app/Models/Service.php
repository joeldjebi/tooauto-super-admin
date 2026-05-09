<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class);
    }

    public function professionnel()
    {
        return $this->belongsTo(Professionnel::class, 'created_by');
    }
}