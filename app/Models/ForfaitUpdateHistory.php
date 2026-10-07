<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForfaitUpdateHistory extends Model
{
    protected $fillable = [
        'forfait_id',
        'type_forfait',
        'old_values',
        'new_values',
        'updated_by',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];
}
