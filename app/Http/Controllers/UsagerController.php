<?php

namespace App\Http\Controllers;

use App\Models\Super;
use App\Models\User;
use App\Models\Annonce;
use App\Models\Alert;
use App\Models\Vehicule;
use App\Models\AnnonceConcessionnaire;
use App\Models\Type_de_demande;
use App\Models\Type_de_vehicule;
use App\Models\Marque;
use App\Models\Concessionnaire;
use App\Models\Type_alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Redirector; 
use Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UsagerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function indexUsager()
    {
        $data['title'] ='Liste des usagers';
        $data['menu'] ='usager';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        $data["usagers"] = User::orderBy('id', 'desc')->get();

        // dd($data["usagers"]);
        
        return view('usagers.index',$data);
    }

    /**
     * Display the specified resource.
     */
    public function updateUsager(Request $request, $id)
    {
        // Validation des champs
        $request->validate([
            'email' => [
                'required',
                'string',
                'max:200',
                Rule::unique('usagers')->ignore($id), // Ignore l'enregistrement actuel
            ],
            'mobile' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users')->ignore($id), // Ignore l'enregistrement actuel
            ],
            'indicatif' => 'required',
            'nom' => 'required|string',
            'prenoms' => 'required|string',
            'statut' => 'required',
        ]);
    
        // Récupérer la catégorie
        $usager = User::find($id);
        if (!$usager) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Usager introuvable');
            return back();
        }
    
        // Mise à jour du libellé
        $usager->email = html_entity_decode($request->email);
        $usager->indicatif = html_entity_decode($request->indicatif);
        $usager->mobile = html_entity_decode($request->mobile);
        $usager->nom = html_entity_decode($request->nom);
        $usager->prenoms = html_entity_decode($request->prenoms);
        $usager->statut = html_entity_decode($request->statut);
    
        // Sauvegarde des modifications
        if ($usager->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Usager mise à jour avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de la mise à jour');
        }
    
        return back();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function deleteUser($userId)
    {
        // Démarrer une transaction pour garantir l'intégrité des données
        DB::beginTransaction();

        try {
            // Récupérer les annonces de l'utilisateur
            $annonces = Annonce::where('user_id', $userId)->get();
        
            // Marquer les annonces avec l'ancien ID utilisateur
            foreach ($annonces as $annonce) {
                $annonce->deleted_by_user_id = $userId;
                $annonce->usager_id = null; // Détache l'annonce de l'utilisateur
                $annonce->save();
            }

            // Récupérer les alertes de l'utilisateur
            $alerts = Alert::where('user_id', $userId)->get();

            // Marquer les alertes avec l'ancien ID utilisateur
            foreach ($alerts as $alert) {
                $alert->deleted_by_user_id = $userId;
                $alert->usager_id = null; // Détache l'alerte de l'utilisateur
                $alert->save();
            }
        
            // Supprimer l'utilisateur
            $user = User::findOrFail($userId);
            $user->delete();

            // Commit des changements dans la base de données
            DB::commit();

            // Message de succès
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Utilisateur supprimé avec succès.');
        
        } catch (\Exception $e) {
            // En cas d'erreur, annuler la transaction
            DB::rollBack();

            // Message d'erreur
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de la suppression de l\'utilisateur.');
        }

        // Retour à la page précédente
        return back();
    }

    

    /**
     * Display the specified resource.
     */
    public function showUsager(Request $request, $id)
    {
        $data['title'] = 'Détails de l\'usager';
        $data['menu'] = 'usager';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        $data["usager"] = User::find($id);
        if (!$data["usager"]) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Usager introuvable');
            return back();
        }
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        // Récupérer les paramètres de filtrage et pagination
        $annonceFilter = $request->get('annonce_filter', '');
        $vehiculeFilter = $request->get('vehicule_filter', '');
        $alertFilter = $request->get('alert_filter', '');
        $concessionnaireFilter = $request->get('concessionnaire_filter', '');
        
        // Paramètres de pagination personnalisés
        $perPage = $request->get('per_page', 10); // 10 éléments par défaut
        $perPage = in_array($perPage, [5, 10, 25, 50]) ? $perPage : 10; // Sécurité

        // Annonces avec pagination optimisée et filtres
        $annoncesQuery = Annonce::select(['id', 'libelle', 'image', 'description', 'modele', 'mobile', 'is_whatsapp', 'statut', 'created_at'])
            ->where('usager_id', $id);
        if ($annonceFilter) {
            $annoncesQuery->where(function($q) use ($annonceFilter) {
                $q->where('libelle', 'like', '%' . $annonceFilter . '%')
                  ->orWhere('modele', 'like', '%' . $annonceFilter . '%')
                  ->orWhere('description', 'like', '%' . $annonceFilter . '%');
            });
        }
        $data["annonces"] = $annoncesQuery->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'annonces_page')
            ->appends($request->query());

        // Véhicules avec pagination optimisée et filtres
        $vehiculesQuery = Vehicule::select(['id', 'matricule', 'carte_grise', 'photos', 'modele', 'couleur', 'statut', 'created_at'])
            ->where('user_id', $id);
        if ($vehiculeFilter) {
            $vehiculesQuery->where(function($q) use ($vehiculeFilter) {
                $q->where('matricule', 'like', '%' . $vehiculeFilter . '%')
                  ->orWhere('carte_grise', 'like', '%' . $vehiculeFilter . '%')
                  ->orWhere('modele', 'like', '%' . $vehiculeFilter . '%')
                  ->orWhere('couleur', 'like', '%' . $vehiculeFilter . '%');
            });
        }
        $data["vehicules"] = $vehiculesQuery->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'vehicules_page')
            ->appends($request->query());

        // Alertes avec pagination optimisée et filtres
        $alertsQuery = Alert::select(['id', 'vehicule_id', 'type_alert_id', 'date_debut', 'date_fin', 'kilometrage', 'autres', 'created_at'])
            ->with(['vehicule:id,matricule', 'type_alert:id,libelle'])
            ->where('user_id', $id);
        if ($alertFilter) {
            $alertsQuery->where(function($q) use ($alertFilter) {
                $q->where('kilometrage', 'like', '%' . $alertFilter . '%')
                  ->orWhere('autres', 'like', '%' . $alertFilter . '%');
            });
        }
        $data["alerts"] = $alertsQuery->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'alerts_page')
            ->appends($request->query());

        // Demandes concessionnaires avec pagination optimisée et filtres
        $concessionnairesQuery = AnnonceConcessionnaire::select(['id', 'type_de_demande_id', 'type_de_vehicule_id', 'marque_id', 'modele', 'concessionaire_id', 'statut', 'created_at'])
            ->with([
                'typeDeDemande:id,libelle',
                'typeDeVehicule:id,libelle', 
                'marque:id,libelle',
                'concessionnaire:id,name'
            ])
            ->where('user_id', $id);
        if ($concessionnaireFilter) {
            $concessionnairesQuery->where(function($q) use ($concessionnaireFilter) {
                $q->where('modele', 'like', '%' . $concessionnaireFilter . '%');
            });
        }
        $data["annonce_concessionnaires"] = $concessionnairesQuery->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'concessionnaires_page')
            ->appends($request->query());

        // Debug: Vérifier les types d'alertes disponibles
        $data["debug_type_alerts"] = \App\Models\Type_alert::all();
        
        // Debug: Vérifier une alerte spécifique
        if ($data["alerts"]->count() > 0) {
            $firstAlert = $data["alerts"]->first();
            \Log::info('Debug Alert:', [
                'alert_id' => $firstAlert->id,
                'type_alert_id' => $firstAlert->type_alert_id,
                'type_alert_relation' => $firstAlert->type_alert,
                'vehicule_relation' => $firstAlert->vehicule,
            ]);
        }

        return view('usagers.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}