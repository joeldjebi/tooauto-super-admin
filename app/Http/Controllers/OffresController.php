<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Etablissement;
use App\Models\Super;
use App\Models\Type_etablissement;
use App\Models\User;
use App\Models\Type_offre;
use App\Models\Type_contrat;
use App\Models\Ville;
use App\Models\Form_offre;
use App\Models\Professionnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Redirector; 
use Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Offre_emploi;
use App\Models\Offre_emploi_recrutement;
use App\Models\Recrutement_offre;

class OffresController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function indexOffres()
    {
        $data['title'] ='Liste des offres d\'emploi';
        $data['menu'] ='offre';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        $data["offres"] = Offre_emploi::orderBy('id', 'desc')
        ->with('type_contrat')
        ->get();
        
        $data["types"] = Type_contrat::orderBy('id', 'desc')->get();
        $data["types_offre"] = Type_offre::orderBy('id', 'desc')->get();
        $data["villes"] = Ville::orderBy('id', 'asc')->get();

        
        return view('offres_emploi.index',$data);
    }
    
    /**
     * Display a listing of the resource.
     */
    public function indexTypeOffre()
    {
        $data['title'] ='Liste des types d\'offres d\'emploi';
        $data['menu'] ='offre';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        $data["types"] = Type_offre::orderBy('id', 'desc')
        ->get();

        
        return view('offres_emploi.type_offre',$data);
    }
    
    /**
     * Afficher la page de modification d'un type d'offre
     */
    public function editTypeOffre($id)
    {
        $data['title'] = 'Modifier le type d\'offre d\'emploi';
        $data['menu'] = 'offre';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        $data["type_offre"] = Type_offre::find($id);
        
        if (!$data["type_offre"]) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Type d\'offre introuvable!');
            return redirect()->route('index-type-offre');
        }

        return view('offres_emploi.edit_type_offre', $data);
    }
    
    public function storeTypeOffre(Request $request)
    {
        $data['title'] ='Liste des types d\'offres d\'emploi';
        $data['menu'] ='offre';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        // Validation des données
        $validator = Validator::make($request->all(), [
            'libelle' => 'required|string|max:255',
            'commentaire' => 'nullable|string',
            'posts' => 'required|array|min:1',
            'posts.*' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Traitement des posts multiples
        $posts = $request->posts;
        // Filtrer les posts vides et nettoyer les espaces
        $posts = array_filter(array_map('trim', $posts), function($post) {
            return !empty($post);
        });

        if (empty($posts)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Au moins un post doit être renseigné!');
            return back();
        }

        // Concaténer tous les posts avec un séparateur
        $postsString = implode('; ', $posts);

        $type_offre = new Type_offre();
        $type_offre->libelle = html_entity_decode($request->libelle);
        $type_offre->commentaire = html_entity_decode($request->commentaire);
        $type_offre->post = html_entity_decode($postsString);

        $type_offre->save();
        session()->flash('type', 'alert-success');
        session()->flash('message', 'Type d\'offre d\'emploi créé avec succès');
        return back();

    }

    public function updateTypeOffre(Request $request, $id)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'libelle' => 'required|string|max:255',
            'commentaire' => 'nullable|string',
            'posts' => 'required|array|min:1',
            'posts.*' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Traitement des posts multiples
        $posts = $request->posts;
        // Filtrer les posts vides et nettoyer les espaces
        $posts = array_filter(array_map('trim', $posts), function($post) {
            return !empty($post);
        });

        if (empty($posts)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Au moins un post doit être renseigné!');
            return back();
        }

        // Concaténer tous les posts avec un séparateur
        $postsString = implode('; ', $posts);

        $type_offre = Type_offre::find($id);
        if (!$type_offre) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Type d\'offre introuvable!');
            return redirect()->route('index-type-offre');
        }

        try {
            // Mise à jour directe avec update()
            $updated = Type_offre::where('id', $id)->update([
                'libelle' => html_entity_decode($request->libelle),
                'commentaire' => html_entity_decode($request->commentaire),
                'post' => html_entity_decode($postsString)
            ]);

            if ($updated) {
                session()->flash('type', 'alert-success');
                session()->flash('message', 'Type d\'offre d\'emploi mise à jour avec succès');
                return redirect()->route('index-type-offre');
            } else {
                session()->flash('type', 'alert-danger');
                session()->flash('message', 'Aucune modification effectuée!');
                return back();
            }
        } catch (\Exception $e) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Erreur: ' . $e->getMessage());
            return back();
        }
    }

    public function destroyTypeOffre($id)
    {
        $data['title'] ='Liste des types d\'offres d\'emploi';
        $data['menu'] ='offre';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        $type_offre = Type_offre::find($id);
        $type_offre->delete();
        session()->flash('type', 'alert-success');
        session()->flash('message', 'Type d\'offre d\'emploi supprimé avec succès');
        return back();
    }

    // ==================== CRUD TYPES DE CONTRATS ====================

    /**
     * Afficher la liste des types de contrats
     */
    public function indexTypeContrat()
    {
        $data['title'] = 'Liste des types de contrats';
        $data['menu'] = 'offre';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        $data["types"] = Type_contrat::orderBy('id', 'desc')->get();

        return view('offres_emploi.type_contrat', $data);
    }

    /**
     * Afficher la page de modification d'un type de contrat
     */
    public function editTypeContrat($id)
    {
        $data['title'] = 'Modifier le type de contrat';
        $data['menu'] = 'offre';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        $data["type_contrat"] = Type_contrat::find($id);
        
        if (!$data["type_contrat"]) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Type de contrat introuvable!');
            return redirect()->route('index-type-contrat');
        }

        return view('offres_emploi.edit_type_contrat', $data);
    }

    /**
     * Créer un nouveau type de contrat
     */
    public function storeTypeContrat(Request $request)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'libelle' => 'required|string|max:100|unique:type_de_contrats,libelle'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $type_contrat = new Type_contrat();
            $type_contrat->libelle = html_entity_decode($request->libelle);
            $type_contrat->save();

            session()->flash('type', 'alert-success');
            session()->flash('message', 'Type de contrat créé avec succès');
        } catch (\Exception $e) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Erreur lors de la création: ' . $e->getMessage());
        }

        return back();
    }

    /**
     * Mettre à jour un type de contrat
     */
    public function updateTypeContrat(Request $request, $id)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'libelle' => 'required|string|max:100|unique:type_de_contrats,libelle,' . $id
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $type_contrat = Type_contrat::find($id);
        if (!$type_contrat) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Type de contrat introuvable!');
            return redirect()->route('index-type-contrat');
        }

        try {
            // Mise à jour directe avec update()
            $updated = Type_contrat::where('id', $id)->update([
                'libelle' => html_entity_decode($request->libelle)
            ]);

            if ($updated) {
                session()->flash('type', 'alert-success');
                session()->flash('message', 'Type de contrat mis à jour avec succès');
                return redirect()->route('index-type-contrat');
            } else {
                session()->flash('type', 'alert-danger');
                session()->flash('message', 'Aucune modification effectuée!');
                return back();
            }
        } catch (\Exception $e) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Erreur: ' . $e->getMessage());
            return back();
        }
    }

    /**
     * Supprimer un type de contrat
     */
    public function destroyTypeContrat($id)
    {
        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        $type_contrat = Type_contrat::find($id);
        
        if (!$type_contrat) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Type de contrat introuvable!');
            return back();
        }

        try {
            // Vérifier s'il y a des offres liées à ce type de contrat
            $offresCount = $type_contrat->offres()->count();
            
            if ($offresCount > 0) {
                session()->flash('type', 'alert-warning');
                session()->flash('message', 'Impossible de supprimer ce type de contrat car il est utilisé par ' . $offresCount . ' offre(s).');
                return back();
            }

            $type_contrat->delete();
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Type de contrat supprimé avec succès');
        } catch (\Exception $e) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Erreur lors de la suppression: ' . $e->getMessage());
        }

        return back();
    }

    // ==================== CRUD OFFRES D'EMPLOI ====================

    /**
     * Afficher la page de modification d'une offre d'emploi
     */
    public function editOffreEmploi($id)
    {
        $data['title'] = 'Modifier l\'offre d\'emploi';
        $data['menu'] = 'offre';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        $data["offre"] = Offre_emploi::with(['type_contrat', 'type_offre', 'ville'])->find($id);
        
        if (!$data["offre"]) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Offre d\'emploi introuvable!');
            return redirect()->route('index-offre');
        }

        // Récupérer les données pour les listes déroulantes
        $data["types_contrat"] = Type_contrat::orderBy('libelle')->get();
        $data["types_offre"] = Type_offre::orderBy('libelle')->get();
        $data["villes"] = Ville::orderBy('libelle')->get();

        return view('offres_emploi.edit_offre', $data);
    }

    /**
     * Créer une nouvelle offre d'emploi
     */
    public function storeOffreEmploi(Request $request)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
            'type_offre_id' => 'required|exists:type_offres,id',
            'ville_id' => 'required|exists:villes,id',
            'type_de_contrat_id' => 'required|exists:type_de_contrats,id',
            'experience' => 'required|string|max:200',
            'salaire' => 'required|string|max:200',
            'competence_requises' => 'required|array|min:1',
            'competence_requises.*' => 'required|string|max:200',
            'missions' => 'required|array|min:1',
            'missions.*' => 'required|string|max:200',
            'profil_rechercher' => 'required|array|min:1',
            'profil_rechercher.*' => 'required|string|max:200',
            'avantages' => 'required|array|min:1',
            'avantages.*' => 'required|string|max:200'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Filtrer et nettoyer les tableaux
            $competences = array_filter(array_map('trim', $request->competence_requises), function($comp) {
                return !empty($comp);
            });
            $missions = array_filter(array_map('trim', $request->missions), function($mission) {
                return !empty($mission);
            });
            $profils = array_filter(array_map('trim', $request->profil_rechercher), function($profil) {
                return !empty($profil);
            });
            $avantages = array_filter(array_map('trim', $request->avantages), function($avantage) {
                return !empty($avantage);
            });

            $offre = new Offre_emploi();
            $offre->description = html_entity_decode($request->description);
            $offre->type_offre_id = $request->type_offre_id;
            $offre->ville_id = $request->ville_id;
            $offre->type_de_contrat_id = $request->type_de_contrat_id;
            $offre->experience = html_entity_decode($request->experience);
            $offre->salaire = html_entity_decode($request->salaire);
            $offre->competence_requises = implode('; ', $competences);
            $offre->missions = implode('; ', $missions);
            $offre->profil_rechercher = implode('; ', $profils);
            $offre->avantages = implode('; ', $avantages);
            $offre->save();

            session()->flash('type', 'alert-success');
            session()->flash('message', 'Offre d\'emploi créée avec succès');
        } catch (\Exception $e) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Erreur lors de la création: ' . $e->getMessage());
        }

        return back();
    }

    /**
     * Mettre à jour une offre d'emploi
     */
    public function updateOffreEmploi(Request $request, $id)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
            'type_offre_id' => 'required|exists:type_offres,id',
            'ville_id' => 'required|exists:villes,id',
            'type_de_contrat_id' => 'required|exists:type_de_contrats,id',
            'experience' => 'required|string|max:200',
            'salaire' => 'required|string|max:200',
            'competence_requises' => 'required|array|min:1',
            'competence_requises.*' => 'required|string|max:200',
            'missions' => 'required|array|min:1',
            'missions.*' => 'required|string|max:200',
            'profil_rechercher' => 'required|array|min:1',
            'profil_rechercher.*' => 'required|string|max:200',
            'avantages' => 'required|array|min:1',
            'avantages.*' => 'required|string|max:200'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $offre = Offre_emploi::find($id);
        if (!$offre) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Offre d\'emploi introuvable!');
            return redirect()->route('index-offre');
        }

        try {
            // Filtrer et nettoyer les tableaux
            $competences = array_filter(array_map('trim', $request->competence_requises), function($comp) {
                return !empty($comp);
            });
            $missions = array_filter(array_map('trim', $request->missions), function($mission) {
                return !empty($mission);
            });
            $profils = array_filter(array_map('trim', $request->profil_rechercher), function($profil) {
                return !empty($profil);
            });
            $avantages = array_filter(array_map('trim', $request->avantages), function($avantage) {
                return !empty($avantage);
            });

            // Mise à jour directe avec update()
            $updated = Offre_emploi::where('id', $id)->update([
                'description' => html_entity_decode($request->description),
                'type_offre_id' => $request->type_offre_id,
                'ville_id' => $request->ville_id,
                'type_de_contrat_id' => $request->type_de_contrat_id,
                'experience' => html_entity_decode($request->experience),
                'salaire' => html_entity_decode($request->salaire),
                'competence_requises' => implode('; ', $competences),
                'missions' => implode('; ', $missions),
                'profil_rechercher' => implode('; ', $profils),
                'avantages' => implode('; ', $avantages)
            ]);

            if ($updated) {
                session()->flash('type', 'alert-success');
                session()->flash('message', 'Offre d\'emploi mise à jour avec succès');
                return redirect()->route('index-offre');
            } else {
                session()->flash('type', 'alert-danger');
                session()->flash('message', 'Aucune modification effectuée!');
                return back();
            }
        } catch (\Exception $e) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Erreur: ' . $e->getMessage());
            return back();
        }
    }

    /**
     * Supprimer une offre d'emploi
     */
    public function destroyOffreEmploi($id)
    {
        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        $offre = Offre_emploi::find($id);
        
        if (!$offre) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Offre d\'emploi introuvable!');
            return back();
        }

        try {
            $offre->delete();
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Offre d\'emploi supprimée avec succès');
        } catch (\Exception $e) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Erreur lors de la suppression: ' . $e->getMessage());
        }

        return back();
    }

    // ==================== GESTION DES CANDIDATURES ====================

    /**
     * Afficher la liste des candidatures
     */
    public function indexCandidat()
    {
        $data['title'] = 'Candidatures reçues';
        $data['menu'] = 'candidat';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        // Récupérer toutes les candidatures avec leurs offres d'emploi
        $data["candidats"] = Form_offre::with(['offre_emploi.type_offre', 'offre_emploi.ville', 'offre_emploi.type_contrat'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('offres_emploi.candidat', $data);
    }

    /**
     * Afficher les détails d'une candidature
     */
    public function showCandidat($id)
    {
        $data['title'] = 'Détails de la candidature';
        $data['menu'] = 'candidat';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        $data["candidat"] = Form_offre::with(['offre_emploi.type_offre', 'offre_emploi.ville', 'offre_emploi.type_contrat'])
            ->find($id);

        if (!$data["candidat"]) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Candidature introuvable!');
            return redirect()->route('index-candidat');
        }

        return view('offres_emploi.show_candidat', $data);
    }

    /**
     * Supprimer une candidature
     */
    public function destroyCandidat($id)
    {
        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        try {
            $candidat = Form_offre::find($id);
            
            if (!$candidat) {
                session()->flash('type', 'alert-danger');
                session()->flash('message', 'Candidature introuvable!');
                return back();
            }

            // Supprimer le fichier CV s'il existe
            if ($candidat->cv && file_exists(public_path('uploads/cv/' . $candidat->cv))) {
                unlink(public_path('uploads/cv/' . $candidat->cv));
            }

            $candidat->delete();

            session()->flash('type', 'alert-success');
            session()->flash('message', 'Candidature supprimée avec succès');
        } catch (\Exception $e) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Erreur lors de la suppression: ' . $e->getMessage());
        }

        return back();
    }

    /**
     * Afficher les statistiques des candidatures
     */
    public function statsCandidat()
    {
        $data['title'] = 'Statistiques des candidatures';
        $data['menu'] = 'candidat';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        // Statistiques générales
        $data['total_candidats'] = Form_offre::count();
        $data['candidats_ce_mois'] = Form_offre::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $data['candidats_cette_semaine'] = Form_offre::whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ])->count();

        // Top 5 des offres avec le plus de candidatures
        $data['top_offres'] = Form_offre::selectRaw('offre_emploi_id, COUNT(*) as total')
            ->with(['offre_emploi.type_offre', 'offre_emploi.ville'])
            ->groupBy('offre_emploi_id')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // Candidatures par mois (6 derniers mois)
        $data['candidats_par_mois'] = Form_offre::selectRaw('MONTH(created_at) as mois, YEAR(created_at) as annee, COUNT(*) as total')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('mois', 'annee')
            ->orderBy('annee', 'asc')
            ->orderBy('mois', 'asc')
            ->get();

        return view('offres_emploi.stats_candidat', $data);
    }

    /**
     * Afficher le formulaire de création d'une offre d'emploi détaillée
     */
    public function createOffreEmploiDetail()
    {
        $data['title'] = 'Créer une offre d\'emploi';
        $data['menu'] = 'offre-recrutement';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        // Récupérer le dernier numéro d'ordre pour proposer le suivant
        $derniereOffre = Offre_emploi_recrutement::orderBy('ordre', 'desc')->first();
        $data['prochain_ordre'] = $derniereOffre ? ($derniereOffre->ordre ?? 0) + 1 : 1;

        return view('offres_emploi.create_offre_detail', $data);
    }

    /**
     * Enregistrer une offre d'emploi détaillée
     */
    public function storeOffreEmploiDetail(Request $request)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'titre' => 'required|string|max:255',
            'ordre' => 'required|integer|min:1',
            'categorie' => 'required|string|max:100',
            'description' => 'required|string',
            'missions' => 'required|array|min:1',
            'missions.*' => 'required|string',
            'profil_recherche' => 'required|array|min:1',
            'profil_recherche.*' => 'required|string',
            'competences' => 'required|array|min:1',
            'competences.*' => 'required|string|max:200',
            'prerequis' => 'nullable|array',
            'prerequis.*' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Filtrer et nettoyer les tableaux
            $missions = array_filter(array_map('trim', $request->missions), function($mission) {
                return !empty($mission);
            });
            $profils = array_filter(array_map('trim', $request->profil_recherche), function($profil) {
                return !empty($profil);
            });
            $competences = array_filter(array_map('trim', $request->competences), function($comp) {
                return !empty($comp);
            });
            $prerequis = [];
            if ($request->has('prerequis')) {
                $prerequis = array_filter(array_map('trim', $request->prerequis), function($prereq) {
                    return !empty($prereq);
                });
            }

            $offre = new Offre_emploi_recrutement();
            $offre->titre = html_entity_decode($request->titre);
            $offre->ordre = $request->ordre;
            $offre->categorie = html_entity_decode($request->categorie);
            $offre->description = html_entity_decode($request->description);
            $offre->missions = implode('; ', $missions);
            $offre->profil_rechercher = implode('; ', $profils);
            $offre->competence_requises = implode('; ', $competences);
            $offre->prerequis = !empty($prerequis) ? implode('; ', $prerequis) : null;
            $offre->save();

            session()->flash('type', 'alert-success');
            session()->flash('message', 'Offre d\'emploi créée avec succès');
            return redirect()->route('show-offres');
        } catch (\Exception $e) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Erreur lors de la création: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    /**
     * Afficher toutes les offres d'emploi selon le format HTML fourni
     */
    public function showOffres()
    {
        $data['title'] = 'Liste des offres d\'emploi recrutement';
        $data['menu'] = 'offre-recrutement';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        // Récupérer toutes les offres triées par ordre
        $data["offres"] = Offre_emploi_recrutement::orderBy('ordre', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return view('offres_emploi.show_offres', $data);
    }

    /**
     * Afficher le formulaire de modification d'une offre d'emploi recrutement
     */
    public function editOffreEmploiRecrutement($id)
    {
        $data['title'] = 'Modifier une offre d\'emploi';
        $data['menu'] = 'offre-recrutement';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        $data["offre"] = Offre_emploi_recrutement::find($id);
        
        if (!$data["offre"]) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Offre d\'emploi introuvable!');
            return redirect()->route('show-offres');
        }

        return view('offres_emploi.edit_offre_detail', $data);
    }

    /**
     * Mettre à jour une offre d'emploi recrutement
     */
    public function updateOffreEmploiRecrutement(Request $request, $id)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'titre' => 'required|string|max:255',
            'ordre' => 'required|integer|min:1',
            'categorie' => 'required|string|max:100',
            'description' => 'required|string',
            'missions' => 'required|array|min:1',
            'missions.*' => 'required|string',
            'profil_recherche' => 'required|array|min:1',
            'profil_recherche.*' => 'required|string',
            'competences' => 'required|array|min:1',
            'competences.*' => 'required|string|max:200',
            'prerequis' => 'nullable|array',
            'prerequis.*' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $offre = Offre_emploi_recrutement::find($id);
        
        if (!$offre) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Offre d\'emploi introuvable!');
            return redirect()->route('show-offres');
        }

        try {
            // Filtrer et nettoyer les tableaux
            $missions = array_filter(array_map('trim', $request->missions), function($mission) {
                return !empty($mission);
            });
            $profils = array_filter(array_map('trim', $request->profil_recherche), function($profil) {
                return !empty($profil);
            });
            $competences = array_filter(array_map('trim', $request->competences), function($comp) {
                return !empty($comp);
            });
            $prerequis = [];
            if ($request->has('prerequis')) {
                $prerequis = array_filter(array_map('trim', $request->prerequis), function($prereq) {
                    return !empty($prereq);
                });
            }

            // Mise à jour de l'offre
            $offre->titre = html_entity_decode($request->titre);
            $offre->ordre = $request->ordre;
            $offre->categorie = html_entity_decode($request->categorie);
            $offre->description = html_entity_decode($request->description);
            $offre->missions = implode('; ', $missions);
            $offre->profil_rechercher = implode('; ', $profils);
            $offre->competence_requises = implode('; ', $competences);
            $offre->prerequis = !empty($prerequis) ? implode('; ', $prerequis) : null;
            $offre->save();

            session()->flash('type', 'alert-success');
            session()->flash('message', 'Offre d\'emploi mise à jour avec succès');
            return redirect()->route('show-offres');
        } catch (\Exception $e) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Erreur lors de la mise à jour: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    /**
     * Supprimer une offre d'emploi recrutement
     */
    public function destroyOffreEmploiRecrutement($id)
    {
        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        try {
            $offre = Offre_emploi_recrutement::find($id);
            
            if (!$offre) {
                session()->flash('type', 'alert-danger');
                session()->flash('message', 'Offre d\'emploi introuvable!');
                return back();
            }

            $offre->delete();

            session()->flash('type', 'alert-success');
            session()->flash('message', 'Offre d\'emploi supprimée avec succès');
        } catch (\Exception $e) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Erreur lors de la suppression: ' . $e->getMessage());
        }

        return back();
    }

    /**
     * Afficher la liste des candidats
     */
    public function indexCandidatsRecrutement()
    {
        $data['title'] = 'Liste des candidats';
        $data['menu'] = 'candidats-recrutement';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        // Récupérer tous les candidats avec leurs offres associées
        $data["candidats"] = Recrutement_offre::with('offre')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('offres_emploi.candidats_recrutement', $data);
    }

    /**
     * Afficher les détails d'un candidat
     */
    public function showCandidatRecrutement($id)
    {
        $data['title'] = 'Détails du candidat';
        $data['menu'] = 'candidats-recrutement';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        $data["candidat"] = Recrutement_offre::with('offre')->find($id);
        
        if (!$data["candidat"]) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Candidat introuvable!');
            return redirect()->route('index-candidats-recrutement');
        }

        return view('offres_emploi.show_candidat_recrutement', $data);
    }

    /**
     * Supprimer un candidat
     */
    public function destroyCandidatRecrutement($id)
    {
        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
            return back();
        }

        try {
            $candidat = Recrutement_offre::find($id);
            
            if (!$candidat) {
                session()->flash('type', 'alert-danger');
                session()->flash('message', 'Candidat introuvable!');
                return back();
            }

            // Supprimer les fichiers associés si ils existent
            $baseUrl = 'https://tooauto.com/';
            
            // Note: Les fichiers sont stockés sur le serveur, vous devrez peut-être adapter cette partie
            // selon votre système de stockage de fichiers

            $candidat->delete();

            session()->flash('type', 'alert-success');
            session()->flash('message', 'Candidat supprimé avec succès');
        } catch (\Exception $e) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Erreur lors de la suppression: ' . $e->getMessage());
        }

        return back();
    }
    
}