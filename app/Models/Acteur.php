<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Acteur extends Model
{
    use HasFactory;

    protected $table = 'acteurs';

    protected $fillable = [
        'name',
        'logo',
        'siteweb',
    ];
}