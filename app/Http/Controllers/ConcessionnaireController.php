<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Userconcessionnaire;
use App\Models\Concessionnaire;
use App\Models\Vehicule_concessionnaire;
use App\Models\Marque;
use App\Models\Rdv_concessionnaire;
use App\Models\OffreConcessionnaire;
use App\Models\User;
use App\Models\AnnonceConcessionnaire;
use App\Models\GestionnaireDeFlotte;
use App\Models\Pays;
use App\Models\Ville;
use App\Models\Commune;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class ConcessionnaireController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['title'] ='Enregistrez votre établissement';
        $data['menu'] ='concessionnaire';

        $data['pays'] = Pays::all();
        $data['villes'] = Ville::all();
        $data['communes'] = Commune::all();

        $data['user'] = Auth::user();

        $data['pays'] = Pays::all();
        $data['villes'] = Ville::all();
        $data['communes'] = Commune::all();

        $data['concessionnaire'] = Concessionnaire::where('userconcessionnaire_id', auth()->user()->id)->first();

        if (!empty($data['concessionnaire'])) {
            return back();
        }

        return view('concessionnaire.create', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editConcessionnaire()
    {
        $data['title'] ='Modifier votre établissement';
        $data['menu'] ='etablissement-edit';

        $data['user'] = Auth::user();

        $data['concessionnaire'] = Concessionnaire::where('userconcessionnaire_id', auth()->user()->id)->first();
        if (empty($data['concessionnaire'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Vous n'avez pas encore enregistré votre établissement.");
            return view('concessionnaire.create', $data);
        }
        $data['pays'] = Pays::all();
        $data['villes'] = Ville::all();
        $data['communes'] = Commune::all();

        // dd($data['concessionnaire']);
        if (empty($data['concessionnaire'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Vous n'avez pas encore enregistré votre établissement.");
            return view('concessionnaire.create', $data);
        }

        return view('concessionnaire.edit', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeConcessionnaire(Request $request)
    {
        // dd($request->all());
        // Validation de la requête d'enregistrement de l'établissement
        $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'required|string|max:255|unique:concessionnaires,contact',
            'email' => 'required|email|max:255|unique:concessionnaires,email',
            'adresse' => 'nullable|string|max:255',
            'adresse_map' => 'nullable|string|max:255',
            'longitude' => 'nullable|string|max:255',
            'latitude' => 'nullable|string|max:255',
            'pays_id' => 'required|integer|exists:pays,id',
            'ville_id' => 'required|integer|exists:villes,id',
            'commune_id' => 'required|integer|exists:communes,id',
            'description' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:8048',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:8048',
            'is_whatsapp' => 'required|string',
            'mobile_fix' => 'nullable|string|unique:concessionnaires,mobile_fix',
        ]);

        DB::beginTransaction();
        try {

            $user = Auth::user();

            // Gestion des images
            $logoPath = $coverPath = null;

            if($request->file('logo')) {
                $logo = $request->file('logo');
                $logoName = 'logo-' . time() . '.' . $logo->getClientOriginalExtension();
                $logo->move(public_path('concessionnaire/logo'), $logoName);
                $logoPath = $logoName;
            }

            if($request->file('cover')) {
                $cover = $request->file('cover');
                $coverName = 'cover-' . time() . '.' . $cover->getClientOriginalExtension();
                $cover->move(public_path('concessionnaire/cover'), $coverName);
                $coverPath = $coverName;
            }

            // Création de l'établissement
            $concessionnaire = Concessionnaire::create([
                'name' => html_entity_decode($request->name),
                'contact' => html_entity_decode($request->contact),
                'email' => html_entity_decode($request->email),
                'description' => html_entity_decode($request->description),
                'logo' => $logoPath,
                'cover' => $coverPath,
                'adresse' => html_entity_decode($request->adresse),
                'longitude' => $request->longitude,
                'latitude' => $request->latitude,
                'userconcessionnaire_id' => $user->id,
                'pays_id' => $request->pays_id,
                'ville_id' => $request->ville_id,
                'commune_id' => $request->commune_id,
                'is_whatsapp' => $request->is_whatsapp,
                'mobile_fix' => $request->mobile_fix,
            ]);

            DB::commit();

            session()->flash('type', 'alert-success');
            session()->flash('message', "Établissement créé avec succès avec un abonnement Free.");
            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Erreur lors de la création de l'établissement : " . $e->getMessage());
            return back();
        }
    }

    public function updateConcessionnaire(Request $request, $id)
    {
        $concessionnaire = Concessionnaire::where(['id' => $id, 'userconcessionnaire_id' => auth()->user()->id])->first();
        if (empty($concessionnaire)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Établissement introuvable.");
            return back();
        }
        // Validation de la requête de mise à jour
        $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'required|string|max:255|unique:concessionnaires,contact,' . $concessionnaire->id,
            'email' => 'required|email|max:255|unique:concessionnaires,email,' . $concessionnaire->id,
            'adresse' => 'nullable|string|max:255',
            'adresse_map' => 'nullable|string|max:255',
            'longitude' => 'nullable|string|max:255',
            'latitude' => 'nullable|string|max:255',
            'pays_id' => 'required|integer|exists:pays,id',
            'ville_id' => 'required|integer|exists:villes,id',
            'commune_id' => 'required|integer|exists:communes,id',
            'description' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:8048',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:8048',
            'is_whatsapp' => 'required|string',
            'mobile_fix' => 'nullable|string|unique:concessionnaires,mobile_fix,' . $concessionnaire->id,
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();

            // Gestion des images
            $logoPath = $concessionnaire->logo;
            $coverPath = $concessionnaire->cover;

            if ($request->file('logo')) {
                // Supprimer l'ancien logo s'il existe
                if ($logoPath && File::exists(public_path('concessionnaire/logo/' . $logoPath))) {
                    File::delete(public_path('concessionnaire/logo/' . $logoPath));
                }
                $logo = $request->file('logo');
                $logoName = 'logo-' . time() . '.' . $logo->getClientOriginalExtension();
                $logo->move(public_path('concessionnaire/logo'), $logoName);
                $logoPath = $logoName;
            }

            if ($request->file('cover')) {
                // Supprimer l'ancienne couverture s'il existe
                if ($coverPath && File::exists(public_path('concessionnaire/cover/' . $coverPath))) {
                    File::delete(public_path('concessionnaire/cover/' . $coverPath));
                }
                $cover = $request->file('cover');
                $coverName = 'cover-' . time() . '.' . $cover->getClientOriginalExtension();
                $cover->move(public_path('concessionnaire/cover'), $coverName);
                $coverPath = $coverName;
            }

            // Mise à jour du concessionnaire
            $concessionnaire->update([
                'name' => html_entity_decode($request->name),
                'contact' => html_entity_decode($request->contact),
                'email' => html_entity_decode($request->email),
                'description' => html_entity_decode($request->description),
                'logo' => $logoPath,
                'cover' => $coverPath,
                'adresse' => html_entity_decode($request->adresse),
                'adresse_map' => html_entity_decode($request->adresse_map),
                'longitude' => $request->longitude,
                'latitude' => $request->latitude,
                'userconcessionnaire_id' => $user->id,
                'pays_id' => $request->pays_id,
                'ville_id' => $request->ville_id,
                'commune_id' => $request->commune_id,
                'is_whatsapp' => $request->is_whatsapp,
                'mobile_fix' => $request->mobile_fix ? $request->mobile_fix : null,
            ]);

            DB::commit();

            session()->flash('type', 'alert-success');
            session()->flash('message', "Établissement mis à jour avec succès.");
            return back();

        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Erreur lors de la mise à jour de l'établissement : " . $e->getMessage());
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyConcessionnaire(Request $request, $id)
    {
        $concessionnaire = Concessionnaire::where(['id' => $id, 'userconcessionnaire_id' => auth()->user()->id])->first();
        if (empty($concessionnaire)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Établissement introuvable.");
            return back();
        }

        DB::beginTransaction();
        try {
            // Supprimer les fichiers associés (logo et cover) s'ils existent
            if ($concessionnaire->logo && File::exists(public_path('concessionnaire/logo/' . $concessionnaire->logo))) {
                File::delete(public_path('concessionnaire/logo/' . $concessionnaire->logo));
            }

            if ($concessionnaire->cover && File::exists(public_path('concessionnaire/cover/' . $concessionnaire->cover))) {
                File::delete(public_path('concessionnaire/cover/' . $concessionnaire->cover));
            }

            // Supprimer le concessionnaire de la base de données
            $concessionnaire->delete();

            DB::commit();

            session()->flash('type', 'alert-success');
            session()->flash('message', "Établissement supprimé avec succès.");
            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Erreur lors de la suppression de l'établissement : " . $e->getMessage());
            return back();
        }
    }

    public function indexVehicule()
    {
        $data['title'] ='Enregistrez vos véhicules';
        $data['menu'] ='vehicule';

        $data['pays'] = Pays::all();
        $data['villes'] = Ville::all();
        $data['communes'] = Commune::all();

        $data['user'] = Auth::user();

        $data['concessionnaire'] = Concessionnaire::where('userconcessionnaire_id', auth()->user()->id)->first();
        $data['marques'] = Marque::all();
        if (empty($data['concessionnaire'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Vous n'avez pas encore enregistré votre établissement.");
            return view('concessionnaire.create', $data);
        }
        $data['vehiculeConcessionnaires'] = Vehicule_concessionnaire::where([
            'concessionnaire_id' => $data['concessionnaire']->id,
        ])
        ->with('marque')
        ->get();

        if (empty($data['concessionnaire'])) {
            return back();
        }

        return view('vehicule.index', $data);
    }

    public function storeVehicule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:200',
            'garantie' => 'required|string|max:200',
            'marque_id' => 'required|exists:marques,id',
            'modele' => 'required|string|max:200',
            'prix' => 'required|integer',
            'description' => 'required|string',
            'photos.*' => 'image|mimes:jpeg,png,jpg|max:10048', // Max 2MB par image
			'fichier' => 'nullable|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $concessionnaire = Concessionnaire::where('userconcessionnaire_id', auth()->user()->id)->first();

        $photos = [];
        if ($request->hasFile('photos')) {
            $files = $request->file('photos');

            // Vérifier si $files est un tableau ou un seul fichier
            if (!is_array($files)) {
                $files = [$files]; // Convertir en tableau si c'est un seul fichier
            }

            if (count($files) > 4) {
                session()->flash('type', 'alert-danger');
                session()->flash('message', "Maximum 4 photos autorisées");
                return back();
            }

            foreach ($files as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $destinationPath = public_path('images/vehicules'); // Dossier public/images/vehicules
                $file->move($destinationPath, $filename);
                $photos[] = 'images/vehicules/' . $filename; // Stocker le chemin relatif
            }
        }

		$imagePath = null;

        if($request->file('fichier')) {
            $image = $request->file('fichier');
            $imageName = 'fichier-' . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('fichiers/vehicules'), $imageName);
            $imagePath = $imageName;
        }

        $vehicule = Vehicule_concessionnaire::create([
            'name' => $request->name,
			'garantie' => $request->garantie,
            'concessionnaire_id' => $concessionnaire->id,
            'marque_id' => $request->marque_id,
            'modele' => $request->modele,
            'prix' => $request->prix,
            'description' => $request->description,
            'photos' => $photos,
			'fichier' => $imagePath,
        ]);

        session()->flash('type', 'alert-success');
        session()->flash('message', "Vehicule enregistrer avec succès.");
        return back();

    }

    public function updateVehicule(Request $request, $id)
    {
        $vehicule = Vehicule_concessionnaire::find($id);
        if (!$vehicule) {
            session()->flash('type', 'alert-success');
            session()->flash('message', "Véhicule non trouvé.");
            return back();
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:200',
            'garantie' => 'sometimes|string|max:200',
            'marque_id' => 'sometimes|exists:marques,id',
            'modele' => 'sometimes|string|max:200',
            'prix' => 'sometimes|integer',
            'description' => 'sometimes|string',
            'photos.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Si l'utilisateur veut remplacer les photos
        $photos = $vehicule->photos ?? [];
        if ($request->hasFile('photos')) {
            $files = $request->file('photos');
            if (count($files) > 4) {
                session()->flash('type', 'alert-danger');
                session()->flash('message', "Maximum 4 photos autorisées");
                return back();
            }

            // Supprimer les anciennes photos du serveur
            foreach ($photos as $photo) {
                $photoPath = public_path($photo);
                if (file_exists($photoPath)) {
                    unlink($photoPath);
                }
            }

            // Enregistrer les nouvelles photos
            $photos = [];
            foreach ($files as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $destinationPath = public_path('images/vehicules');
                $file->move($destinationPath, $filename);
                $photos[] = 'images/vehicules/' . $filename;
            }
        }

		// Suppression et mise à jour de l'image si une nouvelle est fournie
		$imagePath = $vehicule->fichier ?? [];;
        if ($request->hasFile('fichier')) {
            // Suppression de l'ancienne image
            if ($vehicule->fichier && file_exists(public_path('fichiers/vehicules/' . $vehicule->fichier))) {
                unlink(public_path('fichiers/vehicules/' . $vehicule->fichier));
            }

            // Enregistrement de la nouvelle image
            $image = $request->file('fichier');
            $imageName = 'fichier-' . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('fichiers/vehicules'), $imageName);
            $imagePath = $imageName;
        }

        // Mise à jour des autres champs
        $vehicule->update([
            'name' => $request->name ?? $vehicule->name,
            'garantie' => $request->garantie ?? $vehicule->garantie,
            'marque_id' => $request->marque_id ?? $vehicule->marque_id,
            'modele' => $request->modele ?? $vehicule->modele,
            'prix' => $request->prix ?? $vehicule->prix,
            'description' => $request->description ?? $vehicule->description,
            'photos' => $photos,
			'fichier' => $imagePath,
        ]);

        session()->flash('type', 'alert-success');
        session()->flash('message', "Vehicule mise a jour avec succès.");
        return back();
    }


    public function destroyVehicule($id)
    {
        $vehicule = Vehicule_concessionnaire::find($id);
        if (!$vehicule) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Véhicule non trouvé.");
            return back();
        }

        // Supprimer les photos du dossier public
        if ($vehicule->photos) {
            foreach ($vehicule->photos as $photo) {
                $photoPath = public_path($photo);
                if (file_exists($photoPath)) {
                    unlink($photoPath);
                }
            }
        }
		// Supprime l'image associée, si elle existe
        if ($vehicule->fichier && file_exists(public_path('fichiers/vehicules/' . $vehicule->fichier))) {
			unlink(public_path('fichiers/vehicules/' . $vehicule->fichier));
		}


        $vehicule->delete();
        session()->flash('type', 'alert-success');
        session()->flash('message', "Véhicule supprimé avec succès.");
        return back();

    }


    public function indexAnnonce()
    {
        $data['title'] ='Les annonces';
        $data['menu'] ='annonce';

        $data['pays'] = Pays::all();
        $data['villes'] = Ville::all();
        $data['communes'] = Commune::all();

        $data['user'] = Auth::user();

        $data['concessionnaire'] = Concessionnaire::where('userconcessionnaire_id', auth()->user()->id)->first();

        if (empty($data['concessionnaire'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Vous n'avez pas encore enregistré votre établissement.");
            return view('concessionnaire.create', $data);
        }

        $data['marques'] = Marque::all();

        $data['annonces'] = AnnonceConcessionnaire::where([
            'concessionaire_id' => $data['concessionnaire']->id,
            'statut' => 1
        ])
        ->with('marque', 'typeDePiece', 'typeDeVehicule', 'typeDeDemande', 'user')
        ->get();

        // dd($data['annonceconcessionnaires']);

        if (empty($data['annonces'])) {
            return back();
        }

        return view('annonce.index', $data);
    }

    public function indexRdvAnnonce()
    {
        $data['title'] ='Les rendez-vous';
        $data['menu'] ='rdv';

        $data['pays'] = Pays::all();
        $data['villes'] = Ville::all();
        $data['communes'] = Commune::all();

        $data['user'] = Auth::user();

        $data['concessionnaire'] = Concessionnaire::where('userconcessionnaire_id', auth()->user()->id)->first();
        $data['marques'] = Marque::all();

        if (empty($data['concessionnaire'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Vous n'avez pas encore enregistré votre établissement.");
            return view('concessionnaire.create', $data);
        }

        // dd($data['concessionnaire']);

        $data['rdv_concessionnaires'] = Rdv_concessionnaire::where([
            'concessionnaire_id' => $data['concessionnaire']->id
        ])
        ->with('concessionnaire')
        ->get();


        if (empty($data['rdv_concessionnaires'])) {
            return back();
        }

        return view('rdv.index', $data);
    }

    public function storeRdvAnnonce(Request $request, $id)
    {
        // Validation des données
        $validatedData = $request->validate([
            'jour' => 'nullable|string|in:Lundi,Mardi,Mercredi,Jeudi,Vendredi,Samedi,Dimanche',
            'heure' => 'nullable|date_format:Y-m-d\TH:i',
            'statut' => 'required|integer',
            'reponse_concessionnaire' => 'required_if:statut,3|string',
        ]);

        // Récupérer le concessionnaire connecté
        $concessionnaire = Concessionnaire::where('userconcessionnaire_id', auth()->id())->first();

        if (!$concessionnaire) {
            return back()->with('type', 'alert-danger')->with('message', "Utilisateur introuvable.");
        }

        // Récupérer le rendez-vous
        $rdvUpdate = Rdv_concessionnaire::where('concessionnaire_id', $concessionnaire->id)
            ->where('id', $id)
            ->first();

        if (!$rdvUpdate) {
            return back()->with('type', 'alert-danger')->with('message', "Rendez-vous introuvable.");
        }

        if ($request->statut == 3) {
            // Création d'un nouveau rendez-vous
            Rdv_concessionnaire::create([
                'jour' => $request->jour,
                'heure' => $request->heure,
                'concessionnaire_id' => $concessionnaire->id,
                'user_id' => $concessionnaire->user_id,
                'gestionnaire_de_flotte_id' => $concessionnaire->gestionnaire_de_flotte_id,
                'statut' => $request->statut,
                'reponse_concessionnaire' => $request->reponse_concessionnaire,
            ]);

            return back()->with('type', 'alert-success')->with('message', "Rendez-vous enregistré avec succès.");
        } else {
            // Mise à jour du rendez-vous existant
            $rdvUpdate->update([
                'statut' => $request->statut,
                'reponse_concessionnaire' => $request->reponse_concessionnaire,
            ]);

            return back()->with('type', 'alert-success')->with('message', "Rendez-vous mis à jour avec succès.");
        }
    }


    public function indexGestionnaireDeFlotte()
    {
        $data['title'] ='Les gestionnaires de flotte';
        $data['menu'] ='flotte';

        $data['pays'] = Pays::all();
        $data['villes'] = Ville::all();
        $data['communes'] = Commune::all();

        $data['user'] = Auth::user();

        $data['concessionnaire'] = Concessionnaire::where('userconcessionnaire_id', auth()->user()->id)->first();
        if (empty($data['concessionnaire'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Vous n'avez pas encore enregistré votre établissement.");
            return view('concessionnaire.create', $data);
        }
        $data['gestionnaireDeFlottes'] = GestionnaireDeFlotte::where('statut', 1)->get();


        return view('annonce.flotte', $data);
    }

    public function storeOffre(Request $request)
    {
        $request->validate([
            'offre' => 'required|mimes:pdf|max:10000', // PDF, max 10MB
            'gestionnaire_de_flotte_id' => 'nullable|integer',
        ]);

        $data['user'] = Auth::user();

        $concessionnaire = Concessionnaire::where('userconcessionnaire_id', auth()->user()->id)->first();

        $offrePath = null;
        if ($request->hasFile('offre')) {
            $file = $request->file('offre');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('offres');
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }
            $file->move($destinationPath, $filename);
            $offrePath = 'offres/' . $filename;
        }

        OffreConcessionnaire::create([
            'offre' => $offrePath,
            'concessionnaire_id' => $concessionnaire->id,
            'gestionnaire_de_flotte_id' => $request->gestionnaire_de_flotte_id,
        ]);

        session()->flash('type', 'alert-success');
        session()->flash('message', "Offre envoyer avec succès...");
        return back();
    }
}