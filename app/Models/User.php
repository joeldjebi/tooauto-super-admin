<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nom',
        'prenoms',
        'email',
        'telephone',
        'password',
        'commercial_id',
        'fcm_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'activated_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Comptes à compter comme « usagers » dans les statistiques :
     * - ont un numéro de mobile ;
     * - ne sont pas des clients créés par un lavage qui ne se sont jamais connectés à l'app usager.
     */
    public function scopeComptesUsagers($query)
    {
        return $query->whereNotNull('users.mobile')
            ->whereRaw("TRIM(users.mobile) <> ''")
            ->where(function ($q) {
                $q->whereNull('users.station_de_lavage_id')
                    ->orWhereNotNull('users.activated_at');
            });
    }

    /**
     * Comptes créés par une station de lavage
     */
    public function scopeCreesParLavage($query)
    {
        return $query->whereNotNull('users.station_de_lavage_id');
    }

    /**
     * Client créé par un lavage qui ne s'est pas encore connecté à l'app usager
     */
    public function estClientLavageNonActive(): bool
    {
        return ! empty($this->station_de_lavage_id) && empty($this->activated_at);
    }

    /**
     * Relation avec les incidents
     */
    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }

    /**
     * Relation avec le commercial
     */
    public function commercial()
    {
        return $this->belongsTo(Commercial::class, 'commercial_id');
    }

    /**
     * Relation avec les abonnements usagers
     */
    public function abonnementUsagers()
    {
        return $this->hasMany(Abonnement_usager::class, 'user_id');
    }

    public function vehicules()
    {
        return $this->hasMany(Vehicule::class, 'user_id');
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class, 'user_id');
    }

    public function annonces()
    {
        return $this->hasMany(Annonce::class, 'usager_id');
    }

    /**
     * Entités ayant pu créer l'usager
     */
    public function stationService()
    {
        return $this->belongsTo(Station_service::class, 'station_service_id');
    }

    public function stationDeLavage()
    {
        return $this->belongsTo(Station_de_lavage::class, 'station_de_lavage_id');
    }

    public function professionnel()
    {
        return $this->belongsTo(Professionnel::class, 'professionnel_id');
    }

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class, 'etablissement_id');
    }

    public function parrain()
    {
        return $this->belongsTo(Parrain::class, 'parrain_id');
    }

    public function qrcodeGenerate()
    {
        return $this->belongsTo(QrcodeGenerate::class, 'qrcode_generate_id');
    }
}
