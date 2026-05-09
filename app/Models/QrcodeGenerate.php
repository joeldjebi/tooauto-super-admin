<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QrcodeGenerate extends Model
{
    use HasFactory;

    protected $table = 'qrcode_generates';

    protected $fillable = [
        'qrcode',
        'is_assigned',
        'assigned_at',
    ];

    protected $casts = [
        'is_assigned' => 'boolean',
        'assigned_at' => 'datetime',
    ];
}

