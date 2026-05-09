<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntrepriseAssurance extends Model
{
    use HasFactory;

    protected $table = 'entreprises_assurances';

    protected $fillable = [
        'nom',
        'logo',
        'situation_geographique',
        'lien_map',
        'site_internet',
        'telephones',
    ];

    protected $casts = [
        'telephones' => 'array',
    ];
}
