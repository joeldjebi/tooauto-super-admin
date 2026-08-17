<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodePromoUtilisation extends Model
{
    use HasFactory;

    protected $table = 'code_promo_utilisations';

    protected $fillable = [
        'code_promo_id',
        'user_id',
        'abonnement_usager_id',
        'forfait_usager_id',
        'paiement_id',
        'montant_initial',
        'montant_reduction',
        'montant_final',
    ];

    protected $casts = [
        'montant_initial' => 'decimal:2',
        'montant_reduction' => 'decimal:2',
        'montant_final' => 'decimal:2',
    ];

    public function codePromo()
    {
        return $this->belongsTo(CodePromo::class, 'code_promo_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function abonnementUsager()
    {
        return $this->belongsTo(Abonnement_usager::class, 'abonnement_usager_id');
    }

    public function forfaitUsager()
    {
        return $this->belongsTo(Forfait_usager::class, 'forfait_usager_id');
    }
}
