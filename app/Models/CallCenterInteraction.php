<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallCenterInteraction extends Model
{
    protected $table = 'call_center_interactions';

    protected $fillable = [
        'call_center_user_id',
        'cible_type',
        'cible_id',
        'alert_id',
        'canal',
        'motif',
        'resultat',
        'duree_minutes',
        'commentaire',
        'rappel_le',
        'rappel_fait_at',
    ];

    protected $casts = [
        'rappel_le' => 'datetime',
        'rappel_fait_at' => 'datetime',
        'duree_minutes' => 'integer',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(CallCenterUser::class, 'call_center_user_id');
    }
}
