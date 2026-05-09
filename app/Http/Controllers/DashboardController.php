<?php

namespace App\Http\Controllers;

use App\Models\Super;
use App\Models\Visite_technique;
use App\Models\Revision_technique;
use App\Models\User;
use App\Models\Abonnement_usager;
use App\Models\Couleur_vehicule;
use App\Models\Contact_util;
use App\Models\Commissariat_inofs;
use App\Models\EntrepriseAssurance;
use App\Models\ForfaitAvantageUsager;
use App\Models\Forfait_usager;
use App\Models\Station_de_lavage;
use App\Models\Categorie_service;
use App\Models\Sous_categorie_service;
use App\Models\Ss_categorie_service;
use App\Models\Acteur;
use App\Models\Incident;
use App\Models\Tv;
use App\Models\Type_docauto;
use App\Models\Forfait;
use App\Models\Type_de_declaration;
use App\Models\Info;
use App\Models\Categorie_tv;
use App\Models\Declaration;
use App\Models\CabinetExpertise;
use App\Models\Marque;
use App\Models\Pays;
use App\Models\Article;
use App\Models\Sapeur_pompier;
use App\Models\Station_service;
use App\Models\Ville;
use App\Models\Usager;
use App\Models\Type_de_prestation;
use App\Models\Abonnement_pro;
use App\Models\Alert;
use App\Models\Annonce;
use App\Models\Commune;
use App\Models\Vehicule;
use App\Models\Type_etablissement;
use App\Models\Etablissement;
use App\Models\Prefecture;
use App\Models\QrcodeGenerate;
use App\Models\Type_alert;
use App\Models\Type_de_Carburant;
use App\Models\Type_de_piece;
use App\Models\Type_de_vehicule;
use App\Models\Concessionnaire;
use App\Models\VehiculeConcessionnaire;
use App\Models\AnnonceConcessionnaire;
use App\Models\RdvConcessionnaire;
use App\Models\UserConcessionnaire;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Redirector;
use Session;
use App\Models\Station;
use Illuminate\Support\Facades\Auth;
use App\Services\WasabiService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    protected $wasabiService;

    public function __construct(WasabiService $wasabiService)
    {
        $this->wasabiService = $wasabiService;
    }
    /**
     * Display a listing of the resource.
     */
    public function dashboard()
    {
        $data['title'] ='Tableau de bord';
        $data['menu'] ='dashboard';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        $data['usagerCount'] = User::count();
        $data['etablissementCount'] = Etablissement::count();
        $data['prefectureCount'] = Prefecture::count();
        $data['alertCount'] = Alert::count();
        $data['annonceCount'] = Annonce::count();
        $data['abonnementUsagerCount'] = Abonnement_usager::count();
        $data['abonnementProCount'] = Abonnement_pro::count();


        return view('dashboard',$data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getCategorieService()
    {
        $data['title'] ='Les catégories de services';
        $data['menu'] ='categorie_service';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["categorie_services"] = Categorie_service::orderBy('id', 'desc')->get()->map(function ($categorie) {
            return $this->attachCategorieServiceImageUrl($categorie);
        });

        $data["sous_categorie_services"] = Sous_categorie_service::with('ssCategorieService')->orderBy('id', 'desc')->get()->map(function ($sousCategorie) {
            return $this->attachSousCategorieServiceImageUrl($sousCategorie);
        });

        $data["ss_categorie_services"] = Ss_categorie_service::orderBy('libelle')->get()->map(function ($ssCategorie) {
            return $this->attachSousCategorieServiceImageUrl($ssCategorie);
        });

        return view('donnees.categorie_service',$data);
    }

    public function getSousCategorieService()
    {
        $data['title'] ='Les sous-catégories de services';
        $data['menu'] ='sous_categorie_service';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data['sous_categorie_services'] = Sous_categorie_service::with('ssCategorieService')->orderBy('id', 'desc')->get()->map(function ($sousCategorie) {
            return $this->attachSousCategorieServiceImageUrl($sousCategorie);
        });

        $data['ss_categorie_services'] = Ss_categorie_service::orderBy('libelle')->get()->map(function ($ssCategorie) {
            return $this->attachSousCategorieServiceImageUrl($ssCategorie);
        });

        return view('donnees.sous_categorie_service', $data);
    }

    public function getSsCategorieService()
    {
        $data['title'] = 'Les SS-catégories de services';
        $data['menu'] = 'ss_categorie_service';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data['ss_categorie_services'] = Ss_categorie_service::orderBy('id', 'desc')->get()->map(function ($ssCategorie) {
            return $this->attachSousCategorieServiceImageUrl($ssCategorie);
        });

        return view('donnees.ss_categorie_service', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeCategorieService(Request $request)
    {
        // Validation des champs
        $request->validate([
            'libelle' => 'required|string|unique:categorie_services',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_pro' => 'nullable',
            'statut' => 'nullable',
            'pro_or_usager' => 'nullable',
            'sous_categorie_service_id' => 'nullable|exists:sous_categorie_services,id',
            'accessible_abonnement_expire' => 'nullable|boolean',
            'visible_par_defaut' => 'nullable|boolean',
            'accessible_en_fonction_de_mon_abonnement_actif' => 'nullable|boolean',
        ]);

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $this->wasabiService->uploadFile(
                $request->file('image'),
                'images/categorie_service',
                'categorie-service'
            );
        }

        $categorie = new Categorie_service();
        $categorie->libelle = html_entity_decode($request->libelle);
        $categorie->image = $imagePath;
        $categorie->statut = $request->statut ?? 1;
        $categorie->is_pro = $request->is_pro;
        $categorie->pro_or_usager = $request->pro_or_usager;
        $categorie->sous_categorie_service_id = !empty($request->sous_categorie_service_id) ? $request->sous_categorie_service_id : null;
        $categorie->accessible_abonnement_expire = $request->accessible_abonnement_expire ?? 0;
        $categorie->visible_par_defaut = $request->visible_par_defaut ?? 0;
        $categorie->accessible_en_fonction_de_mon_abonnement_actif = $request->accessible_en_fonction_de_mon_abonnement_actif ?? 1;

        // Vérification si l'utilisateur a bien été créé
        if ($categorie->save()) {
            // Flash success message
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Catégorie créer avec succès');
            return back();

        } else {
            // Flash error message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue');

            return back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function updateCategorieService(Request $request, $id)
    {
        // Validation des champs
        $request->validate([
            'libelle' => [
                'required',
                'string',
                Rule::unique('categorie_services')->ignore($id), // Ignore l'enregistrement actuel
            ],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_pro' => 'nullable',
            'statut' => 'nullable',
            'pro_or_usager' => 'nullable',
            'sous_categorie_service_id' => 'nullable|exists:sous_categorie_services,id',
            'accessible_abonnement_expire' => 'nullable|boolean',
            'visible_par_defaut' => 'nullable|boolean',
            'accessible_en_fonction_de_mon_abonnement_actif' => 'nullable|boolean',
        ]);

        // Récupérer la catégorie
        $categorie = Categorie_service::find($id);
        if (!$categorie) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Catégorie introuvable');
            return back();
        }

        // Mise à jour du libellé
        $categorie->libelle = html_entity_decode($request->libelle);
        $categorie->is_pro = html_entity_decode($request->is_pro);
        $categorie->statut = $request->statut ?? $categorie->statut;
        $categorie->pro_or_usager = html_entity_decode($request->pro_or_usager);
        $categorie->sous_categorie_service_id = !empty($request->sous_categorie_service_id) ? $request->sous_categorie_service_id : null;
        $categorie->accessible_abonnement_expire = $request->accessible_abonnement_expire ?? 0;
        $categorie->visible_par_defaut = $request->visible_par_defaut ?? 0;
        $categorie->accessible_en_fonction_de_mon_abonnement_actif = $request->accessible_en_fonction_de_mon_abonnement_actif ?? 0;

        // Suppression et mise à jour de l'image si une nouvelle est fournie
        if ($request->hasFile('image')) {
            // Suppression de l'ancienne image
            if (!empty($categorie->image)) {
                $this->wasabiService->deleteFile($categorie->image);
            }

            $categorie->image = $this->wasabiService->uploadFile(
                $request->file('image'),
                'images/categorie_service',
                'categorie-service'
            );
        }

        // Sauvegarde des modifications
        if ($categorie->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Catégorie mise à jour avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de la mise à jour');
        }

        return back();
    }

    public function storeSousCategorieService(Request $request)
    {
        $request->validate([
            'libelle' => 'required|string|unique:sous_categorie_services',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_pro' => 'nullable',
            'statut' => 'nullable',
            'pro_or_usager' => 'nullable',
            'ss_categorie_service_id' => 'nullable|exists:ss_categorie_services,id',
        ]);

        $data['user'] = Super::where(['id' => Auth::user()->id])->first();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->wasabiService->uploadFile(
                $request->file('image'),
                'images/categorie_service',
                'categorie-service'
            );
        }

        $sousCategorie = new Sous_categorie_service();
        $sousCategorie->libelle = html_entity_decode($request->libelle);
        $sousCategorie->image = $imagePath;
        $sousCategorie->statut = $request->statut ?? 1;
        $sousCategorie->is_pro = $request->is_pro;
        $sousCategorie->pro_or_usager = $request->pro_or_usager;
        $sousCategorie->ss_categorie_service_id = !empty($request->ss_categorie_service_id) ? $request->ss_categorie_service_id : null;

        if ($sousCategorie->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Sous-catégorie créée avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue');
        }

        return back();
    }

    public function updateSousCategorieService(Request $request, $id)
    {
        $request->validate([
            'libelle' => [
                'required',
                'string',
                Rule::unique('sous_categorie_services')->ignore($id),
            ],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_pro' => 'nullable',
            'statut' => 'nullable',
            'pro_or_usager' => 'nullable',
            'ss_categorie_service_id' => 'nullable|exists:ss_categorie_services,id',
        ]);

        $sousCategorie = Sous_categorie_service::find($id);
        if (!$sousCategorie) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Sous-catégorie introuvable');
            return back();
        }

        $sousCategorie->libelle = html_entity_decode($request->libelle);
        $sousCategorie->is_pro = html_entity_decode($request->is_pro);
        $sousCategorie->statut = $request->statut ?? $sousCategorie->statut;
        $sousCategorie->pro_or_usager = html_entity_decode($request->pro_or_usager);
        $sousCategorie->ss_categorie_service_id = !empty($request->ss_categorie_service_id) ? $request->ss_categorie_service_id : null;

        if ($request->hasFile('image')) {
            if (!empty($sousCategorie->image)) {
                $this->wasabiService->deleteFile($sousCategorie->image);
            }
            $sousCategorie->image = $this->wasabiService->uploadFile(
                $request->file('image'),
                'images/categorie_service',
                'categorie-service'
            );
        }

        if ($sousCategorie->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Sous-catégorie mise à jour avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de la mise à jour');
        }

        return back();
    }

    public function storeSsCategorieService(Request $request)
    {
        $request->validate([
            'libelle' => 'required|string|unique:ss_categorie_services',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_pro' => 'nullable',
            'statut' => 'nullable',
            'pro_or_usager' => 'nullable',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->wasabiService->uploadFile(
                $request->file('image'),
                'images/categorie_service',
                'ss-categorie-service'
            );
        }

        $ssCategorie = new Ss_categorie_service();
        $ssCategorie->libelle = html_entity_decode($request->libelle);
        $ssCategorie->image = $imagePath;
        $ssCategorie->statut = $request->statut ?? 1;
        $ssCategorie->is_pro = $request->is_pro;
        $ssCategorie->pro_or_usager = $request->pro_or_usager;

        if ($ssCategorie->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'SS-catégorie créée avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue');
        }

        return back();
    }

    public function updateSsCategorieService(Request $request, $id)
    {
        $request->validate([
            'libelle' => [
                'required',
                'string',
                Rule::unique('ss_categorie_services')->ignore($id),
            ],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_pro' => 'nullable',
            'statut' => 'nullable',
            'pro_or_usager' => 'nullable',
        ]);

        $ssCategorie = Ss_categorie_service::find($id);
        if (!$ssCategorie) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'SS-catégorie introuvable');
            return back();
        }

        $ssCategorie->libelle = html_entity_decode($request->libelle);
        $ssCategorie->is_pro = html_entity_decode($request->is_pro);
        $ssCategorie->statut = $request->statut ?? $ssCategorie->statut;
        $ssCategorie->pro_or_usager = html_entity_decode($request->pro_or_usager);

        if ($request->hasFile('image')) {
            if (!empty($ssCategorie->image)) {
                $this->wasabiService->deleteFile($ssCategorie->image);
            }
            $ssCategorie->image = $this->wasabiService->uploadFile(
                $request->file('image'),
                'images/categorie_service',
                'ss-categorie-service'
            );
        }

        if ($ssCategorie->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'SS-catégorie mise à jour avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de la mise à jour');
        }

        return back();
    }

    public function destroySsCategorieService($id)
    {
        $ssCategorie = Ss_categorie_service::find($id);
        if (empty($ssCategorie)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "SS-catégorie introuvable.");
            return back();
        }

        if ($ssCategorie->sousCategorieServices()->count() > 0) {
            session()->flash('type', 'alert-warning');
            session()->flash('message', "Cette SS-catégorie ne peut pas être supprimée car elle est liée à des sous-catégories.");
            return back();
        }

        if (!empty($ssCategorie->image)) {
            $this->wasabiService->deleteFile($ssCategorie->image);
        }

        $ssCategorie->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', "SS-catégorie supprimée avec succès.");
        return back();
    }

    public function destroySousCategorieService($id)
    {
        $sousCategorie = Sous_categorie_service::find($id);
        if (empty($sousCategorie)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Sous-catégorie introuvable.");
            return back();
        }

        if (!empty($sousCategorie->image)) {
            $this->wasabiService->deleteFile($sousCategorie->image);
        }

        $sousCategorie->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', "Sous-catégorie supprimée avec succès.");
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyCategorieService($id)
    {
        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $categorie = Categorie_service::find($id);

        if (empty($categorie)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "categorie introuvable.");
            return back();
        }

        // Supprime l'image associée, si elle existe
        if (!empty($categorie->image)) {
            $this->wasabiService->deleteFile($categorie->image);
        }

        $categorie->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', "categorie supprimé avec succès.");
        return back();
    }

    protected function attachCategorieServiceImageUrl($categorie)
    {
        if (!$categorie) {
            return $categorie;
        }

        $categorie->image = $categorie->image
            ? $this->wasabiService->temporaryUrl($categorie->image)
            : null;

        return $categorie;
    }

    protected function attachSousCategorieServiceImageUrl($sousCategorie)
    {
        if (!$sousCategorie) {
            return $sousCategorie;
        }

        $sousCategorie->image = $sousCategorie->image
            ? $this->wasabiService->temporaryUrl($sousCategorie->image)
            : null;

        return $sousCategorie;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getTypeEtablissement()
    {
        $data['title'] = "Les types d'établissement";
        $data['menu'] ='type_etablissement';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["type_etablissements"] = Type_etablissement::orderBy('id', 'desc')->get();

        return view('donnees.type_etablissement',$data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeTypeEtablissement(Request $request)
    {
        // Validation des champs
        $request->validate([
            'libelle' => 'required|string|unique:type_etablissements',
        ]);

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $type_etablissement = new Type_etablissement();
        $type_etablissement->libelle = html_entity_decode($request->libelle);

        // Vérification si l'utilisateur a bien été créé
        if ($type_etablissement->save()) {
            // Flash success message
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Type établissement créer avec succès');
            return back();

        } else {
            // Flash error message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue');

            return back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function updateTypeEtablissement(Request $request, $id)
    {
        // Validation des champs
        $request->validate([
            'libelle' => [
                'required',
                'string',
                'max:20',
                Rule::unique('type_etablissements')->ignore($id), // Ignore l'enregistrement actuel
            ]
        ]);

        // Récupérer la catégorie
        $type_etablissement = Type_etablissement::find($id);
        if (!$type_etablissement) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Type établissement introuvable');
            return back();
        }

        // Mise à jour du libellé
        $type_etablissement->libelle = html_entity_decode($request->libelle);

        // Sauvegarde des modifications
        if ($type_etablissement->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Type établissement mise à jour avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de la mise à jour');
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyTypeEtablissement($id)
    {
        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $type_etablissement = Type_etablissement::find($id);

        if (empty($type_etablissement)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "type établissement introuvable.");
            return back();
        }

        $type_etablissement->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', "type établissement supprimé avec succès.");
        return back();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getGetForfaitPro()
    {
        $data['title'] = "Les forfait Pro";
        $data['menu'] ='forfait-pro';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["forfaits"] = Forfait::orderBy('id', 'desc')->get();

        return view('donnees.forfait',$data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeGetForfaitPro(Request $request)
    {
        // Validation des champs
        $request->validate([
            'nom' => 'required|string|unique:forfaits',
            'duree' => 'required|numeric',
            'prix' => 'required|numeric',
            'avantages' => 'required|string',
        ]);

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $forfait = new Forfait();
        $forfait->nom = html_entity_decode($request->nom);
        $forfait->duree = html_entity_decode($request->duree);
        $forfait->prix = html_entity_decode($request->prix);
        $forfait->avantages = html_entity_decode($request->avantages);

        // Vérification si l'utilisateur a bien été créé
        if ($forfait->save()) {
            // Flash success message
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Forfair créer avec succès');
            return back();

        } else {
            // Flash error message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue');

            return back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function updateGetForfaitPro(Request $request, $id)
    {
        // Validation des champs
        $request->validate([
            'nom' => [
                'required',
                'string',
                'max:20',
                Rule::unique('forfaits')->ignore($id),
            ],
            'duree' => 'required|numeric',
            'prix' => 'required|numeric',
            'avantages' => 'required|string',
        ]);

        // Récupérer la catégorie
        $forfait = Forfait::find($id);
        if (!$forfait) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Type établissement introuvable');
            return back();
        }

        $forfait->nom = html_entity_decode($request->nom);
        $forfait->duree = html_entity_decode($request->duree);
        $forfait->prix = html_entity_decode($request->prix);
        $forfait->avantages = html_entity_decode($request->avantages);

        // Sauvegarde des modifications
        if ($forfait->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Forfait mise à jour avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de la mise à jour');
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyForfaitPro($id)
    {
        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $forfait = Forfait::find($id);

        if (empty($forfait)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Forfait introuvable.");
            return back();
        }

        $forfait->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', "Forfait supprimé avec succès.");
        return back();
    }


    /**
     * Show the form for creating a new resource.
     */
    public function getMarque()
    {
        $data['title'] = "Les marques de véhicule";
        $data['menu'] ='marque';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["marques"] = Marque::orderBy('id', 'desc')->get();

        return view('donnees.marque',$data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeMarque(Request $request)
    {
        // Uniformiser l'entrée
        $request->merge([
            'libelle' => trim(strtolower($request->libelle)),
        ]);

        // Validation des champs
        $request->validate([
            'libelle' => 'required|string|unique:marques',
        ], [
            'libelle.unique' => 'Ce libellé existe déjà, veuillez en choisir un autre.',
        ]);

        // Vérifier l'utilisateur
        if (!Auth::check()) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        // Enregistrement de la marque
        $marque = new Marque();
        $marque->libelle = html_entity_decode($request->libelle);

        if ($marque->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Marque créée avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue');
        }

        return back();
    }


    /**
     * Display the specified resource.
     */
    public function updateMarque(Request $request, $id)
    {
        // Validation des champs
        $request->validate([
            'libelle' => [
                'required',
                'string',
                'max:20',
                Rule::unique('marques')->ignore($id), // Ignore l'enregistrement actuel
            ]
        ]);

        // Récupérer la catégorie
        $marque = Marque::find($id);
        if (!$marque) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Marque introuvable');
            return back();
        }

        // Mise à jour du libellé
        $marque->libelle = html_entity_decode($request->libelle);

        // Sauvegarde des modifications
        if ($marque->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Marque mise à jour avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de la mise à jour');
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyMarque($id)
    {
        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $marque = Marque::find($id);

        if (empty($marque)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Marque introuvable.");
            return back();
        }

        $marque->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', "Marque supprimé avec succès.");
        return back();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getPays()
    {
        $data['title'] = "Les Pays";
        $data['menu'] ='pays';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["pays"] = Pays::orderBy('id', 'desc')->get();

        return view('donnees.pays',$data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storePays(Request $request)
    {
        // Uniformiser l'entrée
        $request->merge([
            'libelle' => trim(strtolower($request->libelle)),
        ]);

        // Validation des champs
        $request->validate([
            'libelle' => 'required|string|unique:pays',
        ], [
            'libelle.unique' => 'Ce libellé existe déjà, veuillez en choisir un autre.',
        ]);

        // Vérifier l'utilisateur
        if (!Auth::check()) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        // Enregistrement de la Pays
        $pays = new Pays();
        $pays->libelle = html_entity_decode($request->libelle);

        if ($pays->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Pays créée avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue');
        }

        return back();
    }


    /**
     * Display the specified resource.
     */
    public function updatePays(Request $request, $id)
    {
        // Validation des champs
        $request->validate([
            'libelle' => [
                'required',
                'string',
                'max:20',
                Rule::unique('pays')->ignore($id), // Ignore l'enregistrement actuel
            ]
        ]);

        // Récupérer la catégorie
        $pays = Pays::find($id);
        if (!$pays) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Pays introuvable');
            return back();
        }

        // Mise à jour du libellé
        $pays->libelle = html_entity_decode($request->libelle);

        // Sauvegarde des modifications
        if ($pays->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Pays mise à jour avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de la mise à jour');
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyPays($id)
    {
        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $pays = Pays::find($id);

        if (empty($pays)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Pays introuvable.");
            return back();
        }

        $pays->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', "Pays supprimé avec succès.");
        return back();
    }


    /**
     * Show the form for creating a new resource.
     */
    public function getInfo()
    {
        $data['title'] = "Les informations";
        $data['menu'] ='info';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["infos"] = Info::orderBy('id', 'desc')->get();

        return view('donnees.info',$data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeInfo(Request $request)
    {
        // Uniformiser l'entrée
        $request->merge([
            'libelle' => trim(strtolower($request->libelle)),
            'description' => trim(strtolower($request->description)),
        ]);

        // Validation des champs
        $request->validate([
            'libelle' => 'required|string|unique:infos',
            'description' => 'required|string',
        ], [
            'libelle.unique' => 'Ce libellé existe déjà, veuillez en choisir un autre.',
            'description.required' => 'Le champ description est obligatoire.',
        ]);

        // Vérifier l'utilisateur
        if (!Auth::check()) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        // Enregistrement de la Pays
        $info = new Info();
        $info->libelle = html_entity_decode($request->libelle);
        $info->description = html_entity_decode($request->description);

        if ($info->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Info créée avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue');
        }

        return back();
    }


    /**
     * Display the specified resource.
     */
    public function updateInfo(Request $request, $id)
    {
        // Validation des champs
        $request->validate([
            'libelle' => [
                'required',
                'string',
                Rule::unique('infos')->ignore($id), // Ignore l'enregistrement actuel
            ],
            'description' => 'required|string',
        ]);

        // Récupérer la catégorie
        $info = Info::find($id);
        if (!$info) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Info introuvable');
            return back();
        }

        // Mise à jour du libellé
        $info->libelle = html_entity_decode($request->libelle);
        $info->description = html_entity_decode($request->description);

        // Sauvegarde des modifications
        if ($info->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Info mise à jour avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de la mise à jour');
        }

        return back();
    }

        /**
     * Remove the specified resource from storage.
     */
    public function destroyInfo($id)
    {
        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $info = Info::find($id);

        if (empty($info)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Info introuvable.");
            return back();
        }

        $info->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', "Info supprimé avec succès.");
        return back();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getContactUtil()
    {
        $data['title'] = "Les contacts utilisateurs";
        $data['menu'] ='contact_util';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["contact_utils"] = Contact_util::orderBy('id', 'desc')->get();

        return view('donnees.contact_util',$data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeContactUtil(Request $request)
    {
        // Uniformiser l'entrée
        $request->merge([
            'libelle' => trim(strtolower($request->libelle)),
            'description' => trim(strtolower($request->description)),
        ]);

        // Validation des champs
        $request->validate([
            'libelle' => 'required|string|unique:contact_utils',
            'mobile' => 'required|string|unique:contact_utils',
            'email' => 'nullable|email|unique:contact_utils',
            'fix' => 'nullable|string|unique:contact_utils',
            'adresse' => 'nullable|string',
        ], [
            'libelle.required' => 'Le champ libellé est obligatoire.',
            'mobile.required' => 'Le champ mobile est obligatoire.',
            'libelle.unique' => 'Ce libellé existe déjà, veuillez en choisir un autre.',
            'mobile.unique' => 'Ce mobile existe déjà, veuillez en choisir un autre.',
            'email.unique' => 'Cet email existe déjà, veuillez en choisir un autre.',
            'fix.unique' => 'Ce fix existe déjà, veuillez en choisir un autre.',
            'adresse.nullable' => 'Le champ adresse est facultatif.',
        ]);

        // Vérifier l'utilisateur
        if (!Auth::check()) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        // Enregistrement de la Pays
        $contact_util = new Contact_util();
        $contact_util->libelle = html_entity_decode($request->libelle);
        $contact_util->mobile = html_entity_decode($request->mobile);
        $contact_util->email = html_entity_decode($request->email);
        $contact_util->fix = html_entity_decode($request->fix);
        $contact_util->adresse = html_entity_decode($request->adresse);

        if ($contact_util->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Contact util créé avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue');
        }

        return back();
    }


    /**
     * Display the specified resource.
     */
    public function updateContactUtil(Request $request, $id)
    {
        // Validation des champs
        $request->validate([
            'libelle' => [
                'required',
                'string',
                Rule::unique('contact_utils')->ignore($id), // Ignore l'enregistrement actuel
            ],
            'mobile' => 'required|string|unique:contact_utils',
            'email' => 'nullable|email|unique:contact_utils',
            'fix' => 'nullable|string|unique:contact_utils',
            'adresse' => 'nullable|string',
        ], [
            'libelle.required' => 'Le champ libellé est obligatoire.',
        ]);

        // Récupérer la catégorie
        $contact_util = Contact_util::find($id);
        if (!$contact_util) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Contact util introuvable');
            return back();
        }

        // Mise à jour du libellé
        $contact_util->libelle = html_entity_decode($request->libelle);
        $contact_util->mobile = html_entity_decode($request->mobile);
        $contact_util->email = html_entity_decode($request->email);
        $contact_util->fix = html_entity_decode($request->fix);
        $contact_util->adresse = html_entity_decode($request->adresse);

        // Sauvegarde des modifications
        if ($contact_util->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Contact util mise à jour avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de la mise à jour');
        }

        return back();
    }

        /**
     * Remove the specified resource from storage.
     */
    public function destroyContactUtil($id)
    {
        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $contact_util = Contact_util::find($id);

        if (empty($contact_util)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Contact util introuvable.");
            return back();
        }

        $contact_util->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', "Contact util supprimé avec succès.");
        return back();
    }

    /**
     * Afficher la liste des entreprises d'assurance.
     */
    public function getEntreprisesAssurances()
    {
        $data['title'] = "Les entreprises d'assurance";
        $data['menu'] = 'entreprises_assurances';
        $data['user'] = Auth::user();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        $data['entreprises_assurances'] = EntrepriseAssurance::orderBy('id', 'desc')
            ->get()
            ->map(function ($entreprise) {
                return $this->attachEntrepriseAssuranceLogoUrl($entreprise);
            });

        return view('donnees.entreprises_assurances', $data);
    }

    /**
     * Enregistrer une entreprise d'assurance.
     */
    public function storeEntrepriseAssurance(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:150|unique:entreprises_assurances,nom',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'situation_geographique' => 'nullable|string|max:255',
            'lien_map' => 'nullable|url|max:500',
            'site_internet' => 'nullable|url|max:255',
            'telephone_numbers' => 'required|array|min:1',
            'telephone_numbers.*' => 'required|string|max:30',
            'telephone_types' => 'required|array|min:1',
            'telephone_types.*' => 'required|in:fix,mobile,whatsapp',
        ], [
            'nom.required' => 'Le nom est obligatoire.',
            'nom.unique' => 'Cette entreprise existe déjà.',
            'logo.image' => 'Le logo doit être une image.',
            'lien_map.url' => 'Le lien map doit être une URL valide.',
            'site_internet.url' => 'Le site internet doit être une URL valide.',
            'telephone_numbers.required' => 'Ajoutez au moins un numéro de téléphone.',
            'telephone_numbers.*.required' => 'Chaque numéro de téléphone est obligatoire.',
            'telephone_types.*.in' => 'Le type de téléphone doit être fix, mobile ou whatsapp.',
        ]);

        $entreprise = new EntrepriseAssurance();
        $entreprise->nom = html_entity_decode($request->nom);
        $entreprise->situation_geographique = html_entity_decode($request->situation_geographique);
        $entreprise->lien_map = html_entity_decode($request->lien_map);
        $entreprise->site_internet = html_entity_decode($request->site_internet);
        $entreprise->telephones = $this->formatEntrepriseAssuranceTelephones($request);
        $entreprise->logo = $this->handleEntrepriseAssuranceLogoUpload($request);

        if ($entreprise->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', "Entreprise d'assurance créée avec succès.");
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Une erreur est survenue lors de la création.");
        }

        return back();
    }

    public function getCommissariatInofs()
    {
        $data['title'] = "Commissariat infos";
        $data['menu'] = 'commissariat_inofs';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data['commissariat_inofs'] = Commissariat_inofs::orderBy('id', 'desc')->get();

        return view('donnees.commissariat_inofs', $data);
    }

    public function storeCommissariatInofs(Request $request)
    {
        $request->validate([
            'categorie' => 'required|string|max:100',
            'nom' => 'required|string|max:150',
            'commune' => 'nullable|string|max:100',
            'ville' => 'nullable|string|max:100',
            'situation_geographique' => 'nullable|string|max:255',
            'contacts' => 'required|string',
            'statut' => 'nullable|boolean',
        ]);

        if (!Auth::check()) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        $commissariatInfo = new Commissariat_inofs();
        $commissariatInfo->categorie = $request->categorie;
        $commissariatInfo->nom = html_entity_decode($request->nom);
        $commissariatInfo->commune = html_entity_decode($request->commune);
        $commissariatInfo->ville = html_entity_decode($request->ville ?: 'Abidjan');
        $commissariatInfo->situation_geographique = html_entity_decode($request->situation_geographique);
        $commissariatInfo->contacts = $this->formatCommissariatInofsContacts($request->contacts);
        $commissariatInfo->statut = $request->boolean('statut', true);

        if ($commissariatInfo->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Commissariat info créé avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue');
        }

        return back();
    }

    public function updateCommissariatInofs(Request $request, $id)
    {
        $request->validate([
            'categorie' => 'required|string|max:100',
            'nom' => 'required|string|max:150',
            'commune' => 'nullable|string|max:100',
            'ville' => 'nullable|string|max:100',
            'situation_geographique' => 'nullable|string|max:255',
            'contacts' => 'required|string',
            'statut' => 'nullable|boolean',
        ]);

        $commissariatInfo = Commissariat_inofs::find($id);
        if (!$commissariatInfo) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Commissariat info introuvable');
            return back();
        }

        $commissariatInfo->categorie = $request->categorie;
        $commissariatInfo->nom = html_entity_decode($request->nom);
        $commissariatInfo->commune = html_entity_decode($request->commune);
        $commissariatInfo->ville = html_entity_decode($request->ville ?: 'Abidjan');
        $commissariatInfo->situation_geographique = html_entity_decode($request->situation_geographique);
        $commissariatInfo->contacts = $this->formatCommissariatInofsContacts($request->contacts);
        $commissariatInfo->statut = $request->boolean('statut');

        if ($commissariatInfo->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Commissariat info mis à jour avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de la mise à jour');
        }

        return back();
    }

    public function destroyCommissariatInofs($id)
    {
        $commissariatInfo = Commissariat_inofs::find($id);

        if (!$commissariatInfo) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Commissariat info introuvable');
            return back();
        }

        if ($commissariatInfo->delete()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Commissariat info supprimé avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de la suppression');
        }

        return back();
    }

    private function formatCommissariatInofsContacts($contacts): array
    {
        return collect(preg_split('/[\r\n,\/]+/', $contacts))
            ->map(fn ($contact) => trim($contact))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Mettre à jour une entreprise d'assurance.
     */
    public function updateEntrepriseAssurance(Request $request, $id)
    {
        $request->validate([
            'nom' => [
                'required',
                'string',
                'max:150',
                Rule::unique('entreprises_assurances', 'nom')->ignore($id),
            ],
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'situation_geographique' => 'nullable|string|max:255',
            'lien_map' => 'nullable|url|max:500',
            'site_internet' => 'nullable|url|max:255',
            'telephone_numbers' => 'required|array|min:1',
            'telephone_numbers.*' => 'required|string|max:30',
            'telephone_types' => 'required|array|min:1',
            'telephone_types.*' => 'required|in:fix,mobile,whatsapp',
        ]);

        $entreprise = EntrepriseAssurance::find($id);

        if (empty($entreprise)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Entreprise d'assurance introuvable.");
            return back();
        }

        $entreprise->nom = html_entity_decode($request->nom);
        $entreprise->situation_geographique = html_entity_decode($request->situation_geographique);
        $entreprise->lien_map = html_entity_decode($request->lien_map);
        $entreprise->site_internet = html_entity_decode($request->site_internet);
        $entreprise->telephones = $this->formatEntrepriseAssuranceTelephones($request);

        if ($request->hasFile('logo')) {
            $this->deleteEntrepriseAssuranceLogo($entreprise->logo);
            $entreprise->logo = $this->handleEntrepriseAssuranceLogoUpload($request);
        }

        if ($entreprise->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', "Entreprise d'assurance mise à jour avec succès.");
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Une erreur est survenue lors de la mise à jour.");
        }

        return back();
    }

    /**
     * Supprimer une entreprise d'assurance.
     */
    public function destroyEntrepriseAssurance($id)
    {
        $entreprise = EntrepriseAssurance::find($id);

        if (empty($entreprise)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Entreprise d'assurance introuvable.");
            return back();
        }

        $this->deleteEntrepriseAssuranceLogo($entreprise->logo);
        $entreprise->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', "Entreprise d'assurance supprimée avec succès.");
        return back();
    }

    private function formatEntrepriseAssuranceTelephones(Request $request): array
    {
        $numbers = $request->input('telephone_numbers', []);
        $types = $request->input('telephone_types', []);
        $telephones = [];

        foreach ($numbers as $index => $number) {
            $number = trim((string) $number);
            $type = $types[$index] ?? null;

            if ($number === '' || !in_array($type, ['fix', 'mobile', 'whatsapp'], true)) {
                continue;
            }

            $telephones[] = [
                'numero' => $number,
                'type' => $type,
            ];
        }

        return $telephones;
    }

    private function handleEntrepriseAssuranceLogoUpload(Request $request): ?string
    {
        if (!$request->hasFile('logo')) {
            return null;
        }

        return $this->wasabiService->uploadFile(
            $request->file('logo'),
            'images/entreprises_assurances',
            'logo'
        );
    }

    private function deleteEntrepriseAssuranceLogo(?string $logo): void
    {
        if (!empty($logo)) {
            $this->wasabiService->deleteFile($logo);
        }
    }

    private function attachEntrepriseAssuranceLogoUrl($entreprise)
    {
        if (!$entreprise) {
            return $entreprise;
        }

        $entreprise->logo_url = $this->getEntrepriseAssuranceLogoUrl($entreprise->logo);

        return $entreprise;
    }

    private function getEntrepriseAssuranceLogoUrl(?string $logo): ?string
    {
        if (empty($logo)) {
            return null;
        }

        if (filter_var($logo, FILTER_VALIDATE_URL)) {
            return $logo;
        }

        return $this->wasabiService->temporaryUrl($logo);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getCategorieTv()
    {
        $data['title'] = "Les catégories de TV";
        $data['menu'] ='categorie_tv';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["categorie_tv"] = Categorie_tv::orderBy('id', 'desc')->get();

        return view('donnees.categorie_tv',$data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeCategorieTv(Request $request)
    {
        // Uniformiser l'entrée
        $request->merge([
            'libelle' => trim(strtolower($request->libelle)),
        ]);

        // Validation des champs
        $request->validate([
            'libelle' => 'required|string|unique:categorie_tvs',
        ], [
            'libelle.unique' => 'Ce libellé existe déjà, veuillez en choisir un autre.',
        ]);

        // Vérifier l'utilisateur
        if (!Auth::check()) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        // Enregistrement de la Pays
        $categorie_tv = new Categorie_tv();
        $categorie_tv->libelle = html_entity_decode($request->libelle);

        if ($categorie_tv->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Categorie TV créée avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue');
        }

        return back();
    }


    /**
     * Display the specified resource.
     */
    public function updateCategorieTv(Request $request, $id)
    {
        // Validation des champs
        $request->validate([
            'libelle' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categorie_tvs')->ignore($id), // Ignore l'enregistrement actuel
            ]
        ]);

        // Récupérer la catégorie
        $categorie_tv = Categorie_tv::find($id);
        if (!$categorie_tv) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Categorie TV introuvable');
            return back();
        }

        // Mise à jour du libellé
        $categorie_tv->libelle = html_entity_decode($request->libelle);

        // Sauvegarde des modifications
        if ($categorie_tv->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Categorie TV mise à jour avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de la mise à jour');
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyCategorieTv($id)
    {
        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $categorie_tv = Categorie_tv::find($id);

        if (empty($categorie_tv)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Categorie TV introuvable.");
            return back();
        }

        $categorie_tv->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', "Categorie TV supprimé avec succès.");
        return back();
    }


    /**
     * Show the form for creating a new resource.
     */
    public function getTv()
    {
        $data['title'] = "Les TV";
        $data['menu'] ='tv';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["categorie_tvs"] = Categorie_tv::orderBy('id', 'desc')->get();
        $data["tvs"] = Tv::orderBy('id', 'desc')
        ->with('categorie_tv')
        ->get();

        return view('donnees.tv',$data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeTv(Request $request)
    {
        // Normaliser l'entrée
        $request->merge([
            'libelle' => trim(ucwords($request->libelle)), // Capitalisation
            'url' => trim(strtolower($request->url)),
        ]);

        // Validation des champs
        $request->validate([
            'libelle' => 'required|string|unique:tvs',
            'url' => 'required|string|unique:tvs',
            'categorie_tv' => 'required|exists:categorie_tvs,id', // Correction de la règle
        ], [
            'libelle.unique' => 'Ce libellé existe déjà, veuillez en choisir un autre.',
            'libelle.required' => 'Le champ libellé est obligatoire.',
            'categorie_tv.exists' => 'Categorie TV introuvable.', // Correction du message
            'categorie_tv.required' => 'Le champ categorie TV est obligatoire.',
        ]);

        // Enregistrement de la ville
        $tv = new Tv();
        $tv->libelle = html_entity_decode($request->libelle);
        $tv->categorie_tv_id = $request->categorie_tv;
        $tv->url = $request->url;

        if ($tv->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Tv créée avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue');
        }

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function updateTv(Request $request, $id)
    {
        // Validation des champs
        $request->validate([
            'libelle' => [
                'required',
                'string',
                Rule::unique('tvs')->ignore($id), // Ignore l'enregistrement actuel
            ],
            'categorie_tv' => 'required|exists:categorie_tvs,id',
            'url' => 'required|string',
        ]);

        // Récupérer la catégorie
        $tv = Tv::find($id);
        if (!$tv) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Tv introuvable');
            return back();
        }

        // Mise à jour du libellé
        $tv->libelle = html_entity_decode($request->libelle);
        $tv->categorie_tv_id = $request->categorie_tv;
        $tv->url = $request->url;

        // Sauvegarde des modifications
        if ($tv->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Tv mise à jour avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de la mise à jour');
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyTv($id)
    {
        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $tv = Tv::find($id);

        if (empty($tv)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Tv introuvable.");
            return back();
        }

        $tv->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', "Tv supprimé avec succès.");
        return back();
    }


    /**
     * Show the form for creating a new resource.
     */
    public function getVille()
    {
        $data['title'] = "Les villes";
        $data['menu'] ='ville';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["pays"] = Pays::orderBy('id', 'desc')->get();
        $data["villes"] = Ville::orderBy('id', 'desc')
        ->with('pays')
        ->get();

        return view('donnees.ville',$data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeVille(Request $request)
    {
        // Normaliser l'entrée
        $request->merge([
            'libelle' => trim(ucwords($request->libelle)), // Capitalisation
        ]);

        // Validation des champs
        $request->validate([
            'libelle' => 'required|string|unique:villes',
            'pays' => 'required|exists:pays,id', // Correction de la règle
        ], [
            'libelle.unique' => 'Ce libellé existe déjà, veuillez en choisir un autre.',
            'libelle.required' => 'Le champ libellé est obligatoire.',
            'pays.exists' => 'Pays introuvable.', // Correction du message
            'pays.required' => 'Le champ pays est obligatoire.',
        ]);

        // Enregistrement de la ville
        $ville = new Ville();
        $ville->libelle = html_entity_decode($request->libelle);
        $ville->pays_id = $request->pays;

        if ($ville->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Ville créée avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue');
        }

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function updateVille(Request $request, $id)
    {
        // Validation des champs
        $request->validate([
            'libelle' => [
                'required',
                'string',
                'max:20',
                Rule::unique('villes')->ignore($id), // Ignore l'enregistrement actuel
            ],
            'pays' => 'required|exists:pays,id',
        ]);

        // Récupérer la catégorie
        $ville = Ville::find($id);
        if (!$ville) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'ville introuvable');
            return back();
        }

        // Mise à jour du libellé
        $ville->libelle = html_entity_decode($request->libelle);

        // Sauvegarde des modifications
        if ($ville->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Ville mise à jour avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de la mise à jour');
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyVille($id)
    {
        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $ville = Ville::find($id);

        if (empty($ville)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Ville introuvable.");
            return back();
        }

        $ville->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', "Ville supprimé avec succès.");
        return back();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getCommune()
    {
        $data['title'] = "Les communes";
        $data['menu'] ='commune';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["villes"] = Ville::orderBy('id', 'desc')->get();
        $data["communes"] = Commune::orderBy('id', 'desc')
        ->with('ville')
        ->get();

        return view('donnees.commune',$data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeCommune(Request $request)
    {
        // Normaliser l'entrée
        $request->merge([
            'nom' => trim(ucwords($request->nom)), // Capitalisation
        ]);

        // Validation des champs
        $request->validate([
            'nom' => 'required|string|unique:communes',
            'ville' => 'required|exists:villes,id', // Correction de la règle
        ], [
            'nom.unique' => 'Ce libellé existe déjà, veuillez en choisir un autre.',
            'nom.required' => 'Le champ libellé est obligatoire.',
            'ville.exists' => 'Ville introuvable.', // Correction du message
            'ville.required' => 'Le champ ville est obligatoire.',
        ]);

        // Enregistrement de la ville
        $commune = new Commune();
        $commune->nom = html_entity_decode($request->nom);
        $commune->ville_id = $request->ville;

        if ($commune->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Commune créée avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue');
        }

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function updateCommune(Request $request, $id)
    {
        // Validation des champs
        $request->validate([
            'nom' => [
                'required',
                'string',
                'max:20',
                Rule::unique('communes')->ignore($id), // Ignore l'enregistrement actuel
            ],
            'ville' => 'required|exists:villes,id',
        ]);

        // Récupérer la catégorie
        $commune = Commune::find($id);
        if (!$commune) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'commune introuvable');
            return back();
        }

        // Mise à jour du libellé
        $commune->nom = html_entity_decode($request->nom);

        // Sauvegarde des modifications
        if ($commune->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Commune mise à jour avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de la mise à jour');
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyCommune($id)
    {
        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $commune = Commune::find($id);

        if (empty($commune)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Commune introuvable.");
            return back();
        }

        $commune->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', "Commune supprimé avec succès.");
        return back();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getTypeDePiece()
    {
        $data['title'] = "Les types de pièce";
        $data['menu'] ='type_de_piece';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["type_de_pieces"] = Type_de_piece::orderBy('id', 'desc')->get();

        return view('donnees.type_de_piece',$data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeTypeDePiece(Request $request)
    {
        // Validation des champs
        $request->validate([
            'libelle' => 'required|string|unique:type_de_pieces',
        ]);

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $type_de_piece = new Type_de_piece();
        $type_de_piece->libelle = html_entity_decode($request->libelle);

        // Vérification si l'utilisateur a bien été créé
        if ($type_de_piece->save()) {
            // Flash success message
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Type de pièce créer avec succès');
            return back();

        } else {
            // Flash error message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue');

            return back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function updateTypeDePiece(Request $request, $id)
    {
        // Validation des champs
        $request->validate([
            'libelle' => [
                'required',
                'string',
                'max:20',
                Rule::unique('type_de_pieces')->ignore($id), // Ignore l'enregistrement actuel
            ]
        ]);

        // Récupérer la catégorie
        $type_de_piece = Type_de_piece::find($id);
        if (!$type_de_piece) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Type de pièce introuvable');
            return back();
        }

        // Mise à jour du libellé
        $type_de_piece->libelle = html_entity_decode($request->libelle);

        // Sauvegarde des modifications
        if ($type_de_piece->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Type de pièce mise à jour avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de la mise à jour');
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyTypeDePiece($id)
    {
        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $typedepiece = Type_de_piece::find($id);

        if (empty($typedepiece)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Type de pièce introuvable.");
            return back();
        }

        $typedepiece->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', "Type de pièce supprimé avec succès.");
        return back();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getTypeDeCarburant()
    {
        $data['title'] = "Les types de carburant";
        $data['menu'] ='type_de_carburant';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["type_de_carburants"] = Type_de_Carburant::orderBy('id', 'desc')->get();

        return view('donnees.type_de_carburant',$data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeTypeDeCarburant(Request $request)
    {
        // Validation des champs
        $request->validate([
            'libelle' => 'required|string|unique:type_de_carburants',
        ]);

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $type_de_carburant = new Type_de_carburant();
        $type_de_carburant->libelle = html_entity_decode($request->libelle);

        // Vérification si l'utilisateur a bien été créé
        if ($type_de_carburant->save()) {
            // Flash success message
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Type de carburant créer avec succès');
            return back();

        } else {
            // Flash error message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue');

            return back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function updateTypeDeCarburant(Request $request, $id)
    {
        // Validation des champs
        $request->validate([
            'libelle' => [
                'required',
                'string',
                'max:20',
                Rule::unique('type_de_carburants')->ignore($id), // Ignore l'enregistrement actuel
            ]
        ]);

        // Récupérer la catégorie
        $type_de_carburant = Type_de_carburant::find($id);
        if (!$type_de_carburant) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Type de carburant introuvable');
            return back();
        }

        // Mise à jour du libellé
        $type_de_carburant->libelle = html_entity_decode($request->libelle);

        // Sauvegarde des modifications
        if ($type_de_carburant->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Type de carburant mise à jour avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de la mise à jour');
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyTypeDecarburant($id)
    {
        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $type_de_carburant = Type_de_carburant::find($id);

        if (empty($type_de_carburant)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Type de carburant introuvable.");
            return back();
        }

        $type_de_carburant->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', "Type de carburant supprimé avec succès.");
        return back();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getTypeAlert()
    {
        $data['title'] = "Les types alerte";
        $data['menu'] ='type_alert';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["type_alerts"] = Type_alert::orderBy('id', 'desc')->get();

        return view('donnees.type_alert',$data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeTypeAlert(Request $request)
    {
        // Validation des champs
        $request->validate([
            'libelle' => 'required|string|unique:type_alerts',
        ]);

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $type_alert = new Type_alert();
        $type_alert->libelle = html_entity_decode($request->libelle);

        // Vérification si l'utilisateur a bien été créé
        if ($type_alert->save()) {
            // Flash success message
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Type alerte créer avec succès');
            return back();

        } else {
            // Flash error message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue');

            return back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function updateTypeAlert(Request $request, $id)
    {
        // Validation des champs
        $request->validate([
            'libelle' => [
                'required',
                'string',
                'max:20',
                Rule::unique('type_alerts')->ignore($id), // Ignore l'enregistrement actuel
            ]
        ]);

        // Récupérer la catégorie
        $type_alert = Type_alert::find($id);
        if (!$type_alert) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Type de pièce introuvable');
            return back();
        }

        // Mise à jour du libellé
        $type_alert->libelle = html_entity_decode($request->libelle);

        // Sauvegarde des modifications
        if ($type_alert->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Type de pièce mise à jour avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de la mise à jour');
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyTypeAlert($id)
    {
        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $type_alert = Type_alert::find($id);

        if (empty($type_alert)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Type de d'alerte introuvable.");
            return back();
        }

        $type_alert->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', "Type de d'alerte supprimé avec succès.");
        return back();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getTypeDeVehicule()
    {
        $data['title'] = "Les types de véhicule";
        $data['menu'] ='type_de_vehicule';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["type_de_vehicules"] = Type_de_vehicule::orderBy('id', 'desc')->get();

        return view('donnees.type_de_vehicule',$data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeTypeDeVehicule(Request $request)
    {
        // Validation des champs
        $request->validate([
            'libelle' => 'required|string|unique:type_de_vehicules',
        ]);

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $type_de_vehicule = new Type_de_vehicule();
        $type_de_vehicule->libelle = html_entity_decode($request->libelle);

        // Vérification si l'utilisateur a bien été créé
        if ($type_de_vehicule->save()) {
            // Flash success message
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Type alerte créer avec succès');
            return back();

        } else {
            // Flash error message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue');

            return back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function updateTypeDeVehicule(Request $request, $id)
    {
        // Validation des champs
        $request->validate([
            'libelle' => [
                'required',
                'string',
                'max:20',
                Rule::unique('type_de_vehicules')->ignore($id), // Ignore l'enregistrement actuel
            ]
        ]);

        // Récupérer la catégorie
        $type_de_vehicule = Type_de_vehicule::find($id);
        if (!$type_de_vehicule) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Type de pièce introuvable');
            return back();
        }

        // Mise à jour du libellé
        $type_de_vehicule->libelle = html_entity_decode($request->libelle);

        // Sauvegarde des modifications
        if ($type_de_vehicule->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Type de véhicule mise à jour avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de la mise à jour');
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyTypeDeVehicule($id)
    {
        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $type_de_vehicule = Type_de_vehicule::find($id);

        if (empty($type_de_vehicule)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Type de de véhicule introuvable.");
            return back();
        }

        $type_de_vehicule->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', "Type de de véhicule supprimé avec succès.");
        return back();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getTypeDeDeclaration()
    {
        $data['title'] = "Les types de déclaration";
        $data['menu'] ='type_de_declaration';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["type_de_declarations"] = Type_de_declaration::orderBy('id', 'desc')->get();

        return view('donnees.type_de_declaration',$data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeTypeDeDeclaration(Request $request)
    {
        // Validation des champs
        $request->validate([
            'libelle' => 'required|string|unique:type_de_declarations',
        ]);

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $type_de_declaration = new Type_de_declaration();
        $type_de_declaration->libelle = html_entity_decode($request->libelle);

        // Vérification si l'utilisateur a bien été créé
        if ($type_de_declaration->save()) {
            // Flash success message
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Type de déclaration créer avec succès');
            return back();

        } else {
            // Flash error message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue');

            return back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function updateTypeDeDeclaration(Request $request, $id)
    {
        // Validation des champs
        $request->validate([
            'libelle' => [
                'required',
                'string',
                'max:300',
                Rule::unique('type_de_declarations')->ignore($id), // Ignore l'enregistrement actuel
            ]
        ]);

        // Récupérer la catégorie
        $type_de_declaration = Type_de_declaration::find($id);
        if (!$type_de_declaration) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Type de pièce introuvable');
            return back();
        }

        // Mise à jour du libellé
        $type_de_declaration->libelle = html_entity_decode($request->libelle);

        // Sauvegarde des modifications
        if ($type_de_declaration->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Type de déclaration mise à jour avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de la mise à jour');
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyTypeDeDeclaration($id)
    {
        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $type_de_declaration = Type_de_declaration::find($id);

        if (empty($type_de_declaration)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Type de déclaration introuvable.");
            return back();
        }

        $type_de_declaration->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', "Type de déclation supprimé avec succès.");
        return back();
    }


    /**
     * Show the form for creating a new resource.
     */
    public function getAllAnnonce()
    {
        $data['title'] = "Les annonces";
        $data['menu'] ='annonces';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["annonces"] = Annonce::orderBy('id', 'desc')
        ->with('marque', 'currentUser', 'type_de_piece')
        ->get();
        // dd($data["annonces"]);
        return view('annonce.index',$data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getAllArticle()
    {
        $data['title'] = "Les articles";
        $data['menu'] ='articles';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["articles"] = Article::orderBy('id', 'desc')
        ->with('etablissement')
        ->get();
        // dd($data["article"]);
        return view('article.index',$data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getAllVehicule()
    {
        $data['title'] = "Les vehicules";
        $data['menu'] ='vehicules';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["vehicules"] = vehicule::orderBy('id', 'desc')
        ->with('user', 'type_de_vehicule', 'marque', 'type_de_carburant')
        ->get();
        // dd($data["article"]);
        return view('vehicule.index',$data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getAllAlert(Request $request)
    {
        $data['title'] = "Les alertes";
        $data['menu'] ='alertes';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        // Récupérer les paramètres de filtrage et pagination
        $userFilter = $request->get('user_filter', '');
        $vehiculeFilter = $request->get('vehicule_filter', '');
        $typeAlertFilter = $request->get('type_alert_filter', '');
        $dateDebutFilter = $request->get('date_debut_filter', '');
        $dateFinFilter = $request->get('date_fin_filter', '');

        // Paramètres de pagination personnalisés
        $perPage = $request->get('per_page', 15); // 15 éléments par défaut
        $perPage = in_array($perPage, [5, 10, 15, 25, 50]) ? $perPage : 15; // Sécurité

        // Requête optimisée avec pagination et filtres
        $alertesQuery = Alert::select(['id', 'user_id', 'vehicule_id', 'type_alert_id', 'date_debut', 'date_fin', 'kilometrage', 'autres', 'created_at'])
            ->with([
                'user:id,nom,prenoms,email',
                'vehicule:id,matricule,modele',
                'type_alert:id,libelle'
            ])
            ->orderBy('created_at', 'desc');

        // Filtres
        if ($userFilter) {
            $alertesQuery->whereHas('user', function($q) use ($userFilter) {
                $q->where('nom', 'like', '%' . $userFilter . '%')
                  ->orWhere('prenoms', 'like', '%' . $userFilter . '%')
                  ->orWhere('email', 'like', '%' . $userFilter . '%');
            });
        }

        if ($vehiculeFilter) {
            $alertesQuery->whereHas('vehicule', function($q) use ($vehiculeFilter) {
                $q->where('matricule', 'like', '%' . $vehiculeFilter . '%')
                  ->orWhere('modele', 'like', '%' . $vehiculeFilter . '%');
            });
        }

        if ($typeAlertFilter) {
            $alertesQuery->whereHas('type_alert', function($q) use ($typeAlertFilter) {
                $q->where('libelle', 'like', '%' . $typeAlertFilter . '%');
            });
        }

        if ($dateDebutFilter) {
            $alertesQuery->whereDate('date_debut', '>=', $dateDebutFilter);
        }

        if ($dateFinFilter) {
            $alertesQuery->whereDate('date_fin', '<=', $dateFinFilter);
        }

        $data["alertes"] = $alertesQuery->paginate($perPage)->appends($request->query());

        // Récupérer tous les types d'alertes pour le filtre
        $data["types_alerts"] = \App\Models\Type_alert::select('id', 'libelle')->orderBy('libelle')->get();

        return view('alert.index',$data);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function toggleStatusUsager(Request $request)
    {
        $usager = User::find($request->usager_id);

        if (!$usager) {
            return response()->json([
                'success' => false,
                'message' => 'Usager introuvable.'
            ], 404);
        }

        // Inverser le statut
        $usager->statut = !$usager->statut;
        $usager->save();

        return response()->json([
            'success' => true,
            'message' => $usager->statut
                ? 'Usager activé avec succès.'
                : 'Usager désactivé avec succès.',
            'statut' => $usager->statut
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function getTypeDePrestation()
    {
        $data['title'] ='Les types de prestations';
        $data['menu'] ='type_de_prestation';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["type_de_prestations"] = Type_de_prestation::orderBy('id', 'desc')->get();

        return view('donnees.type_de_prestation',$data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeTypeDePrestation(Request $request)
    {
        // Validation des champs
        $request->validate([
            'libelle' => 'required|string|unique:type_de_prestations',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $imagePath = null;

        if($request->file('image')) {
            $image = $request->file('image');
            $imageName = 'image-' . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/type_de_prestation'), $imageName);
            $imagePath = $imageName;
        }

        $typeDePrestation = new Type_de_prestation();
        $typeDePrestation->libelle = html_entity_decode($request->libelle);
        $typeDePrestation->image = $imagePath;

        // Vérification si l'utilisateur a bien été créé
        if ($typeDePrestation->save()) {
            // Flash success message
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Type de prestation créer avec succès');
            return back();

        } else {
            // Flash error message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue');

            return back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function updateTypeDePrestation(Request $request, $id)
    {
        // Validation des champs
        $request->validate([
            'libelle' => [
                'required',
                'string',
                Rule::unique('type_de_prestations')->ignore($id), // Ignore l'enregistrement actuel
            ],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Image devient optionnelle
        ]);

        // Récupérer la catégorie
        $typeDePrestation = Type_de_prestation::find($id);
        if (!$typeDePrestation) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Catégorie introuvable');
            return back();
        }

        // Mise à jour du libellé
        $typeDePrestation->libelle = html_entity_decode($request->libelle);

        // Suppression et mise à jour de l'image si une nouvelle est fournie
        if ($request->hasFile('image')) {
            // Suppression de l'ancienne image
            if ($typeDePrestation->image && file_exists(public_path('images/type_de_prestation/' . $typeDePrestation->image))) {
                unlink(public_path('images/type_de_prestation/' . $typeDePrestation->image));
            }

            // Enregistrement de la nouvelle image
            $image = $request->file('image');
            $imageName = 'image-' . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/type_de_prestation'), $imageName);
            $typeDePrestation->image = $imageName;
        }

        // Sauvegarde des modifications
        if ($typeDePrestation->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Type de prestation mise à jour avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de la mise à jour');
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyTypeDePrestation($id)
    {
        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $typeDePrestation = Type_de_prestation::find($id);

        if (empty($typeDePrestation)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Type de prestation introuvable.");
            return back();
        }

        // Supprime l'image associée, si elle existe
        if ($typeDePrestation->image && file_exists(public_path('images/type_de_prestation/' . $typeDePrestation->image))) {
            unlink(public_path('images/type_de_prestation/' . $typeDePrestation->image));
        }

        $typeDePrestation->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', "Type de prestation supprimé avec succès.");
        return back();
    }


    /**
     * Show the form for creating a new resource.
     */
    public function getAbonnementPro()
    {
        $data['title'] = "Les abonnements pro";
        $data['menu'] ='abonnementpro';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["abonnementPros"] = Abonnement_pro::orderBy('id', 'desc')
        ->with('etablissement', 'forfait')
        ->get();
        // dd($data["abonnementPros"]);
        return view('abonnement.pro',$data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getAbonnementUsager()
    {
        $data['title'] = "Les abonnements usager";
        $data['menu'] ='abonnementusager';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["abonnement_usagers"] = Abonnement_usager::orderBy('id', 'desc')
        ->with('user', 'forfait_usager')
        ->get();

        // Debug: Vérifier les données
        if ($data["abonnement_usagers"]->isNotEmpty()) {
            $firstAbonnement = $data["abonnement_usagers"]->first();
            \Log::info('Debug Abonnement:', [
                'abonnement_id' => $firstAbonnement->id,
                'forfait_id' => $firstAbonnement->forfait_id,
                'forfait_usager' => $firstAbonnement->forfait_usager,
                'duree' => $firstAbonnement->forfait_usager ? $firstAbonnement->forfait_usager->duree : 'NULL'
            ]);
        }
        return view('abonnement.usager',$data);
    }

    /**
     * Display a listing of the resource.
     */
    public function indexSapeurPompier()
    {
        $data['title'] ='Les sapeur pompier';
        $data['menu'] ='sapeur_pompier';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["sapeur_pompiers"] = Sapeur_pompier::orderBy('id', 'desc')
        ->with('ville', 'commune')
        ->get();

        $data["villes"] = Ville::all();
        $data["communes"] = Commune::all();

        return view('sapeur_pompier.index',$data);
    }

    public function getIncidentsSapeurPompier($id)
    {
        $data['title'] ='Les incidents';
        $data['menu'] ='incidents';

        $data["incidents"] = Incident::where('sapeur_pompier_id', $id)
        ->with('user', 'sapeur_pompier.ville', 'sapeur_pompier.commune')
        ->get();

        return view('sapeur_pompier.incidents',$data);
    }

    /**
     * Afficher les détails d'un incident via AJAX
     */
    public function getIncidentDetails($id)
    {
        $incident = Incident::with('user', 'sapeur_pompier.ville', 'sapeur_pompier.commune')->findOrFail($id);

        $html = '
        <div class="row">
            <div class="col-md-6">
                <h6><i class="nav-icon i-User"></i> Informations utilisateur</h6>
                <p><strong>Nom:</strong> ' . ($incident->user->nom ?? 'N/A') . ' ' . ($incident->user->prenoms ?? 'N/A') . '</p>
                <p><strong>Email:</strong> ' . ($incident->user->email ?? 'N/A') . '</p>
                <p><strong>Téléphone:</strong> ' . ($incident->user->telephone ?? 'N/A') . '</p>
            </div>
            <div class="col-md-6">
                <h6><i class="nav-icon i-Fire"></i> Sapeur-Pompier assigné</h6>
                <p><strong>Nom:</strong> ' . ($incident->sapeur_pompier->name ?? 'N/A') . '</p>
                <p><strong>Email:</strong> ' . ($incident->sapeur_pompier->email ?? 'N/A') . '</p>
                <p><strong>Téléphone:</strong> ' . ($incident->sapeur_pompier->mobile ?? 'N/A') . '</p>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-12">
                <h6><i class="nav-icon i-Map-Marker"></i> Localisation</h6>
                <p><strong>Coordonnées:</strong> ' . $incident->latitude . ', ' . $incident->longitude . '</p>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-12">
                <h6><i class="nav-icon i-Photo"></i> Photos de l\'incident</h6>';

        $photos = $incident->getPhotosArray();
        if (count($photos) > 0) {
            $html .= '<div class="row">';
            foreach ($photos as $photo) {
                $html .= '
                <div class="col-md-3 mb-3">
                    <img src="https://api-usager.tooauto.com/' . $photo . '"
                         alt="Photo incident"
                         class="img-fluid rounded"
                         style="max-height: 200px; object-fit: cover;"
                         onerror="this.style.display=\'none\'">
                </div>';
            }
            $html .= '</div>';
        } else {
            $html .= '<p class="text-muted">Aucune photo disponible</p>';
        }

        $html .= '
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <h6><i class="nav-icon i-Calendar"></i> Informations temporelles</h6>
                <p><strong>Date de création:</strong> ' . \Carbon\Carbon::parse($incident->created_at)->format('d/m/Y à H:i:s') . '</p>
                <p><strong>Dernière modification:</strong> ' . \Carbon\Carbon::parse($incident->updated_at)->format('d/m/Y à H:i:s') . '</p>
            </div>
        </div>';

        return response()->json(['html' => $html]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeSapeurPompier(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'mobile' => 'required|string|unique:sapeur_pompiers',
            'email' => 'required|string|unique:sapeur_pompiers',
            'ville_id' => 'required|exists:villes,id',
            'commune_id' => 'nullable|exists:communes,id',
            'adresse' => 'nullable',
            'adresse_map' => 'nullable',
        ]);

        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }
        $password = Hash::make($request->mobile);

        $sapeur_pompier = new Sapeur_pompier();

        $sapeur_pompier->name = $request->name;
        $sapeur_pompier->email = $request->email;
        $sapeur_pompier->mobile = $request->mobile;
        $sapeur_pompier->password = $password;
        $sapeur_pompier->ville_id = $request->ville_id;
        $sapeur_pompier->commune_id = $request->commune_id;
        $sapeur_pompier->adresse = $request->adresse;
        $sapeur_pompier->adresse_map = $request->adresse_map;

        if ($sapeur_pompier->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', "Sapeur pompier créé avec succès.");
            return back();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateSapeurPompier(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'mobile' => 'required|string|unique:sapeur_pompiers,mobile,' . $id,
            'email' => 'required|string|unique:sapeur_pompiers,email,' . $id,
            'ville_id' => 'required|exists:villes,id',
            'commune_id' => 'nullable|exists:communes,id',
            'adresse' => 'nullable|string',
            'adresse_map' => 'nullable|string',
        ]);

        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $sapeur_pompier = Sapeur_pompier::find($id);

        if (empty($sapeur_pompier)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Sapeur pompier est introuvable.");
            return back();
        }

        $sapeur_pompier->name = $request->name;
        $sapeur_pompier->email = $request->email;
        $sapeur_pompier->mobile = $request->mobile;
        $sapeur_pompier->ville_id = $request->ville_id;
        $sapeur_pompier->commune_id = $request->commune_id;
        $sapeur_pompier->adresse = $request->adresse;
        $sapeur_pompier->adresse_map = $request->adresse_map;

        if ($sapeur_pompier->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', "Sapeur pompier mise à jour avec succès.");
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroySapeurPompier($id)
    {
        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $sapeur_pompier = Sapeur_pompier::find($id);

        if (empty($sapeur_pompier)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Sapeur pompier est introuvable.");
            return back();
        }

        $sapeur_pompier->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', "Sapeur pompier supprimé avec succès.");
        return back();
    }


    /**
     * Display a listing of the resource.
     */
    public function indexStationService()
    {
        $data['title'] ='Les sapeur pompier';
        $data['menu'] ='station_service';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["station_services"] = Station_service::orderBy('id', 'desc')
        ->with('ville', 'commune')
        ->get();

        $data["villes"] = Ville::all();
        $data["communes"] = Commune::all();

        return view('donnees.station_service',$data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeStationService(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:100',
                'mobile' => 'required|string',
                'email' => 'required|string',
                'ville_id' => 'required|exists:villes,id',
                'commune_id' => 'nullable|exists:communes,id',
                'adresse' => 'nullable',
                'adresse_map' => 'nullable',
                'borne_electrique' => 'nullable',
            ]);

            $data['user'] = Auth::user();
            if (empty($data['user'])) {
                session()->flash('type', 'alert-danger');
                session()->flash('message', "L'utilisateur est introuvable.");
                return back();
            }

            $station_service = new Station_service();

            $station_service->name = $request->name;
            $station_service->email = $request->email;
            $station_service->mobile = $request->mobile;
            $station_service->ville_id = $request->ville_id;
            $station_service->commune_id = $request->commune_id;
            $station_service->adresse = $request->adresse;
            $station_service->adresse_map = $request->adresse_map;
            $station_service->borne_electrique = $request->borne_electrique;

            if ($station_service->save()) {
                session()->flash('type', 'alert-success');
                session()->flash('message', "Station service créé avec succès.");
                return back();
            } else {
                session()->flash('type', 'alert-danger');
                session()->flash('message', "Une erreur s'est produite lors de la création de la station service.");
                return back();
            }
        } catch (\Exception $e) {
            // Gère les erreurs inattendues
            \Log::error('Erreur lors de la création de la station service: ' . $e->getMessage());
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Une erreur inattendue s'est produite.");
            return back();
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function updateStationService(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'mobile' => 'required|string|unique:sapeur_pompiers,mobile,' . $id,
            'email' => 'required|string|unique:sapeur_pompiers,email,' . $id,
            'ville_id' => 'required|exists:villes,id',
            'commune_id' => 'nullable|exists:communes,id',
            'adresse' => 'nullable|string',
            'adresse_map' => 'nullable|string',
            'borne_electrique' => 'nullable|string',
        ]);

        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $station_service = Station_service::find($id);

        if (empty($station_service)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Station service est introuvable.");
            return back();
        }

        $station_service->name = $request->name;
        $station_service->email = $request->email;
        $station_service->mobile = $request->mobile;
        $station_service->ville_id = $request->ville_id;
        $station_service->commune_id = $request->commune_id;
        $station_service->adresse = $request->adresse;
        $station_service->adresse_map = $request->adresse_map;
        $station_service->borne_electrique = $request->borne_electrique;

        if ($station_service->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', "Station service mise à jour avec succès.");
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyStationService($id)
    {
        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $station_service = Station_service::find($id);

        if (empty($station_service)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Station service est introuvable.");
            return back();
        }

        $station_service->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', "Station service supprimé avec succès.");
        return back();
    }


    /**
     * Show the form for creating a new resource.
     */
    public function getTypeDocAuto()
    {
        $data['title'] ='Les types de documents auto';
        $data['menu'] = 'typedocauto';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["typedocautos"] = Type_docauto::orderBy('id', 'desc')->get();

        return view('donnees.typedocauto',$data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeTypeDocAuto(Request $request)
    {
        // Validation des champs
        $request->validate([
            'libelle' => 'required|string|unique:type_docautos',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $imagePath = null;

        if($request->file('image')) {
            $image = $request->file('image');
            $imageName = 'image-' . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/typedocautos'), $imageName);
            $imagePath = $imageName;
        }

        $typedocauto = new Type_docauto();
        $typedocauto->libelle = html_entity_decode($request->libelle);
        $typedocauto->image = $imagePath;

        // Vérification si l'utilisateur a bien été créé
        if ($typedocauto->save()) {
            // Flash success message
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Type docauto créer avec succès');
            return back();

        } else {
            // Flash error message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue');

            return back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function updateTypeDocAuto(Request $request, $id)
    {
        // Validation des champs
        $request->validate([
            'libelle' => [
                'required',
                'string',
                Rule::unique('type_docautos')->ignore($id), // Ignore l'enregistrement actuel
            ],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5048', // Image devient optionnelle
        ]);

        // Récupérer la catégorie
        $typedocauto = Type_docauto::find($id);
        if (!$typedocauto) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Type docauto introuvable');
            return back();
        }

        // Mise à jour du libellé
        $typedocauto->libelle = html_entity_decode($request->libelle);

        // Suppression et mise à jour de l'image si une nouvelle est fournie
        if ($request->hasFile('image')) {
            // Suppression de l'ancienne image
            if ($typedocauto->image && file_exists(public_path('images/typedocautos/' . $typedocauto->image))) {
                unlink(public_path('images/typedocautos/' . $typedocauto->image));
            }

            // Enregistrement de la nouvelle image
            $image = $request->file('image');
            $imageName = 'image-' . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/typedocautos'), $imageName);
            $typedocauto->image = $imageName;
        }

        // Sauvegarde des modifications
        if ($typedocauto->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Type docauto mise à jour avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de la mise à jour');
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyTypeDocAuto($id)
    {
        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $typedocauto = Type_docauto::find($id);

        if (empty($typedocauto)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Type docauto introuvable.");
            return back();
        }

        // Supprime l'image associée, si elle existe
        if ($typedocauto->image && file_exists(public_path('images/typedocautos/' . $typedocauto->image))) {
            unlink(public_path('images/typedocautos/' . $typedocauto->image));
        }

        $typedocauto->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', "Type docauto supprimé avec succès.");
        return back();
    }





    /**
     * Show the form for creating a new resource.
     */
    public function getVisiteTechnique()
    {
        $data['title'] ='Visite technique';
        $data['menu'] ='visite_technique';

        $data['user'] = Prefecture::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

		$data["villes"] = Ville::orderBy('id', 'desc')->get();
		$data["communes"] = Commune::orderBy('id', 'desc')->get();

        $data["visite_techniques"] = Visite_technique::with(['ville', 'commune'])->orderBy('id', 'desc')->get()->map(function ($visite) {
            return $this->attachVisiteTechniqueLogoUrl($visite);
        });

        return view('donnees.visite_technique',$data);
    }

    public function storeVisiteTechnique(Request $request)
	{
		$request->validate([
			'name' => 'required|string|max:100',
			'ville_id' => 'required|exists:villes,id',
			'commune_id' => 'nullable|exists:communes,id',
			'adresse' => 'nullable|string',
			'contacts' => 'nullable|string',
			'email' => 'nullable|email',
			'adresse_map' => 'nullable|string',
			'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
		]);

		$logoPath = $this->handleLogoUpload($request);

		$visite = new Visite_technique([
			'name' => $request->name,
			'ville_id' => $request->ville_id,
			'commune_id' => $request->commune_id,
			'adresse' => $request->adresse,
			'contacts' => $request->contacts,
			'email' => $request->email,
			'adresse_map' => $request->adresse_map,
			'logo' => $logoPath,
		]);

		if ($visite->save()) {
			return back()->with(['type' => 'alert-success', 'message' => 'Visite créée avec succès']);
		}

		return back()->with(['type' => 'alert-danger', 'message' => 'Une erreur est survenue']);
	}

	public function updateVisiteTechnique(Request $request, $id)
	{
		$request->validate([
			'name' => 'required|string|max:100',
			'ville_id' => 'required|exists:villes,id',
			'commune_id' => 'nullable|exists:communes,id',
			'adresse' => 'nullable|string',
			'contacts' => 'nullable|string',
			'email' => 'nullable|email',
			'adresse_map' => 'required|string',
			'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
		]);

		$visite = Visite_technique::findOrFail($id);

		if ($request->hasFile('logo')) {
			$this->deleteLogoIfExists($visite->logo);
			$visite->logo = $this->handleLogoUpload($request);
		}

		$visite->update([
			'name' => $request->name,
			'ville_id' => $request->ville_id,
			'commune_id' => $request->commune_id,
			'adresse' => $request->adresse,
			'contacts' => $request->contacts,
			'email' => $request->email,
			'adresse_map' => $request->adresse_map,
		]);

		return back()->with(['type' => 'alert-success', 'message' => 'Visite mise à jour avec succès']);
	}

	public function destroyVisiteTechnique($id)
	{
		$visite = Visite_technique::findOrFail($id);

		$this->deleteLogoIfExists($visite->logo);

		$visite->delete();

		return back()->with(['type' => 'alert-success', 'message' => 'Visite technique supprimée avec succès']);
	}

	/**
	 * Gère le téléchargement de l'image.
	 */
	private function handleLogoUpload(Request $request): ?string
	{
		if ($request->hasFile('logo')) {
			return $this->wasabiService->uploadFile(
				$request->file('logo'),
				'images/visite_technique',
				'logo'
			);
		}

		return null;
	}

	/**
	 * Supprime l'image si elle existe.
	 */
	private function deleteLogoIfExists(?string $filename): void
	{
		if ($filename) {
			$this->wasabiService->deleteFile($filename);
		}
	}

	private function attachVisiteTechniqueLogoUrl($visite)
	{
		if (!$visite) {
			return $visite;
		}

		$visite->logo = $visite->logo
			? $this->wasabiService->temporaryUrl($visite->logo)
			: null;

		return $visite;
	}

	    /**
     * Display a listing of the resource.
     */
    public function getParrainageForCommerciaux()
    {
        $data['title'] ='Parrainages';
        $data['menu'] ='commerciaux';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        $data['getCommercials'] = User::whereNotNull('commercial_id')
			->where('commercial_id', '!=', 0)
			->with('commercial')
			->get();

        return view('parrainage.commercial',$data);
    }

	/**
     * Display a listing of the resource.
     */
    public function getParrainageForStationService()
    {
        $data['title'] ='Parrainages';
        $data['menu'] ='sation_service';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        $data['getStationsServices'] = User::whereNotNull('station_service_id')
		->where('station_service_id', '!=', 0)
		->with('station_service')
		->get();

        //dd($data['getStationsServices']);
        return view('parrainage.sation_service',$data);
    }


	/**
     * Display a listing of the resource.
     */
    public function getParrainageForStationDeLavage()
    {
        $data['title'] ='Parrainages';
        $data['menu'] ='sation_de_lavage';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        $data['getStationDeLavages'] = User::whereNotNull('station_de_lavage_id')
			->where('station_de_lavage_id', '!=', 0)
			->with('station_de_lavage')
			->get();
        //dd($data['getStationDeLavages']);
        return view('parrainage.sation_de_lavage',$data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getConcessionnaires()
    {
        $data['title'] = "Les concessionnaires";
        $data['menu'] = 'concessionnaires';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["concessionnaires"] = Concessionnaire::orderBy('id', 'desc')
            ->with('pays', 'ville', 'commune', 'userConcessionnaire')
            ->get();

        return view('concessionnaire.index', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeConcessionnaire(Request $request)
    {
        // Validation des champs
        $request->validate([
            'name' => 'required|string|max:200',
            'adresse' => 'nullable|string|max:200',
            'adresse_map' => 'nullable|string|max:500',
            'contact' => 'nullable|string|max:20',
            'mobile_fix' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:200',
            'pays_id' => 'required|exists:pays,id',
            'ville_id' => 'required|exists:villes,id',
            'commune_id' => 'nullable|exists:communes,id',
            'userconcessionnaire_id' => 'required|exists:users,id',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'longitude' => 'nullable|string|max:100',
            'latitude' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'is_whatsapp' => 'nullable|boolean',
        ]);

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        $logoPath = null;
        $coverPath = null;

        // Gestion du logo
        if ($request->file('logo')) {
            $logo = $request->file('logo');
            $logoName = 'logo-' . time() . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('images/concessionnaires'), $logoName);
            $logoPath = $logoName;
        }

        // Gestion de la cover
        if ($request->file('cover')) {
            $cover = $request->file('cover');
            $coverName = 'cover-' . time() . '.' . $cover->getClientOriginalExtension();
            $cover->move(public_path('images/concessionnaires'), $coverName);
            $coverPath = $coverName;
        }

        $concessionnaire = new Concessionnaire();
        $concessionnaire->name = html_entity_decode($request->name);
        $concessionnaire->adresse = html_entity_decode($request->adresse);
        $concessionnaire->adresse_map = html_entity_decode($request->adresse_map);
        $concessionnaire->contact = html_entity_decode($request->contact);
        $concessionnaire->mobile_fix = html_entity_decode($request->mobile_fix);
        $concessionnaire->email = html_entity_decode($request->email);
        $concessionnaire->pays_id = $request->pays_id;
        $concessionnaire->ville_id = $request->ville_id;
        $concessionnaire->commune_id = $request->commune_id;
        $concessionnaire->userconcessionnaire_id = $request->userconcessionnaire_id;
        $concessionnaire->logo = $logoPath;
        $concessionnaire->cover = $coverPath;
        $concessionnaire->longitude = $request->longitude;
        $concessionnaire->latitude = $request->latitude;
        $concessionnaire->description = html_entity_decode($request->description);
        $concessionnaire->is_whatsapp = $request->is_whatsapp ?? false;
        $concessionnaire->statut = 1;

        if ($concessionnaire->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Concessionnaire créé avec succès');
            return back();
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue');
            return back();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateConcessionnaire(Request $request, $id)
    {
        // Validation des champs
        $request->validate([
            'name' => 'required|string|max:200',
            'adresse' => 'nullable|string|max:200',
            'adresse_map' => 'nullable|string|max:500',
            'contact' => 'nullable|string|max:20',
            'mobile_fix' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:200',
            'pays_id' => 'required|exists:pays,id',
            'ville_id' => 'required|exists:villes,id',
            'commune_id' => 'nullable|exists:communes,id',
            'userconcessionnaire_id' => 'required|exists:users,id',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'longitude' => 'nullable|string|max:100',
            'latitude' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'is_whatsapp' => 'nullable|boolean',
        ]);

        $concessionnaire = Concessionnaire::find($id);
        if (!$concessionnaire) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Concessionnaire introuvable');
            return back();
        }

        // Mise à jour des champs
        $concessionnaire->name = html_entity_decode($request->name);
        $concessionnaire->adresse = html_entity_decode($request->adresse);
        $concessionnaire->adresse_map = html_entity_decode($request->adresse_map);
        $concessionnaire->contact = html_entity_decode($request->contact);
        $concessionnaire->mobile_fix = html_entity_decode($request->mobile_fix);
        $concessionnaire->email = html_entity_decode($request->email);
        $concessionnaire->pays_id = $request->pays_id;
        $concessionnaire->ville_id = $request->ville_id;
        $concessionnaire->commune_id = $request->commune_id;
        $concessionnaire->userconcessionnaire_id = $request->userconcessionnaire_id;
        $concessionnaire->longitude = $request->longitude;
        $concessionnaire->latitude = $request->latitude;
        $concessionnaire->description = html_entity_decode($request->description);
        $concessionnaire->is_whatsapp = $request->is_whatsapp ?? false;

        // Gestion du logo
        if ($request->hasFile('logo')) {
            // Suppression de l'ancien logo
            if ($concessionnaire->logo && file_exists(public_path('images/concessionnaires/' . $concessionnaire->logo))) {
                unlink(public_path('images/concessionnaires/' . $concessionnaire->logo));
            }

            // Enregistrement du nouveau logo
            $logo = $request->file('logo');
            $logoName = 'logo-' . time() . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('images/concessionnaires'), $logoName);
            $concessionnaire->logo = $logoName;
        }

        // Gestion de la cover
        if ($request->hasFile('cover')) {
            // Suppression de l'ancienne cover
            if ($concessionnaire->cover && file_exists(public_path('images/concessionnaires/' . $concessionnaire->cover))) {
                unlink(public_path('images/concessionnaires/' . $concessionnaire->cover));
            }

            // Enregistrement de la nouvelle cover
            $cover = $request->file('cover');
            $coverName = 'cover-' . time() . '.' . $cover->getClientOriginalExtension();
            $cover->move(public_path('images/concessionnaires'), $coverName);
            $concessionnaire->cover = $coverName;
        }

        if ($concessionnaire->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Concessionnaire mis à jour avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de la mise à jour');
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyConcessionnaire($id)
    {
        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $concessionnaire = Concessionnaire::find($id);

        if (empty($concessionnaire)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Concessionnaire introuvable.");
            return back();
        }

        // Suppression des images associées
        if ($concessionnaire->logo && file_exists(public_path('images/concessionnaires/' . $concessionnaire->logo))) {
            unlink(public_path('images/concessionnaires/' . $concessionnaire->logo));
        }

        if ($concessionnaire->cover && file_exists(public_path('images/concessionnaires/' . $concessionnaire->cover))) {
            unlink(public_path('images/concessionnaires/' . $concessionnaire->cover));
        }

        $concessionnaire->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', "Concessionnaire supprimé avec succès.");
        return back();
    }

    /**
     * Show the form for displaying annonces of a concessionnaire.
     */
    public function getAnnoncesConcessionnaire($id)
    {
        $data['title'] = "Annonces du concessionnaire";
        $data['menu'] = 'concessionnaires';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        // Récupérer le concessionnaire
        $concessionnaire = Concessionnaire::with('pays', 'ville', 'commune')->find($id);

        if (!$concessionnaire) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Concessionnaire introuvable!');
            return back();
        }

        $data['concessionnaire'] = $concessionnaire;

        // Récupérer les annonces du concessionnaire
        $data["annonces"] = AnnonceConcessionnaire::where('concessionaire_id', $id)
            ->orderBy('id', 'desc')
            ->with('typeDeDemande', 'typeDeVehicule', 'marque', 'user')
            ->get();

        return view('concessionnaire.annonces', $data);
    }

    /**
     * Show the form for displaying vehicules of a concessionnaire.
     */
    public function getVehiculesConcessionnaire($id)
    {
        $data['title'] = "Véhicules du concessionnaire";
        $data['menu'] = 'concessionnaires';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        // Récupérer le concessionnaire
        $concessionnaire = Concessionnaire::with('pays', 'ville', 'commune')->find($id);

        if (!$concessionnaire) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Concessionnaire introuvable!');
            return back();
        }

        $data['concessionnaire'] = $concessionnaire;

        // Récupérer les véhicules du concessionnaire
        $data["vehicules"] = VehiculeConcessionnaire::where('concessionnaire_id', $id)
            ->orderBy('id', 'desc')
            ->with('concessionnaire', 'marque')
            ->get();

        return view('concessionnaire.vehicules', $data);
    }

    /**
     * Show the form for displaying RDV of a concessionnaire.
     */
    public function getRdvConcessionnaire($id)
    {
        $data['title'] = "Rendez-vous du concessionnaire";
        $data['menu'] = 'concessionnaires';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        // Récupérer le concessionnaire
        $concessionnaire = Concessionnaire::with('pays', 'ville', 'commune')->find($id);

        if (!$concessionnaire) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Concessionnaire introuvable!');
            return back();
        }

        $data['concessionnaire'] = $concessionnaire;

        // Récupérer les RDV du concessionnaire
        $data["rdvs"] = RdvConcessionnaire::where('concessionnaire_id', $id)
            ->orderBy('created_at', 'desc')
            ->with('concessionnaire', 'user', 'gestionnaireDeFlotte')
            ->get();

        // Statistiques des RDV
        $data['stats'] = [
            'total' => $data["rdvs"]->count(),
            'en_attente' => $data["rdvs"]->where('statut', 0)->count(),
            'acceptes' => $data["rdvs"]->where('statut', 1)->count(),
            'annules' => $data["rdvs"]->where('statut', 2)->count(),
            'indisponibles' => $data["rdvs"]->where('statut', 3)->count(),
        ];

        return view('concessionnaire.rdv', $data);
    }


    /**
     * Afficher la liste des cabinets d'expertise
     */
    public function indexCabinetExpertise()
    {
        $data['title'] = "Liste des cabinets d'expertise";
        $data['menu'] = 'cabinet_expertise';

        $data["user"] = Prefecture::where([
            'id' => auth()->user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        $data['cabinets'] = CabinetExpertise::where('ville_id', $data["user"]->ville_id)
            ->with('ville', 'commune')
            ->get();

        $data['communes'] = Commune::all();

        return view('donnees.cabinet-expertise', $data);
    }

    /**
     * Afficher le formulaire de création d'un cabinet d'expertise
     */
    public function createCabinetExpertise()
    {
        $data['title'] = "Ajouter un cabinet d'expertise";
        $data['menu'] = 'cabinet_expertise';

        $data["user"] = Prefecture::where([
            'id' => auth()->user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        $data['communes'] = Commune::where('ville_id', $data["user"]->ville_id)->get();

        return view('donnees.cabinet-expertise-create', $data);
    }

    /**
     * Enregistrer un nouveau cabinet d'expertise
     */
    public function storeCabinetExpertise(Request $request)
    {
        // Validation des entrées
        $request->validate([
            'name' => 'required|string|max:200',
            'nom' => 'required|string|max:200',
            'prenoms' => 'required|string|max:200',
            'mobile' => 'required|string|max:20|unique:cabinet_expertises',
            'mobile_secondaire' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:200',
            'commune_id' => 'required|exists:communes,id',
            'adresse' => 'nullable|string|max:500',
            'longitude' => 'nullable|string|max:200',
            'latitude' => 'nullable|string|max:200',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10048',
        ], [
            'name.required' => 'Le nom du cabinet est requis.',
            'nom.required' => 'Le nom est requis.',
            'prenoms.required' => 'Le prénom est requis.',
            'mobile.required' => 'Le numéro de mobile est requis.',
            'mobile.unique' => 'Ce numéro de mobile est déjà utilisé.',
            'commune_id.required' => 'La commune est requise.',
            'commune_id.exists' => 'La commune sélectionnée n\'existe pas.',
            'email.email' => 'L\'adresse email doit être valide.',
            'photo.image' => 'Le fichier doit être une image.',
            'photo.mimes' => 'L\'image doit être de type jpeg, png, jpg ou gif.',
            'photo.max' => 'L\'image ne doit pas dépasser 10MB.',
        ]);

        // Récupérer l'utilisateur authentifié
        $prefecture = Auth::user();

        if (!$prefecture) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        // Gestion de la photo
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoName = 'photo-' . time() . '.' . $photo->getClientOriginalExtension();
            $photo->move(public_path('images/cabinet_expertise'), $photoName);
            $photoPath = $photoName;
        }

        $communes = Commune::where('id', $request->commune_id)->first();

        // Création du cabinet d'expertise
        $cabinet = new CabinetExpertise();
        $cabinet->name = html_entity_decode($request->name);
        $cabinet->nom = html_entity_decode($request->nom);
        $cabinet->prenoms = html_entity_decode($request->prenoms);
        $cabinet->mobile = html_entity_decode($request->mobile);
        $cabinet->mobile_secondaire = html_entity_decode($request->mobile_secondaire);
        $cabinet->email = html_entity_decode($request->email);
        $cabinet->ville_id = $communes->ville_id;
        $cabinet->commune_id = $communes->id;
        $cabinet->adresse = html_entity_decode($request->adresse);
        $cabinet->longitude = $request->longitude;
        $cabinet->latitude = $request->latitude;
        $cabinet->photo = $photoPath;
        $cabinet->statut = 1;

        // Sauvegarde du cabinet
        if ($cabinet->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', "Cabinet d'expertise créé avec succès.");
            return back();
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Erreur lors de la création du cabinet d'expertise.");
            return back();
        }
    }

    /**
     * Afficher le formulaire d'édition d'un cabinet d'expertise
     */
    public function editCabinetExpertise($id)
    {
        $data['title'] = "Modifier le cabinet d'expertise";
        $data['menu'] = 'cabinet_expertise';

        $data["user"] = Prefecture::where([
            'id' => auth()->user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        $data['cabinet'] = CabinetExpertise::where([
            'id' => $id,
            'ville_id' => $data["user"]->ville_id
        ])->first();

        if (empty($data['cabinet'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Cabinet d'expertise introuvable.");
            return back();
        }

        $data['communes'] = Commune::where('ville_id', $data["user"]->ville_id)->get();

        return view('donnees.cabinet-expertise-edit', $data);
    }

    /**
     * Mettre à jour un cabinet d'expertise
     */
    public function updateCabinetExpertise(Request $request, $id)
    {
        // Validation des entrées
        $request->validate([
            'name' => 'required|string|max:200',
            'nom' => 'required|string|max:200',
            'prenoms' => 'required|string|max:200',
            'mobile' => 'required|string|max:20|unique:cabinet_expertises,mobile,' . $id,
            'mobile_secondaire' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:200',
            'commune_id' => 'required|exists:communes,id',
            'adresse' => 'nullable|string|max:500',
            'longitude' => 'nullable|string|max:200',
            'latitude' => 'nullable|string|max:200',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10048',
        ], [
            'name.required' => 'Le nom du cabinet est requis.',
            'nom.required' => 'Le nom est requis.',
            'prenoms.required' => 'Le prénom est requis.',
            'mobile.required' => 'Le numéro de mobile est requis.',
            'mobile.unique' => 'Ce numéro de mobile est déjà utilisé.',
            'commune_id.required' => 'La commune est requise.',
            'commune_id.exists' => 'La commune sélectionnée n\'existe pas.',
            'email.email' => 'L\'adresse email doit être valide.',
            'photo.image' => 'Le fichier doit être une image.',
            'photo.mimes' => 'L\'image doit être de type jpeg, png, jpg ou gif.',
            'photo.max' => 'L\'image ne doit pas dépasser 10MB.',
        ]);

        // Récupérer l'utilisateur authentifié
        $prefecture = Auth::user();

        if (!$prefecture) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        // Récupérer le cabinet à mettre à jour
        $cabinet = CabinetExpertise::where([
            'id' => $id
        ])->first();

        if (empty($cabinet)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Cabinet d'expertise introuvable.");
            return back();
        }

        $communes = Commune::where('id', $request->commune_id)->first();

        // Mise à jour des données
        $cabinet->name = html_entity_decode($request->name);
        $cabinet->nom = html_entity_decode($request->nom);
        $cabinet->prenoms = html_entity_decode($request->prenoms);
        $cabinet->mobile = html_entity_decode($request->mobile);
        $cabinet->mobile_secondaire = html_entity_decode($request->mobile_secondaire);
        $cabinet->email = html_entity_decode($request->email);
        $cabinet->commune_id = $communes->id;
        $cabinet->ville_id = $communes->ville_id;
        $cabinet->adresse = html_entity_decode($request->adresse);
        $cabinet->longitude = $request->longitude;
        $cabinet->latitude = $request->latitude;

        // Gestion de la photo
        if ($request->hasFile('photo')) {
            // Supprimer l'ancienne photo si elle existe
            if ($cabinet->photo && file_exists(public_path('images/cabinet_expertise/' . $cabinet->photo))) {
                unlink(public_path('images/cabinet_expertise/' . $cabinet->photo));
            }

            $photo = $request->file('photo');
            $photoName = 'photo-' . time() . '.' . $photo->getClientOriginalExtension();
            $photo->move(public_path('images/cabinet_expertise'), $photoName);
            $cabinet->photo = $photoName;
        }

        // Sauvegarde des modifications
        if ($cabinet->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', "Cabinet d'expertise mis à jour avec succès.");
            return redirect()->route('index-cabinet-expertise');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Erreur lors de la mise à jour du cabinet d'expertise.");
            return back();
        }
    }

    /**
     * Supprimer un cabinet d'expertise
     */
    public function destroyCabinetExpertise($id)
    {
        // Récupérer l'utilisateur authentifié
        $prefecture = Auth::user();

        if (!$prefecture) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        // Récupérer le cabinet à supprimer
        $cabinet = CabinetExpertise::where([
            'id' => $id,
            'ville_id' => $prefecture->ville_id
        ])->first();

        if (empty($cabinet)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Cabinet d'expertise introuvable.");
            return back();
        }

        // Supprimer la photo associée si elle existe
        if ($cabinet->photo && file_exists(public_path('images/cabinet_expertise/' . $cabinet->photo))) {
            unlink(public_path('images/cabinet_expertise/' . $cabinet->photo));
        }

        // Suppression du cabinet
        if ($cabinet->delete()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', "Cabinet d'expertise supprimé avec succès.");
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Erreur lors de la suppression du cabinet d'expertise.");
        }

        return back();
    }

    /**
     * Changer le statut d'un cabinet d'expertise
     */
    public function toggleStatusCabinetExpertise($id)
    {
        // Récupérer l'utilisateur authentifié
        $prefecture = Auth::user();

        if (!$prefecture) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        // Récupérer le cabinet
        $cabinet = CabinetExpertise::where([
            'id' => $id,
        ])->first();

        if (empty($cabinet)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Cabinet d'expertise introuvable.");
            return back();
        }

        // Changer le statut
        $cabinet->statut = $cabinet->statut == 1 ? 0 : 1;

        if ($cabinet->save()) {
            $statusText = $cabinet->statut == 1 ? 'activé' : 'désactivé';
            session()->flash('type', 'alert-success');
            session()->flash('message', "Cabinet d'expertise {$statusText} avec succès.");
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Erreur lors du changement de statut.");
        }

        return back();
    }

    /**
     * Récupérer les données d'une visite technique pour l'édition (AJAX)
     */
    public function editVisiteTechnique($id)
    {
        $visite = Visite_technique::where('id', $id)->first();

        if (empty($visite)) {
            return response()->json(['error' => 'Visite technique introuvable'], 404);
        }

        return response()->json([
            'id' => $visite->id,
            'name' => $visite->name,
            'ville_id' => $visite->ville_id,
            'commune_id' => $visite->commune_id,
            'adresse' => $visite->adresse,
            'contacts' => $visite->contacts,
            'email' => $visite->email,
            'adresse_map' => $visite->adresse_map,
            'logo' => $visite->logo ? $this->wasabiService->temporaryUrl($visite->logo) : null,
            'statut' => $visite->statut
        ]);
    }

    /**
     * Changer le statut d'une visite technique
     */
    public function toggleStatusVisiteTechnique($id)
    {
        // Récupérer l'utilisateur authentifié
        $user = Auth::user();

        if (!$user) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        // Récupérer la visite technique
        $visite = Visite_technique::where('id', $id)->first();

        if (empty($visite)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Visite technique introuvable.");
            return back();
        }

        // Changer le statut
        $visite->statut = $visite->statut == 1 ? 0 : 1;

        if ($visite->save()) {
            $statusText = $visite->statut == 1 ? 'activée' : 'désactivée';
            session()->flash('type', 'alert-success');
            session()->flash('message', "Visite technique {$statusText} avec succès.");
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Erreur lors du changement de statut.");
        }

        return back();
    }

    /**
     * Afficher la liste des révisions techniques
     */
    public function getRevisionTechnique()
    {
        $data['title'] = 'Révisions techniques';
        $data['menu'] = 'revision_technique';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        $data["villes"] = Ville::orderBy('id', 'desc')->get();
        $data["communes"] = Commune::orderBy('id', 'desc')->get();
        $data["revision_techniques"] = Revision_technique::orderBy('id', 'desc')
            ->get()
            ->map(function ($revision) {
                return $this->attachRevisionTechniqueLogoUrl($revision);
            });

        return view('donnees.revision_technique', $data);
    }

    /**
     * Enregistrer une nouvelle révision technique
     */
    public function storeRevisionTechnique(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'ville_id' => 'required|exists:villes,id',
            'commune_id' => 'nullable|exists:communes,id',
            'adresse_map' => 'required|string|max:300',
            'adresse' => 'required|string|max:500',
            'contact' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $logoPath = $this->handleRevisionLogoUpload($request);

        $revision = new Revision_technique([
            'name' => $request->name,
            'ville_id' => $request->ville_id,
            'commune_id' => $request->commune_id,
            'adresse_map' => $request->adresse_map,
            'adresse' => $request->adresse,
            'contact' => $request->contact,
            'email' => $request->email,
            'logo' => $logoPath,
            'statut' => 1
        ]);

        if ($revision->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Révision technique créée avec succès.');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Erreur lors de la création de la révision technique.');
        }

        return back();
    }

    /**
     * Récupérer les données d'une révision technique pour l'édition (AJAX)
     */
    public function editRevisionTechnique($id)
    {
        $revision = Revision_technique::where('id', $id)->first();

        if (empty($revision)) {
            return response()->json(['error' => 'Révision technique introuvable'], 404);
        }

        return response()->json([
            'id' => $revision->id,
            'name' => $revision->name,
            'ville_id' => $revision->ville_id,
            'commune_id' => $revision->commune_id,
            'adresse_map' => $revision->adresse_map,
            'adresse' => $revision->adresse,
            'contact' => $revision->contact,
            'email' => $revision->email,
            'logo' => $this->getRevisionTechniqueLogoUrl($revision->logo),
            'statut' => $revision->statut
        ]);
    }

    /**
     * Mettre à jour une révision technique
     */
    public function updateRevisionTechnique(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'ville_id' => 'required|exists:villes,id',
            'commune_id' => 'nullable|exists:communes,id',
            'adresse_map' => 'required|string|max:300',
            'adresse' => 'required|string|max:500',
            'contact' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $revision = Revision_technique::where('id', $id)->first();

        if (empty($revision)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Révision technique introuvable.');
            return back();
        }

        $revision->name = $request->name;
        $revision->ville_id = $request->ville_id;
        $revision->commune_id = $request->commune_id;
        $revision->adresse_map = $request->adresse_map;
        $revision->adresse = $request->adresse;
        $revision->contact = $request->contact;
        $revision->email = $request->email;

        if ($request->hasFile('logo')) {
            if ($revision->logo) {
                $this->wasabiService->deleteFile($revision->logo);
            }
            $logoPath = $this->handleRevisionLogoUpload($request);
            $revision->logo = $logoPath;
        }

        if ($revision->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Révision technique mise à jour avec succès.');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Erreur lors de la mise à jour de la révision technique.');
        }

        return back();
    }

    /**
     * Supprimer une révision technique
     */
    public function destroyRevisionTechnique($id)
    {
        $revision = Revision_technique::where('id', $id)->first();

        if (empty($revision)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Révision technique introuvable.');
            return back();
        }

        if ($revision->logo) {
            $this->wasabiService->deleteFile($revision->logo);
        }

        if ($revision->delete()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Révision technique supprimée avec succès.');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Erreur lors de la suppression de la révision technique.');
        }

        return back();
    }

    /**
     * Changer le statut d'une révision technique
     */
    public function toggleStatusRevisionTechnique($id)
    {
        $revision = Revision_technique::where('id', $id)->first();

        if (empty($revision)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Révision technique introuvable.');
            return back();
        }

        $revision->statut = $revision->statut == 1 ? 0 : 1;

        if ($revision->save()) {
            $statusText = $revision->statut == 1 ? 'activée' : 'désactivée';
            session()->flash('type', 'alert-success');
            session()->flash('message', "Révision technique {$statusText} avec succès.");
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Erreur lors du changement de statut.');
        }

        return back();
    }

    /**
     * Gère le téléchargement de l'image pour les révisions techniques.
     */
    private function handleRevisionLogoUpload(Request $request): ?string
    {
        if ($request->hasFile('logo')) {
            return $this->wasabiService->uploadFile(
                $request->file('logo'),
                'images/revision_technique',
                'logo'
            );
        }

        return null;
    }

    private function attachRevisionTechniqueLogoUrl($revision)
    {
        if (!$revision) {
            return $revision;
        }

        $revision->logo = $this->getRevisionTechniqueLogoUrl($revision->logo);

        return $revision;
    }

    private function getRevisionTechniqueLogoUrl(?string $logo): ?string
    {
        if (empty($logo)) {
            return null;
        }

        if (filter_var($logo, FILTER_VALIDATE_URL)) {
            return $logo;
        }

        return $this->wasabiService->temporaryUrl($logo);
    }

    /**
     * Supprimer toutes les alertes
     */
    public function deleteAllAlerts()
    {
        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        try {
            $count = Alert::count();

            if ($count > 0) {
                Alert::truncate();

                session()->flash('type', 'alert-success');
                session()->flash('message', "Toutes les alertes ({$count}) ont été supprimées avec succès.");
            } else {
                session()->flash('type', 'alert-info');
                session()->flash('message', "Aucune alerte à supprimer.");
            }
        } catch (\Exception $e) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Erreur lors de la suppression des alertes: " . $e->getMessage());
        }

        return back();
    }

    /**
     * Afficher la liste des concessionnaires
     */
    public function getConcessionnairesListe()
    {
        $data['title'] = "Liste des concessionnaires";
        $data['menu'] = 'concessionnaires';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        // Récupérer tous les concessionnaires avec le nombre de véhicules
        $data["concessionnaires"] = Concessionnaire::withCount('vehicules')
            ->where('statut', 1)
            ->orderBy('name', 'asc')
            ->get();

        return view('concessionnaires.liste', $data);
    }

    /**
     * Afficher la liste des concessionnaires
     */
    public function getPrefecture()
    {
        $data['title'] = "Liste des prefectures";
        $data['menu'] = 'prefectures';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["villes"] = Ville::orderBy('id', 'desc')->get();
        $data["communes"] = Commune::orderBy('id', 'desc')->get();

        // Récupérer toutes les prefectures
        $data["prefectures"] = Prefecture::all();


        return view('prefecture.index',$data);
    }
    /**
     * Créer une nouvelle préfecture
     */
    public function storePrefecture(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'ville_id' => 'required|exists:villes,id',
            'adresse' => 'nullable|string|max:200',
            'mobile' => 'required|string|max:20',
            'email' => 'required|email|max:200',
        ]);

        $user = Auth::user();
        if (empty($user)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }
        $rawPassword = strval(random_int(100000, 999999));
        $hashedPassword = Hash::make($rawPassword);
        $mobileWithIndicatif = '225' . $request->mobile;
        $message = strtoupper(
            "Votre compte a ete cree avec succes\n" .
            "Voici vos identifiants de connexion :\n" .
            "Numero de telephone : " . $mobileWithIndicatif . "\n" .
            "Mot de passe : $rawPassword"
        );

        $this->sendSmsMtarget($message, $mobileWithIndicatif);

        $prefecture = new Prefecture();
        $prefecture->name = $request->name;
        $prefecture->ville_id = $request->ville_id;
        $prefecture->adresse = $request->adresse;
        $prefecture->mobile = $request->mobile;
        $prefecture->email = $request->email;
        $prefecture->password = $hashedPassword;
        $prefecture->created_by = 1; // Super Admin

        if ($prefecture->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Préfecture créée avec succès!');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Erreur lors de la création de la préfecture!');
        }
        return back();
    }

    public function updatePrefecture(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'ville_id' => 'required|exists:villes,id',
            'adresse' => 'nullable|string|max:200',
            'mobile' => 'required|string|max:20',
            'email' => 'required|email|max:200',
            'password' => 'nullable|string|min:6',
        ]);

        $prefecture = Prefecture::find($id);
        if (!$prefecture) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Préfecture introuvable!');
            return back();
        }

        $prefecture->name = $request->name;
        $prefecture->ville_id = $request->ville_id;
        $prefecture->adresse = $request->adresse;
        $prefecture->mobile = $request->mobile;
        $prefecture->email = $request->email;

        // Mettre à jour le mot de passe seulement si fourni
        if ($request->filled('password')) {
            $prefecture->password = Hash::make($request->password);
        }

        if ($prefecture->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Préfecture mise à jour avec succès!');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Erreur lors de la mise à jour de la préfecture!');
        }
        return back();
    }

    public function destroyPrefecture($id)
    {
        $prefecture = Prefecture::find($id);
        if (!$prefecture) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Préfecture introuvable!');
            return back();
        }
        if ($prefecture->delete()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Préfecture supprimée avec succès!');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Erreur lors de la suppression de la préfecture!');
        }
        return back();
    }


    /**
     * Afficher les détails d'un concessionnaire avec ses véhicules
     */
    public function getConcessionnaireDetails($id)
    {
        $data['title'] = "Détails du concessionnaire";
        $data['menu'] = 'concessionnaires';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        // Récupérer le concessionnaire avec ses véhicules
        $data["concessionnaire"] = Concessionnaire::with([
            'vehicules' => function($query) {
                $query->with('marque:id,libelle')
                      ->orderBy('created_at', 'desc');
            }
        ])->find($id);

        if (!$data["concessionnaire"]) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Concessionnaire non trouvé!');
            return redirect()->route('concessionnaires.liste');
        }

        return view('concessionnaires.details', $data);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function getCouleur()
    {
        $data['title'] = "Les couleurs";
        $data['menu'] ='couleur';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["couleurs"] = Couleur_vehicule::orderBy('id', 'desc')->get();

        return view('donnees.couleur',$data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeCouleur(Request $request)
    {
        // Normaliser l'entrée
        $request->merge([
            'libelle' => trim(ucwords($request->libelle)), // Capitalisation
        ]);

        // Validation des champs
        $request->validate([
            'libelle' => 'required|string|unique:couleur_vehicules',
        ], [
            'libelle.unique' => 'Ce libellé existe déjà, veuillez en choisir un autre.',
            'libelle.required' => 'Le champ libellé est obligatoire.',
        ]);

        // Enregistrement de la ville
        $couleur = new Couleur_vehicule();
        $couleur->libelle = html_entity_decode($request->libelle);

        if ($couleur->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Couleur créée avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue');
        }

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function updateCouleur(Request $request, $id)
    {
        // Validation des champs
        $request->validate([
            'libelle' => [
                'required',
                'string',
                Rule::unique('couleur_vehicules')->ignore($id), // Ignore l'enregistrement actuel
            ],
        ]);

        // Récupérer la catégorie
        $couleur = Couleur_vehicule::find($id);
        if (!$couleur) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Couleur introuvable');
            return back();
        }

        // Mise à jour du libellé
        $couleur->libelle = html_entity_decode($request->libelle);

        // Sauvegarde des modifications
        if ($couleur->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Couleur mise à jour avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de la mise à jour');
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyCouleur($id)
    {
        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $couleur = Couleur_vehicule::find($id);

        if (empty($couleur)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Couleur introuvable.");
            return back();
        }

        $couleur->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', "Couleur supprimée avec succès.");
        return back();
    }

    function sendSmsMtarget($message, $msisdn, $sender = 'TOO AUTO') {
        // URL de l'API MTarget
        $url = 'https://api-public-2.mtarget.fr/messages';

        // Vérifier et ajouter le signe '+' si nécessaire
        if (strpos($msisdn, '+') !== 0) {
            $msisdn = '+' . $msisdn;
        }

        // Paramètres d'authentification et de message
        $postData = http_build_query([
            'username' => 'bwantech',
            'password' => 'x7jyKG0IJRNH',
            'msisdn' => $msisdn,
            'msg' => $message,
            'sender' => $sender
        ]);

        // Initialisation de cURL
        $ch = curl_init();

        // Configuration des options cURL
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,  // Pour récupérer la réponse
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
            ],
            CURLOPT_SSL_VERIFYPEER => false, // Désactiver la vérification SSL pour les tests
            CURLOPT_TIMEOUT => 30, // Timeout de 30 secondes
        ]);

        // Exécution de la requête
        $response = curl_exec($ch);
        // dd($response);

        // Gestion des erreurs
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception("Erreur cURL : " . $error);
        }

        // Récupération du code de statut HTTP
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Fermeture de la session cURL
        curl_close($ch);

        // Vérification du code de statut HTTP
        if ($httpCode !== 200) {
            throw new \Exception("Erreur HTTP : " . $httpCode . " - Réponse : " . $response);
        }

        return $response;
    }

    /**
     * Afficher la liste des QR codes générés
     */
    public function getQrcodeGenerate()
    {
        $data['title'] = "Liste des QR codes générés";
        $data['menu'] = 'qrcode-generate';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["qrcodes"] = QrcodeGenerate::orderBy('id', 'desc')->get();

        return view('donnees.qrcode_generate', $data);
    }

    /**
     * Générer des QR codes
     */
    public function storeQrcodeGenerate(Request $request)
    {
        $request->validate([
            'nombre' => 'required|integer|min:1|max:1000',
        ], [
            'nombre.required' => 'Le nombre de QR codes à générer est requis.',
            'nombre.integer' => 'Le nombre doit être un entier.',
            'nombre.min' => 'Vous devez générer au moins 1 QR code.',
            'nombre.max' => 'Vous ne pouvez pas générer plus de 1000 QR codes à la fois.',
        ]);

        $user = Auth::user();
        if (empty($user)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $nombre = $request->nombre;

        // Trouver le dernier QR code généré
        // Récupérer tous les QR codes et trouver le plus grand numéro
        $tousQrcodes = QrcodeGenerate::all();
        $maxNumero = 0;
        $dernierQrcode = null;

        foreach ($tousQrcodes as $qr) {
            if (preg_match('/QR(\d+)/', $qr->qrcode, $matches)) {
                $numero = (int)$matches[1];
                if ($numero > $maxNumero) {
                    $maxNumero = $numero;
                    $dernierQrcode = $qr;
                }
            }
        }

        // Déterminer le numéro de départ
        $numeroDebut = 1;

        if ($dernierQrcode) {
            // Extraire le numéro du dernier QR code
            $qrcodeStr = $dernierQrcode->qrcode;
            if (preg_match('/QR(\d+)/', $qrcodeStr, $matches)) {
                $numeroDebut = (int)$matches[1] + 1;
            }
        }

        $qrCodesGeneres = [];
        $erreurs = [];

        try {
            for ($i = 0; $i < $nombre; $i++) {
                $numero = $numeroDebut + $i;

                // Générer le code QR avec le bon format
                // Si le numéro est > 9999, utiliser 5 chiffres (QR00001)
                // Sinon utiliser 4 chiffres (QR0001)
                if ($numero > 9999) {
                    $qrcode = 'QR' . str_pad($numero, 5, '0', STR_PAD_LEFT);
                } else {
                    $qrcode = 'QR' . str_pad($numero, 4, '0', STR_PAD_LEFT);
                }

                // Vérifier si le QR code existe déjà
                $existe = QrcodeGenerate::where('qrcode', $qrcode)->exists();

                if (!$existe) {
                    $qrCode = new QrcodeGenerate();
                    $qrCode->qrcode = $qrcode;
                    $qrCode->is_assigned = 0;

                    if ($qrCode->save()) {
                        $qrCodesGeneres[] = $qrcode;
                    } else {
                        $erreurs[] = $qrcode;
                    }
                } else {
                    $erreurs[] = $qrcode . ' (déjà existant)';
                }
            }

            if (count($qrCodesGeneres) > 0) {
                session()->flash('type', 'alert-success');
                session()->flash('message', count($qrCodesGeneres) . ' QR code(s) généré(s) avec succès!');
            }

            if (count($erreurs) > 0) {
                session()->flash('type', 'alert-warning');
                session()->flash('message', count($qrCodesGeneres) . ' QR code(s) généré(s), ' . count($erreurs) . ' erreur(s).');
            }

        } catch (\Exception $e) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Erreur lors de la génération des QR codes: ' . $e->getMessage());
        }

        return back();
    }

    /**
     * Mettre à jour un QR code
     */
    public function updateQrcodeGenerate(Request $request, $id)
    {
        $request->validate([
            'qrcode' => 'required|string|max:255|unique:qrcode_generates,qrcode,' . $id,
            'is_assigned' => 'nullable|boolean',
        ], [
            'qrcode.required' => 'Le code QR est requis.',
            'qrcode.unique' => 'Ce code QR existe déjà.',
        ]);

        $qrCode = QrcodeGenerate::find($id);

        if (!$qrCode) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'QR code introuvable!');
            return back();
        }

        // Vérifier si le QR code est assigné
        if ($qrCode->is_assigned) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Ce QR code est assigné et ne peut pas être modifié!');
            return back();
        }

        $qrCode->qrcode = $request->qrcode;

        if ($request->has('is_assigned')) {
            $qrCode->is_assigned = $request->is_assigned ? 1 : 0;
            if ($qrCode->is_assigned && !$qrCode->assigned_at) {
                $qrCode->assigned_at = now();
            } elseif (!$qrCode->is_assigned) {
                $qrCode->assigned_at = null;
            }
        }

        if ($qrCode->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'QR code mis à jour avec succès!');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Erreur lors de la mise à jour du QR code!');
        }

        return back();
    }

    /**
     * Supprimer un QR code
     */
    public function destroyQrcodeGenerate($id)
    {
        $user = Auth::user();
        if (empty($user)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $qrCode = QrcodeGenerate::find($id);

        if (empty($qrCode)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "QR code introuvable.");
            return back();
        }

        // Vérifier si le QR code est assigné
        if ($qrCode->is_assigned) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Ce QR code est assigné et ne peut pas être supprimé!");
            return back();
        }

        if ($qrCode->delete()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', "QR code supprimé avec succès.");
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Erreur lors de la suppression du QR code.");
        }

        return back();
    }

    /**
     * Afficher la liste des acteurs
     */
    public function getActeurs()
    {
        $data['title'] = "Liste des acteurs";
        $data['menu'] = 'acteurs';

        $data['user'] = Auth::user();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["acteurs"] = Acteur::orderBy('id', 'desc')->get();

        return view('donnees.acteurs', $data);
    }

    /**
     * Enregistrer un nouvel acteur
     */
    public function storeActeur(Request $request)
    {
        // Validation des champs
        $request->validate([
            'name' => 'required|string|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'siteweb' => 'nullable|url|max:150',
        ], [
            'name.required' => 'Le nom de l\'acteur est requis.',
            'name.max' => 'Le nom ne doit pas dépasser 100 caractères.',
            'logo.image' => 'Le fichier doit être une image.',
            'logo.mimes' => 'L\'image doit être de type jpeg, png, jpg, gif ou svg.',
            'logo.max' => 'L\'image ne doit pas dépasser 2MB.',
            'siteweb.url' => 'Le site web doit être une URL valide.',
            'siteweb.max' => 'L\'URL ne doit pas dépasser 150 caractères.',
        ]);

        $data['user'] = Auth::user();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        $logoPath = null;

        // Gestion du logo
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = 'logo-' . time() . '.' . $logo->getClientOriginalExtension();
            $destinationPath = public_path('images/acteurs');

            // Créer le dossier s'il n'existe pas
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $logo->move($destinationPath, $logoName);
            $logoPath = $logoName;
        }

        $acteur = new Acteur();
        $acteur->name = html_entity_decode($request->name);
        $acteur->logo = $logoPath;
        $acteur->siteweb = html_entity_decode($request->siteweb);

        if ($acteur->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Acteur créé avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue');
        }

        return back();
    }

    /**
     * Mettre à jour un acteur
     */
    public function updateActeur(Request $request, $id)
    {
        // Validation des champs
        $request->validate([
            'name' => 'required|string|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'siteweb' => 'nullable|url|max:150',
        ], [
            'name.required' => 'Le nom de l\'acteur est requis.',
            'name.max' => 'Le nom ne doit pas dépasser 100 caractères.',
            'logo.image' => 'Le fichier doit être une image.',
            'logo.mimes' => 'L\'image doit être de type jpeg, png, jpg, gif ou svg.',
            'logo.max' => 'L\'image ne doit pas dépasser 2MB.',
            'siteweb.url' => 'Le site web doit être une URL valide.',
            'siteweb.max' => 'L\'URL ne doit pas dépasser 150 caractères.',
        ]);

        $acteur = Acteur::find($id);

        if (!$acteur) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Acteur introuvable');
            return back();
        }

        // Mise à jour des champs
        $acteur->name = html_entity_decode($request->name);
        $acteur->siteweb = html_entity_decode($request->siteweb);

        // Gestion du logo
        if ($request->hasFile('logo')) {
            // Suppression de l'ancien logo
            if ($acteur->logo && file_exists(public_path('images/acteurs/' . $acteur->logo))) {
                unlink(public_path('images/acteurs/' . $acteur->logo));
            }

            // Enregistrement du nouveau logo
            $logo = $request->file('logo');
            $logoName = 'logo-' . time() . '.' . $logo->getClientOriginalExtension();
            $destinationPath = public_path('images/acteurs');

            // Créer le dossier s'il n'existe pas
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $logo->move($destinationPath, $logoName);
            $acteur->logo = $logoName;
        }

        if ($acteur->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Acteur mis à jour avec succès');
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de la mise à jour');
        }

        return back();
    }

    /**
     * Supprimer un acteur
     */
    public function destroyActeur($id)
    {
        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $acteur = Acteur::find($id);

        if (empty($acteur)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Acteur introuvable.");
            return back();
        }

        // Supprimer le logo associé, s'il existe
        if ($acteur->logo && file_exists(public_path('images/acteurs/' . $acteur->logo))) {
            unlink(public_path('images/acteurs/' . $acteur->logo));
        }

        if ($acteur->delete()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', "Acteur supprimé avec succès.");
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Erreur lors de la suppression de l'acteur.");
        }

        return back();
    }

	/**
     * Display a listing of forfait usagers.
     */
    public function indexForfaitUsager()
    {
        $data['title'] = "Les forfaits usagers";
        $data['menu'] = 'forfait-usager';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["forfait_usagers"] = Forfait_usager::with(['categorieServices.sousCategorieService', 'avantageUsager'])
            ->orderBy('id', 'desc')
            ->get();
        $data["categorie_services"] = Categorie_service::where('statut', 1)->orderBy('libelle')->get();
        $data["forfait_avantage_usagers"] = ForfaitAvantageUsager::where('available', 1)
            ->orderBy('avantages')
            ->get();

        return view('donnees.forfait_usager', $data);
    }

    public function indexForfaitAvantageUsager()
    {
        $data['title'] = "Les avantages des forfaits usagers";
        $data['menu'] = 'forfait-avantage-usager';

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["forfait_avantage_usagers"] = ForfaitAvantageUsager::orderBy('id', 'desc')->get();

        return view('donnees.forfait_avantage_usager', $data);
    }

    public function storeForfaitAvantageUsager(Request $request)
    {
        $request->validate([
            'avantages' => 'required|string',
            'available' => 'nullable|boolean',
        ]);

        $avantage = new ForfaitAvantageUsager();
        $avantage->avantages = html_entity_decode($request->avantages);
        $avantage->available = $request->available ?? 1;

        if ($avantage->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Avantage créé avec succès');
            return back();
        }

        session()->flash('type', 'alert-danger');
        session()->flash('message', 'Une erreur est survenue');
        return back();
    }

    public function updateForfaitAvantageUsager(Request $request, $id)
    {
        $request->validate([
            'avantages' => 'required|string',
            'available' => 'nullable|boolean',
        ]);

        $avantage = ForfaitAvantageUsager::find($id);
        if (!$avantage) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Avantage introuvable');
            return back();
        }

        $avantage->avantages = html_entity_decode($request->avantages);
        $avantage->available = $request->available ?? $avantage->available;

        if ($avantage->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Avantage modifié avec succès');
            return back();
        }

        session()->flash('type', 'alert-danger');
        session()->flash('message', 'Une erreur est survenue lors de la modification');
        return back();
    }

    public function destroyForfaitAvantageUsager($id)
    {
        $avantage = ForfaitAvantageUsager::find($id);
        if (!$avantage) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Avantage introuvable');
            return back();
        }

        if ($avantage->forfaitUsagers()->count() > 0) {
            session()->flash('type', 'alert-warning');
            session()->flash('message', "Cet avantage ne peut pas être supprimé car il est utilisé par un forfait usager.");
            return back();
        }

        $avantage->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', 'Avantage supprimé avec succès');
        return back();
    }

    /**
     * Store a newly created forfait usager in storage.
     */
    public function storeForfaitUsager(Request $request)
    {
        // Validation des champs
        $request->validate([
            'libelle' => 'required|string|unique:forfait_usagers',
            'duree' => 'required|integer|min:0',
            'prix' => 'required|integer|min:0',
            'nombre_vehicule' => 'required|integer|min:0',
            'statut' => 'nullable',
            'forfait_avantage_usager_id' => 'nullable|exists:forfait_avantage_usagers,id',
            'categorie_services' => 'nullable|array',
            'categorie_services.*' => 'exists:categorie_services,id',
        ]);

        $data['user'] = Super::where([
            'id' => Auth::user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $forfait = new Forfait_usager();
        $forfait->libelle = html_entity_decode($request->libelle);
        $forfait->duree = $request->duree;
        $forfait->prix = $request->prix;
        $forfait->nombre_vehicule = $request->nombre_vehicule;
        $forfait->statut = $request->statut ?? 1;
        $forfait->forfait_avantage_usager_id = $request->forfait_avantage_usager_id;

        // Vérification si le forfait a bien été créé
        if ($forfait->save()) {
            // Sauvegarder les catégories de service liées
            if (!empty($request->categorie_services)) {
                $forfait->categorieServices()->attach($request->categorie_services);
            }
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Forfait usager créé avec succès');
            return back();
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue');
            return back();
        }
    }

    /**
     * Update the specified forfait usager in storage.
     */
    public function updateForfaitUsager(Request $request, $id)
    {
        // Validation des champs
        $request->validate([
            'libelle' => [
                'required',
                'string',
                Rule::unique('forfait_usagers')->ignore($id),
            ],
            'duree' => 'required|integer|min:0',
            'prix' => 'required|integer|min:0',
            'nombre_vehicule' => 'required|integer|min:0',
            'statut' => 'nullable',
            'forfait_avantage_usager_id' => 'nullable|exists:forfait_avantage_usagers,id',
            'categorie_services' => 'nullable|array',
            'categorie_services.*' => 'exists:categorie_services,id',
        ]);

        // Récupérer le forfait
        $forfait = Forfait_usager::find($id);
        if (!$forfait) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Forfait usager introuvable');
            return back();
        }

        $forfait->libelle = html_entity_decode($request->libelle);
        $forfait->duree = $request->duree;
        $forfait->prix = $request->prix;
        $forfait->nombre_vehicule = $request->nombre_vehicule;
        $forfait->statut = $request->statut ?? $forfait->statut;
        $forfait->forfait_avantage_usager_id = $request->forfait_avantage_usager_id;

        // Sauvegarde des modifications
        if ($forfait->save()) {
            // Mettre à jour les catégories de service liées
            $forfait->categorieServices()->sync($request->categorie_services ?? []);
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Forfait usager modifié avec succès');
            return back();
        } else {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de la modification');
            return back();
        }
    }

    /**
     * Remove the specified forfait usager from storage.
     */
    public function destroyForfaitUsager($id)
    {
        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $forfait = Forfait_usager::find($id);

        if (empty($forfait)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Forfait usager introuvable.");
            return back();
        }

        // Vérifier si le forfait est utilisé dans des abonnements
        if ($forfait->abonnement_usagers()->count() > 0) {
            session()->flash('type', 'alert-warning');
            session()->flash('message', "Ce forfait ne peut pas être supprimé car il est utilisé dans des abonnements.");
            return back();
        }

        $forfait->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', "Forfait usager supprimé avec succès.");
        return back();
    }

}
