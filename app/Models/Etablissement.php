<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Etablissement extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'adresse',
        'telephone',
        'email',
        'type_de_prestations',
        'statut',
    ];

    /**
     * Relation avec les types de prestations
     */
    public function typesPrestations()
    {
        return $this->belongsToMany(TypeDePrestation::class, 'etablissement_type_prestations', 'etablissement_id', 'type_prestation_id');
    }
    public function categorieService()
    {
        return $this->belongsTo(Categorie_service::class, 'categorie_service_id');
    }

    /**
     * Relation avec le type d'établissement
     */
    public function typeEtablissement()
    {
        return $this->belongsTo(TypeEtablissement::class, 'type_etablissement_id');
    }

    /**
     * Relation avec le professionnel
     */
    public function professionnel()
    {
        return $this->belongsTo(Professionnel::class, 'professionnel_id');
    }

    /**
     * Relation avec le pays
     */
    public function pays()
    {
        return $this->belongsTo(Pays::class, 'pays_id');
    }

    /**
     * Relation avec la ville
     */
    public function ville()
    {
        return $this->belongsTo(Ville::class, 'ville_id');
    }

    /**
     * Relation avec la commune
     */
    public function commune()
    {
        return $this->belongsTo(Commune::class, 'commune_id');
    }

    /**
     * Relation avec les articles
     */
    public function articles()
    {
        return $this->hasMany(Article::class, 'etablissement_id');
    }

    /**
     * Relation avec les promotions
     */
    public function promotions()
    {
        return $this->hasMany(Promotion::class, 'etablissement_id');
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'etablissement_id');
    }

    public function abonnementsPro()
    {
        return $this->hasMany(Abonnement_pro::class, 'etablissement_id');
    }

    /**
     * Relation avec les annonces de l'établissement
     */
    public function annonces()
    {
        return $this->belongsToMany(Annonce::class, 'annonce_etablissements', 'etablissement_id', 'annonce_id')
            ->withPivot('is_visible')
            ->withTimestamps();
    }

    /**
     * Relation avec le parrain via le code parrainage
     */
    public function parrain()
    {
        return $this->belongsTo(Parrain::class, 'code_parrain', 'code');
    }

    /**
     * Récupérer les libellés des types de prestations
     */
    public function getTypesPrestationsLibelles()
    {
        if (empty($this->type_de_prestations)) {
            return [];
        }

        $ids = json_decode($this->type_de_prestations, true);
        if (!is_array($ids)) {
            return [];
        }

        return TypeDePrestation::whereIn('id', $ids)->pluck('libelle')->toArray();
    }

    /**
     * Récupérer les types de prestations complets
     */
    public function getTypesPrestationsComplets()
    {
        if (empty($this->type_de_prestations)) {
            return [];
        }

        $ids = json_decode($this->type_de_prestations, true);
        if (!is_array($ids)) {
            return [];
        }

        return TypeDePrestation::whereIn('id', $ids)->get();
    }

    /**
     * Récupérer les libellés des types de prestations (version optimisée)
     */
    public function getTypesPrestationsLibellesOptimized($allTypesPrestations)
    {
        if (empty($this->type_de_prestations)) {
            return [];
        }

        $ids = json_decode($this->type_de_prestations, true);
        if (!is_array($ids)) {
            return [];
        }

        $libelles = [];
        foreach ($ids as $id) {
            if (isset($allTypesPrestations[$id])) {
                $libelles[] = $allTypesPrestations[$id]->libelle;
            }
        }

        return $libelles;
    }

    /**
     * Récupérer les types de prestations complets (version optimisée)
     */
    public function getTypesPrestationsCompletsOptimized($allTypesPrestations)
    {
        if (empty($this->type_de_prestations)) {
            return [];
        }

        $ids = json_decode($this->type_de_prestations, true);
        if (!is_array($ids)) {
            return [];
        }

        $types = [];
        foreach ($ids as $id) {
            if (isset($allTypesPrestations[$id])) {
                $types[] = $allTypesPrestations[$id];
            }
        }

        return collect($types);
    }
}
