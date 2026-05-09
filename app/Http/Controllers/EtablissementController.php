<?php

namespace App\Http\Controllers;

use App\Models\Etablissement;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Article;
use App\Models\Type_etablissement;
use App\Models\TypeDePrestation;
use App\Models\Cabinet_expertise;
use App\Models\Promotion;
use App\Models\Abonnement_pro;
use App\Models\Commissariat;
use App\Models\Categorie_service;
use App\Models\Station_service;
use App\Models\Commune;
use App\Models\TypeEtablissement;
use App\Models\Pays;
use App\Models\Ville;
use App\Models\Service;
use App\Models\Sapeur_pompier;
use App\Models\Professionnel;
use App\Models\Type_de_prestation;
use App\Models\Type_de_piece;
use App\Models\Type_de_demande;
use App\Models\Super;
use Validator;
use App\Models\Type_alert;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Response;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Services\WasabiService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class EtablissementController extends Controller
{
    protected $wasabiService;

    public function __construct(WasabiService $wasabiService)
    {
        $this->wasabiService = $wasabiService;
    }

    /**
     * Display a listing of the resource for admin interface.
     */
    public function indexEtablissement(Request $request)
    {
        $data['title'] = 'Liste des établissements';
        $data['menu'] = 'etablissement';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        $now = Carbon::now();
        $data['stats'] = [
            'today' => Etablissement::whereDate('created_at', $now->toDateString())->count(),
            'this_week' => Etablissement::whereBetween('created_at', [
                $now->copy()->startOfWeek(),
                $now->copy()->endOfWeek(),
            ])->count(),
            'current_month' => Etablissement::whereYear('created_at', $now->year)
                ->whereMonth('created_at', $now->month)
                ->count(),
            'previous_month' => Etablissement::whereYear('created_at', $now->copy()->subMonth()->year)
                ->whereMonth('created_at', $now->copy()->subMonth()->month)
                ->count(),
            'total' => Etablissement::count(),
        ];

        $search = trim((string) $request->get('search', ''));
        $typeEtablissementId = $request->get('type_etablissement_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = Etablissement::with('professionnel', 'typeEtablissement', 'parrain.commercial', 'pays', 'ville', 'commune')
            ->orderBy('id', 'desc');

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', '%' . $search . '%')
                    ->orWhere('mobile', 'like', '%' . $search . '%')
                    ->orWhere('adresse', 'like', '%' . $search . '%')
                    ->orWhereHas('typeEtablissement', function ($typeQuery) use ($search) {
                        $typeQuery->where('libelle', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('professionnel', function ($professionnelQuery) use ($search) {
                        $professionnelQuery->where('nom', 'like', '%' . $search . '%')
                            ->orWhere('prenoms', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('parrain.commercial', function ($commercialQuery) use ($search) {
                        $commercialQuery->where('nom', 'like', '%' . $search . '%')
                            ->orWhere('prenoms', 'like', '%' . $search . '%')
                            ->orWhere('mobile', 'like', '%' . $search . '%');
                    });
            });
        }

        if (!empty($typeEtablissementId)) {
            $query->where('type_etablissement_id', $typeEtablissementId);
        }

        if (!empty($dateFrom)) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if (!empty($dateTo)) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $data["etablissements"] = $query->paginate(25)->withQueryString();
        $data["etablissements"]->getCollection()->transform(function ($etablissement) {
            $etablissement->logo_url = $this->getSignedEtablissementMediaUrl($etablissement->logo, 'etablissement/logo');
            return $etablissement;
        });
        $data["typeEtablissements"] = TypeEtablissement::orderBy('libelle', 'asc')->get(['id', 'libelle']);
        $data["filters"] = [
            'search' => $search,
            'type_etablissement_id' => $typeEtablissementId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];


        return view('etablissements.index', $data);
    }

    public function showEtablissement($id)
    {
        $data['title'] = "Détails de l'établissement";
        $data['menu'] = 'etablissement';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        $etablissement = Etablissement::with([
            'professionnel',
            'typeEtablissement',
            'categorieService',
            'parrain.commercial',
            'pays',
            'ville',
            'commune',
            'articles' => function ($query) {
                $query->latest()->limit(5);
            },
            'annonces' => function ($query) {
                $query->latest()->limit(5);
            },
        ])->findOrFail($id);

        $allTypesPrestations = Type_de_prestation::all()->keyBy('id');

        $etablissement->logo_url = $this->getSignedEtablissementMediaUrl($etablissement->logo, 'etablissement/logo');
        $etablissement->cover_url = $this->getSignedEtablissementMediaUrl($etablissement->cover, 'etablissement/cover');
        $etablissement->types_prestations_libelles = $this->getTypesPrestationsLibelles($etablissement->type_de_prestations, $allTypesPrestations);
        $etablissement->types_prestations_complets = $this->getTypesPrestationsComplets($etablissement->type_de_prestations, $allTypesPrestations);
        $etablissement->articles->transform(function ($article) {
            $article->image_url = $this->getSignedEtablissementMediaUrl($article->image, 'articles/image');
            return $article;
        });
        $etablissement->annonces->transform(function ($annonce) {
            $annonce->image_url = $this->getAnnonceImageUrl($annonce->image ?? null);
            return $annonce;
        });

        $data['etablissement'] = $etablissement;
        $data['articlesCount'] = Article::where('etablissement_id', $etablissement->id)->count();
        $data['annoncesCount'] = $etablissement->annonces()->count();
        $data = array_merge($data, $this->getEtablissementFormData($etablissement->professionnel_id));

        return view('etablissements.show', $data);
    }

    public function updateEtablissement(Request $request, $id)
    {
        $etablissement = Etablissement::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'specialite' => 'nullable|string|max:255',
            'adresse' => 'required|string|max:255',
            'adresse_map' => 'nullable|string|max:255',
            'indicatif' => 'nullable|string|max:10',
            'mobile' => 'nullable|string|max:30',
            'mobile_fix' => 'nullable|string|max:30',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('etablissements', 'email')->ignore($etablissement->id),
            ],
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'type_etablissement_id' => 'nullable|exists:type_etablissements,id',
            'categorie_service_id' => 'nullable|exists:categorie_services,id',
            'pays_id' => 'nullable|exists:pays,id',
            'ville_id' => 'nullable|exists:villes,id',
            'commune_id' => 'nullable|exists:communes,id',
            'professionnel_id' => 'required|exists:professionnels,id',
            'owner_nom' => 'nullable|string|max:255',
            'owner_prenoms' => 'nullable|string|max:255',
            'owner_role' => 'nullable|string|max:255',
            'owner_email' => 'nullable|email|max:255',
            'owner_mobile' => 'nullable|string|max:255',
            'code_parrain' => 'nullable|string|max:255',
            'logo' => 'nullable|string|max:255',
            'cover' => 'nullable|string|max:255',
            'logo_where_is_create' => 'nullable|integer',
            'cover_where_is_create' => 'nullable|integer',
            'logo_create_by' => 'nullable|integer',
            'cover_create_by' => 'nullable|integer',
            'is_whatsapp' => 'required|boolean',
            'service_mobile' => 'required|boolean',
            'statut' => 'required|boolean',
            'type_de_prestations' => 'nullable|array',
            'type_de_prestations.*' => 'integer|exists:type_de_prestations,id',
        ]);

        $validated['type_de_prestations'] = !empty($validated['type_de_prestations'])
            ? json_encode(array_map('intval', $validated['type_de_prestations']))
            : $etablissement->type_de_prestations;

        $etablissement->name = $validated['name'];
        $etablissement->description = $validated['description'] ?? null;
        $etablissement->specialite = $validated['specialite'] ?? null;
        $etablissement->adresse = $validated['adresse'];
        $etablissement->adresse_map = $validated['adresse_map'] ?? null;
        $etablissement->indicatif = $validated['indicatif'] ?? null;
        $etablissement->mobile = $validated['mobile'] ?? null;
        $etablissement->mobile_fix = $validated['mobile_fix'] ?? null;
        $etablissement->email = $validated['email'] ?? null;
        $etablissement->latitude = $validated['latitude'] ?? null;
        $etablissement->longitude = $validated['longitude'] ?? null;
        $etablissement->type_etablissement_id = $validated['type_etablissement_id'] ?? $etablissement->type_etablissement_id;
        $etablissement->categorie_service_id = $validated['categorie_service_id'] ?? $etablissement->categorie_service_id;
        $etablissement->pays_id = $validated['pays_id'] ?? $etablissement->pays_id;
        $etablissement->ville_id = $validated['ville_id'] ?? $etablissement->ville_id;
        $etablissement->commune_id = $validated['commune_id'] ?? $etablissement->commune_id;
        $etablissement->professionnel_id = $validated['professionnel_id'];
        $etablissement->code_parrain = $validated['code_parrain'] ?? null;
        $etablissement->logo = $validated['logo'] ?? null;
        $etablissement->cover = $validated['cover'] ?? null;
        $etablissement->logo_where_is_create = $validated['logo_where_is_create'] ?? null;
        $etablissement->cover_where_is_create = $validated['cover_where_is_create'] ?? null;
        $etablissement->logo_create_by = $validated['logo_create_by'] ?? null;
        $etablissement->cover_create_by = $validated['cover_create_by'] ?? null;
        $etablissement->is_whatsapp = (int) $validated['is_whatsapp'];
        $etablissement->service_mobile = (int) $validated['service_mobile'];
        $etablissement->statut = (int) $validated['statut'];
        $etablissement->type_de_prestations = $validated['type_de_prestations'];
        // dd($etablissement);
        $etablissement->save();

        $professionnel = null;

        if (!empty($etablissement->professionnel_id)) {
            $professionnel = Professionnel::find($etablissement->professionnel_id);
        }

        if ($professionnel) {
            $professionnel->nom = $validated['owner_nom'] ?? $professionnel->nom;
            $professionnel->prenoms = $validated['owner_prenoms'] ?? $professionnel->prenoms;
            $professionnel->role = $validated['owner_role'] ?? $professionnel->role;
            $professionnel->email = $validated['owner_email'] ?? $professionnel->email;
            $professionnel->mobile = $validated['owner_mobile'] ?? $professionnel->mobile;
            $professionnel->save();
        }

        return redirect()
            ->route('show-etablissement', $etablissement->id)
            ->with('message', "Les informations de l'établissement ont été mises à jour avec succès.")
            ->with('type', 'alert-success');
    }

    public function destroyEtablissement($id)
    {
        $etablissement = Etablissement::with(['articles', 'promotions', 'services', 'annonces', 'abonnementsPro'])->findOrFail($id);

        DB::transaction(function () use ($etablissement) {
            foreach ($etablissement->articles as $article) {
                if (!empty($article->image)) {
                    $this->wasabiService->deleteFile($article->image);
                }

                $article->delete();
            }

            foreach ($etablissement->promotions as $promotion) {
                if (!empty($promotion->image)) {
                    Storage::delete('public/promotions/' . $promotion->image);
                }

                $promotion->delete();
            }

            foreach ($etablissement->services as $service) {
                $service->delete();
            }

            foreach ($etablissement->abonnementsPro as $abonnementPro) {
                $abonnementPro->delete();
            }

            $etablissement->annonces()->detach();

            if (DB::getSchemaBuilder()->hasTable('etablissement_type_prestations')) {
                DB::table('etablissement_type_prestations')
                    ->where('etablissement_id', $etablissement->id)
                    ->delete();
            }

            if (!empty($etablissement->logo)) {
                $this->wasabiService->deleteFile($etablissement->logo);
            }

            if (!empty($etablissement->cover)) {
                $this->wasabiService->deleteFile($etablissement->cover);
            }

            $etablissement->delete();
        });

        return redirect()
            ->route('index-etablissements')
            ->with('message', "L'établissement et ses données liées ont été supprimés définitivement.")
            ->with('type', 'alert-success');
    }

    public function showEtablissementArticles($id)
    {
        $data['title'] = "Articles de l'établissement";
        $data['menu'] = 'etablissement';
        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        $data['etablissement'] = Etablissement::findOrFail($id);
        $data['articles'] = Article::where('etablissement_id', $id)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $data['articles']->getCollection()->transform(function ($article) {
            $article->image_url = $this->getSignedEtablissementMediaUrl($article->image, 'articles/image');
            return $article;
        });

        return view('etablissements.articles', $data);
    }

    public function showEtablissementAnnonces($id)
    {
        $data['title'] = "Annonces de l'établissement";
        $data['menu'] = 'etablissement';
        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        $data['etablissement'] = Etablissement::findOrFail($id);
        $data['annonces'] = $data['etablissement']->annonces()
            ->with('marque', 'currentUser', 'type_de_piece')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $data['annonces']->getCollection()->transform(function ($annonce) {
            $annonce->image_url = $this->getAnnonceImageUrl($annonce->image ?? null);
            return $annonce;
        });

        return view('etablissements.annonces', $data);
    }

    private function getSignedEtablissementMediaUrl($value, $directory)
    {
        if (empty($value)) {
            return null;
        }

        $path = $this->normalizeEtablissementMediaPath($value, $directory);

        try {
            return $this->wasabiService->temporaryUrl($path) ?? $path;
        } catch (\Throwable $e) {
            return $path;
        }
    }

    private function normalizeEtablissementMediaPath($value, $directory)
    {
        if (empty($value) || filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        if (Str::contains($value, '/')) {
            return ltrim($value, '/');
        }

        return trim($directory, '/') . '/' . ltrim($value, '/');
    }

    private function getAnnonceImageUrl($value)
    {
        if (empty($value)) {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        if (Str::contains($value, '/')) {
            return asset(ltrim($value, '/'));
        }

        return asset('images/annonces/' . ltrim($value, '/'));
    }

    private function getEtablissementFormData($professionnelId = null)
    {
        $professionnelsList = DB::table('professionnels')
            ->select('id', 'nom', 'prenoms', 'mobile')
            ->orderBy('nom')
            ->orderBy('prenoms')
            ->limit(500)
            ->get();

        if (!empty($professionnelId) && !$professionnelsList->contains('id', $professionnelId)) {
            $professionnel = DB::table('professionnels')
                ->select('id', 'nom', 'prenoms', 'mobile')
                ->where('id', $professionnelId)
                ->first();

            if ($professionnel) {
                $professionnelsList->prepend($professionnel);
            }
        }

        return [
            'typeEtablissementsList' => TypeEtablissement::orderBy('libelle')->get(['id', 'libelle']),
            'categorieServicesList' => Categorie_service::orderBy('libelle')->get(['id', 'libelle']),
            'paysList' => Pays::orderBy('libelle')->get(['id', 'libelle']),
            'villesList' => Ville::orderBy('libelle')->get(['id', 'libelle', 'pays_id']),
            'communesList' => Commune::orderBy('nom')->get(['id', 'nom', 'ville_id']),
            'professionnelsList' => $professionnelsList,
            'typesPrestationsList' => Type_de_prestation::orderBy('libelle')->get(['id', 'libelle']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Récupérer les établissements triés par ID décroissant
        $etablissements = Etablissement::orderBy('id', 'desc')->get();

        // Vérifier si des établissements existent
        if ($etablissements->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun établissement enregistré pour le moment.',
            ], 404);
        }

        // Récupérer tous les types de prestations en une seule requête
        $allTypesPrestations = Type_de_prestation::all()->keyBy('id');

        // Ajouter les libellés des types de prestations à chaque établissement
        $etablissements->transform(function ($etablissement) use ($allTypesPrestations) {
            // Méthode simple et directe
            $etablissement->types_prestations_libelles = $this->getTypesPrestationsLibelles($etablissement->type_de_prestations, $allTypesPrestations);
            $etablissement->types_prestations_complets = $this->getTypesPrestationsComplets($etablissement->type_de_prestations, $allTypesPrestations);

            // Ajouter l'indicatif '+225' au champ mobile s'il existe
            if (!empty($etablissement->mobile)) {
                $etablissement->mobile = '+225' . $etablissement->mobile;
            }

            return $etablissement;
        });

        // Retourner la liste des établissements
        return response()->json([
            'success' => true,
            'message' => 'Liste des établissements.',
            'etablissements' => $etablissements,
        ], 200);
    }

    /**
     * Récupérer les libellés des types de prestations
     */
    private function getTypesPrestationsLibelles($typeDePrestationsJson, $allTypesPrestations)
    {
        if (empty($typeDePrestationsJson)) {
            return [];
        }

        $ids = json_decode($typeDePrestationsJson, true);
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
     * Récupérer les types de prestations complets
     */
    private function getTypesPrestationsComplets($typeDePrestationsJson, $allTypesPrestations)
    {
        if (empty($typeDePrestationsJson)) {
            return [];
        }

        $ids = json_decode($typeDePrestationsJson, true);
        if (!is_array($ids)) {
            return [];
        }

        $types = [];
        foreach ($ids as $id) {
            if (isset($allTypesPrestations[$id])) {
                $types[] = $allTypesPrestations[$id];
            }
        }

        return $types;
    }

    /**
     * Display a listing of the resource.
     */
    public function getTypeEtablissement()
    {
        // Récupérer les établissements triés par ID décroissant
        $type_etablissements = Type_etablissement::orderBy('id', 'desc')->get();

        // Vérifier si des établissements existent
        if ($type_etablissements->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "Aucun type d'établissement enregistré pour le moment.",
            ], 404);
        }

        // Retourner la liste des établissements
        return response()->json([
            'success' => true,
            'message' => "Liste des type d'établissements.",
            'type_etablissements' => $type_etablissements,
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function getEtablissementByType(Request $request)
	{
		// Validation des données d'entrée
		$validator = Validator::make($request->all(), [
			'type_etablissement_id' => 'required|exists:type_etablissements,id',
			'longitude' => 'required|numeric',
			'latitude' => 'required|numeric',
		]);

		if ($validator->fails()) {
			return response()->json([
				'success' => false,
				'message' => 'Les données fournies ne sont pas valides.',
				'errors' => $validator->errors(),
			], 422);
		}

		$latitude = $request->latitude;
		$longitude = $request->longitude;

		// Requête pour récupérer les établissements triés par distance
		$etablissements = Etablissement::select(
			'*',
			DB::raw("
                CASE
                    WHEN longitude IS NULL OR latitude IS NULL THEN 0
                    ELSE (
                        6371 * acos(
                            cos(radians($latitude)) *
                            cos(radians(latitude)) *
                            cos(radians(longitude) - radians($longitude)) +
                            sin(radians($latitude)) *
                            sin(radians(latitude))
                        )
                    )
                END AS distance
            ")
		)
		->where('type_etablissement_id', $request->type_etablissement_id)
		->where('statut', 1)
		->with(['type_etablissement', 'pays', 'ville', 'commune'])
		->orderBy('distance', 'asc') // Trier par distance croissante
		->get();

		// Vérifier si des établissements existent
		if ($etablissements->isEmpty()) {
			return response()->json([
				'success' => false,
				'message' => 'Aucun établissement trouvé avec ces critères.',
			], 404);
		}

		// Retourner les établissements triés par proximité
		return response()->json([
			'success' => true,
			'message' => 'Liste des établissements triée par proximité.',
			'etablissements' => $etablissements,
		], 200);
	}


	    /**
     * Display a listing of the resource.
     */
    public function getTypeAlert()
    {
        // Récupérer les établissements triés par ID décroissant
        $type_alert = Type_alert::orderBy('id', 'desc')->get();

        // Vérifier si des établissements existent
        if ($type_alert->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "Aucun type d'alert enregistré pour le moment.",
            ], 404);
        }

        // Retourner la liste des établissements
        return response()->json([
            'success' => true,
            'message' => "Liste des type d'alert.",
            'type_alert' => $type_alert,
        ], 200);
    }

	    /**
     * Display a listing of the resource.
     */
    public function getArticleForEtablissement()
    {
        // Récupérer les établissements triés par ID décroissant
        $articles = Article::orderBy('id', 'desc')
        ->with(['etablissement' => function ($query) {
            $query->select('id', 'name', 'logo', 'mobile', 'longitude', 'latitude', 'mobile', 'mobile_fix');
        }])
        ->get();


        // Vérifier si des établissements existent
        if ($articles->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "Aucun article enregistré pour le moment.",
            ], 404);
        }

        // Retourner la liste des établissements
        return response()->json([
            'success' => true,
            'message' => "Liste des type d'alert.",
            'articles' => $articles,
        ], 200);
    }

	    /**
     * Display a listing of the resource.
     */
    public function getCategorieService()
    {
        $user = Auth::user();

        if (!empty($user->gestionnaire_de_flotte_id)) {
            // Récupérer les établissements triés par ID décroissant
            $categorie_services = Categorie_service::where(['statut' => 1, 'is_pro' => 1])
            ->orderBy('id', 'asc')->get();
        }else {
            // Récupérer les établissements triés par ID décroissant
            $categorie_services = Categorie_service::where('statut', 1)
            ->orderBy('id', 'asc')->get();
        }


        // Vérifier si des établissements existent
        if ($categorie_services->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "Aucune catégorie de service enregistré pour le moment.",
            ], 404);
        }

        // Retourner la liste des établissements
        return response()->json([
            'success' => true,
            'message' => "Liste des catégories de services.",
            'categorie_services' => $categorie_services,
        ], 200);
    }


    /**
     * Display a listing of the resource.
     */
    public function getTypeDePrestation(Request $request)
    {
        // Récupérer les établissements triés par ID décroissant
        $type_de_prestataire = Type_de_prestation::orderBy('id', 'asc')->get();

        // Vérifier si des établissements existent
        if ($type_de_prestataire->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "Aucun type de prestation enregistré pour le moment.",
            ], 404);
        }

        // Retourner la liste des établissements
        return response()->json([
            'success' => true,
            'message' => "Liste des types de prestation.",
            'type_de_prestataire' => $type_de_prestataire,
        ], 200);
    }


    /**
     * Display a listing of the resource.
     */
    public function getTypeDePiece()
    {
        // Récupérer les établissements triés par ID décroissant
        $type_de_piece = Type_de_piece::orderBy('id', 'desc')->get();

        // Vérifier si des établissements existent
        if ($type_de_piece->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "Aucun type de pièce enregistré pour le moment.",
            ], 404);
        }

        // Retourner la liste des établissements
        return response()->json([
            'success' => true,
            'message' => "Liste des type de pièce.",
            'type_de_piece' => $type_de_piece,
        ], 200);
    }


    /**
     * Display a listing of the resource.
     */
    public function getTypeDeDemande()
    {
        // Récupérer les établissements triés par ID décroissant
        $type_de_demande = Type_de_demande::orderBy('id', 'desc')->get();

        // Vérifier si des établissements existent
        if ($type_de_demande->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "Aucun type de demande enregistré pour le moment.",
            ], 404);
        }

        // Retourner la liste des établissements
        return response()->json([
            'success' => true,
            'message' => "Liste des type de pièce.",
            'type_de_demande' => $type_de_demande,
        ], 200);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function getEtablissementById(Request $request)
    {
        // Validation des données d'entrée
        $validator = Validator::make($request->all(), [
            'etablissement_id' => 'required|exists:etablissements,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Les données fournies ne sont pas valides.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Récupérer l'établissement avec ses relations
        $etablissement = Etablissement::where('id', $request->etablissement_id)
            ->with(['typeEtablissement', 'pays', 'ville', 'commune', 'articles'])
            ->first();

        // Vérifier si l'établissement est nul
        if (!$etablissement) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun établissement trouvé avec cet ID.',
            ], 404);
        }

        // Récupérer tous les types de prestations en une seule requête
        $allTypesPrestations = Type_de_prestation::all()->keyBy('id');

        // Ajouter les libellés des types de prestations à l'établissement
        $etablissement->types_prestations_libelles = $this->getTypesPrestationsLibelles($etablissement->type_de_prestations, $allTypesPrestations);
        $etablissement->types_prestations_complets = $this->getTypesPrestationsComplets($etablissement->type_de_prestations, $allTypesPrestations);

        // Ajouter les libellés des relations
        $etablissement->type_etablissement_libelle = $etablissement->typeEtablissement ? $etablissement->typeEtablissement->libelle : null;
        $etablissement->pays_libelle = $etablissement->pays ? $etablissement->pays->libelle : null;
        $etablissement->ville_libelle = $etablissement->ville ? $etablissement->ville->libelle : null;
        $etablissement->commune_libelle = $etablissement->commune ? $etablissement->commune->libelle : null;

        // Retourner les détails de l'établissement
        return response()->json([
            'success' => true,
            'message' => 'Détails de l\'établissement.',
            'etablissement' => $etablissement,
        ], 200);
    }

    /**
     * Récupérer les articles d'un établissement
     */
    public function getArticlesByEtablissement(Request $request)
    {
        // Validation des données d'entrée
        $validator = Validator::make($request->all(), [
            'etablissement_id' => 'required|exists:etablissements,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Les données fournies ne sont pas valides.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Récupérer les articles de l'établissement
        $articles = Article::where('etablissement_id', $request->etablissement_id)
            ->orderBy('id', 'desc')
            ->get();

        // Vérifier si des articles existent
        if ($articles->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun article trouvé pour cet établissement.',
            ], 404);
        }

        // Retourner la liste des articles
        return response()->json([
            'success' => true,
            'message' => 'Liste des articles de l\'établissement.',
            'articles' => $articles,
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function getEtablissementByCategorieService(Request $request)
    {
        // Validation des données d'entrée
        $validator = Validator::make($request->all(), [
            'categorie_service_id' => 'required|exists:categorie_services,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Les données fournies ne sont pas valides.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Récupérer l'établissement avec ses relations
        $etablissement = Etablissement::where('categorie_service_id', $request->categorie_service_id)
            ->where('statut', 1)
            ->with(['type_etablissement', 'pays', 'ville', 'commune'])
            ->get();

        // Vérifier si l'établissement est nul
        if (!$etablissement) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun établissement trouvé avec cet ID.',
            ], 404);
        }

        // Retourner les détails de l'établissement
        return response()->json([
            'success' => true,
            'message' => 'Liste des services de proximite.',
            'etablissements' => $etablissement,
        ], 200);
    }

    /**
     * Display the specified resource.
     */
   	public function searchAll(Request $request)
	{
		$searchTerm = $request->input('query');

		// Fonction pour formater les résultats
		$formatResult = function ($items, $etiquette) {
			return collect($items)->map(function ($item) use ($etiquette) {
				return [
					'id' => $item->id,
					'libelle_name' => $item->libelle_name ?? $item->name,
					'logo' => $item->image_logo ?? null,
					'adresse' => $item->adresse ?? null,
					'name' => $item->name ?? null,
					'description' => $item->description ?? null,
					'adresse_map' => $item->adresse_map ?? null,
					'mobile' => $item->mobile ?? null,
					'cover' => $item->cover ?? null,
					'longitude' => $item->longitude ?? null,
					'latitude' => $item->latitude ?? null,
					'etiquette' => $etiquette,
				];
			});
		};

		// Recherche dans la table services
		$services = Service::where('libelle', 'like', '%' . $searchTerm . '%')
			->leftJoin('etablissements', 'services.etablissement_id', '=', 'etablissements.id')
			->select(
				'services.id',
				'services.libelle as libelle_name',
				'services.image as image_logo',
				'etablissements.adresse',
				'etablissements.name',
				'etablissements.description',
				'etablissements.adresse_map',
				'etablissements.mobile',
				'etablissements.cover',
				'etablissements.longitude',
				'etablissements.latitude'
			)
			->get();

		// Recherche dans la table articles
		$articles = Article::where('libelle', 'like', '%' . $searchTerm . '%')
			->leftJoin('etablissements', 'articles.etablissement_id', '=', 'etablissements.id')
			->select(
				'articles.id',
				'articles.libelle as libelle_name',
				'articles.image as image_logo',
				'etablissements.adresse',
                'etablissements.name',
				'etablissements.description',
				'etablissements.adresse_map',
				'etablissements.mobile',
				'etablissements.cover',
				'etablissements.longitude',
				'etablissements.latitude'
			)
			->get();

		// Recherche dans la table etablissements
		$etablissements = Etablissement::where('name', 'like', '%' . $searchTerm . '%')
			->select(
				'id',
				'name as libelle_name',
			    'name',
				'logo as image_logo',
			    'logo',
				'adresse',
				'adresse_map',
				'description',
				'mobile',
				'cover',
				'longitude',
				'latitude'
			)
			->get();

		// Formatage des résultats
		$formattedServices = $formatResult($services, 'service');
		$formattedArticles = $formatResult($articles, 'article');
		$formattedEtablissements = $formatResult($etablissements, 'etablissement');

		// Fusionner les résultats en collections
		$results = collect()->merge($formattedServices)
			->merge($formattedArticles)
			->merge($formattedEtablissements);

		return response()->json($results);
	}


    public function searchArticleByEtablissement(Request $request, $id)
    {
        $searchTerm = $request->input('query');

        // Fonction pour formater les résultats
        $formatResult = function ($items, $etiquette) {
            return collect($items)->map(function ($item) use ($etiquette) {
                return [
                    'id' => $item->id,
                    'libelle_name' => $item->libelle_name ?? $item->name,
                    'image_logo' => $item->image_logo ?? null,
                    'description' => $item->description ?? null,
                    'amount' => $item->amount ?? null,
                    'etiquette' => $etiquette,
                ];
            });
        };

        // Recherche dans la table articles
        $articles = Article::where('libelle', 'like', '%' . $searchTerm . '%')
            ->where('etablissement_id', $id)
            ->select(
                'articles.id',
                'articles.libelle as libelle_name',
                'articles.image as image_logo',
                'articles.description',
                'articles.amount'
            )
            ->get();

        // Formatage des résultats
        $formattedArticles = $formatResult($articles, 'article');

        // Pas besoin de fusionner, car il n'y a qu'une seule collection
        return response()->json($formattedArticles);
    }

    public function searchServiceByEtablissement(Request $request, $id)
    {
        $searchTerm = $request->input('query');

        // Fonction pour formater les résultats
        $formatResult = function ($items, $etiquette) {
            return collect($items)->map(function ($item) use ($etiquette) {
                return [
                    'id' => $item->id,
                    'libelle_name' => $item->libelle_name ?? $item->name,
                    'image_logo' => $item->image_logo ?? null,
                    'description' => $item->description ?? null,
                    'amount_min' => $item->amount_min ?? null,
                    'etiquette' => $etiquette,
                ];
            });
        };

        // Recherche dans la table services
        $services = Service::where('libelle', 'like', '%' . $searchTerm . '%')
            ->where('etablissement_id', $id)
            ->select(
                'services.id',
                'services.libelle as libelle_name',
                'services.image as image_logo',
                'services.description',
                'services.amount_min'
            )
            ->get();

        // Formatage des résultats
        $formattedServices = $formatResult($services, 'service');

        // Pas besoin de fusionner, car il n'y a qu'une seule collection
        return response()->json($formattedServices);
    }


    /**
     * Display a listing of the resource.
     */
    public function getPromotionOfEtablissement()
    {
        // Récupérer la date actuelle (au format Y-m-d)
        $currentDate = Carbon::now()->toDateString(); // Format : 'YYYY-MM-DD'

        // Récupérer les promotions dont la date de fin n'est pas expirée et le statut est 1
        $promotions = Promotion::where('statut', 1)
                               ->whereDate('date_fin', '>=', $currentDate) // Utilisation de whereDate pour comparer les dates sans l'heure
                               ->orderBy('id', 'desc')
                               ->with('etablissement')
                               ->get();

        // Vérifier si des promotions existent
        if ($promotions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "Aucune promotion active pour le moment.",
            ], 404);
        }

        // Retourner la liste des promotions
        return response()->json([
            'success' => true,
            'message' => "Liste des promotions.",
            'promotions' => $promotions,
        ], 200);
    }

    /**
     * Display a listing of the resource.
     */
    public function getPromotionByEtablissement($id)
	{
		// Récupérer la date actuelle (au format Y-m-d)
		$currentDate = Carbon::now()->toDateString(); // Format : 'YYYY-MM-DD'

		// Récupérer la première promotion dont la date de fin n'est pas expirée et le statut est 1
		$promotion = Promotion::where(['statut' => 1, 'etablissement_id' => $id])
			->whereDate('date_fin', '>=', $currentDate) // Utilisation de whereDate pour comparer les dates sans l'heure
			->orderBy('id', 'desc')
			->with('etablissement')
			->first();

		// Vérifier si une promotion existe
		if (!$promotion) {
			return response()->json([
				'success' => false,
				'message' => "Aucune promotion active pour le moment.",
			], 404);
		}

		// Retourner la promotion
		return response()->json([
			'success' => true,
			'message' => "Promotion trouvée.",
			'promotion' => $promotion,
		], 200);
	}


    /**
     * Display a listing of the resource.
     */
    public function getAllCommissariatAgentConstat(Request $request)
	{
		// Validation des données d'entrée
		$validator = Validator::make($request->all(), [
			'longitude' => 'required|numeric',
			'latitude' => 'required|numeric',
		]);

		if ($validator->fails()) {
			return response()->json([
				'success' => false,
				'message' => 'Les données fournies ne sont pas valides.',
				'errors' => $validator->errors(),
			], 422);
		}

		$latitude = $request->latitude;
		$longitude = $request->longitude;

		// Requête pour récupérer les commissariats triés par distance
		$commissariats = Commissariat::select(
			'*', // Remplace '*' par les champs spécifiques si nécessaire
			DB::raw("
                CASE
                    WHEN longitude IS NULL OR latitude IS NULL THEN 0
                    ELSE (
                        6371 * acos(
                            cos(radians($latitude)) *
                            cos(radians(latitude)) *
                            cos(radians(longitude) - radians($longitude)) +
                            sin(radians($latitude)) *
                            sin(radians(latitude))
                        )
                    )
                END AS distance
            ")
		)
		->orderBy('distance', 'asc') // Trier par distance croissante
		->get();

		// Vérifier si des commissariats existent
		if ($commissariats->isEmpty()) {
			return response()->json([
				'success' => false,
				'message' => "Aucun commissariat trouvé.",
			], 404);
		}

		// Retourner la liste des commissariats triés par proximité
		return response()->json([
			'success' => true,
			'message' => "Liste des commissariats triée par proximité.",
			'commissariats' => $commissariats,
		], 200);
	}



    /**
     * Display a listing of the resource.
     */
	public function getAllStationServiceNormal(Request $request)
	{
		// Validation des données d'entrée
		$validator = Validator::make($request->all(), [
			'longitude' => 'required|numeric',
			'latitude' => 'required|numeric',
		]);

		if ($validator->fails()) {
			return response()->json([
				'success' => false,
				'message' => 'Les données fournies ne sont pas valides.',
				'errors' => $validator->errors(),
			], 422);
		}

		$latitude = $request->latitude;
		$longitude = $request->longitude;

		// Requête pour récupérer les commissariats triés par distance
		$station_service = Station_service::select(
			'*', // Remplace '*' par les champs spécifiques si nécessaire
			DB::raw("
                CASE
                    WHEN longitude IS NULL OR latitude IS NULL THEN 0
                    ELSE (
                        6371 * acos(
                            cos(radians($latitude)) *
                            cos(radians(latitude)) *
                            cos(radians(longitude) - radians($longitude)) +
                            sin(radians($latitude)) *
                            sin(radians(latitude))
                        )
                    )
                END AS distance
            ")
		)
		->where('statut', 1)
		->where('borne_electrique', 0)
        ->with('ville', 'commune')
		->orderBy('distance', 'asc') // Trier par distance croissante
		->get();

		// Vérifier si des commissariats existent
		if ($station_service->isEmpty()) {
			return response()->json([
				'success' => false,
				'message' => "Aucun station service trouvé.",
			], 404);
		}

		// Retourner la liste des commissariats triés par proximité
		return response()->json([
			'success' => true,
			'message' => "Liste des stations services triée par proximité.",
			'station_services' => $station_service,
		], 200);
	}

    /**
     * Display a listing of the resource.
     */
    public function getAllStationServiceElectrique(Request $request)
	{
		// Validation des données d'entrée
		$validator = Validator::make($request->all(), [
			'longitude' => 'required|numeric',
			'latitude' => 'required|numeric',
		]);

		if ($validator->fails()) {
			return response()->json([
				'success' => false,
				'message' => 'Les données fournies ne sont pas valides.',
				'errors' => $validator->errors(),
			], 422);
		}

		$latitude = $request->latitude;
		$longitude = $request->longitude;

		// Requête pour récupérer les commissariats triés par distance
		$station_service = Station_service::select(
			'*', // Remplace '*' par les champs spécifiques si nécessaire
			DB::raw("
                CASE
                    WHEN longitude IS NULL OR latitude IS NULL THEN 0
                    ELSE (
                        6371 * acos(
                            cos(radians($latitude)) *
                            cos(radians(latitude)) *
                            cos(radians(longitude) - radians($longitude)) +
                            sin(radians($latitude)) *
                            sin(radians(latitude))
                        )
                    )
                END AS distance
            ")
		)
		->where('statut', 1)
		->where('borne_electrique', 1)
        ->with('ville', 'commune')
		->orderBy('distance', 'asc') // Trier par distance croissante
		->get();

		// Vérifier si des commissariats existent
		if ($station_service->isEmpty()) {
			return response()->json([
				'success' => false,
				'message' => "Aucun station service trouvé.",
			], 404);
		}

		// Retourner la liste des commissariats triés par proximité
		return response()->json([
			'success' => true,
			'message' => "Liste des stations services triée par proximité.",
			'station_services' => $station_service,
		], 200);
	}

    /**
     * Display a listing of the resource.
     */
    public function getAllSapeurPompier(Request $request)
	{
		// Validation des données d'entrée
		$validator = Validator::make($request->all(), [
			'longitude' => 'required|numeric',
			'latitude' => 'required|numeric',
		]);

		if ($validator->fails()) {
			return response()->json([
				'success' => false,
				'message' => 'Les données fournies ne sont pas valides.',
				'errors' => $validator->errors(),
			], 422);
		}

		$latitude = $request->latitude;
		$longitude = $request->longitude;

		// Requête pour récupérer les commissariats triés par distance
		$sapeur_pompier = Sapeur_pompier::select(
			'*', // Remplace '*' par les champs spécifiques si nécessaire
			DB::raw("
				CASE
					WHEN longitude IS NULL OR latitude IS NULL THEN 0
					ELSE (
						6371 * acos(
							cos(radians($latitude)) *
							cos(radians(latitude)) *
							cos(radians(longitude) - radians($longitude)) +
							sin(radians($latitude)) *
							sin(radians(latitude))
						)
					)
				END AS distance
			")
		)
		->orderBy('distance', 'asc')
		->get();


		// Vérifier si des commissariats existent
		if ($sapeur_pompier->isEmpty()) {
			return response()->json([
				'success' => false,
				'message' => "Aucun sapeur pompier trouvé.",
			], 404);
		}

		// Retourner la liste des commissariats triés par proximité
		return response()->json([
			'success' => true,
			'message' => "Liste des sapeur pompier triée par proximité.",
			'sapeur_pompiers' => $sapeur_pompier,
		], 200);
	}

    /**
     * Display a listing of the resource.
     */
    public function getAgentConstatByCommune(Request $request)
    {
        // Validation des données d'entrée
        $validator = Validator::make($request->all(), [
            'commune_id' => 'required|exists:communes,id',
        ]);

        $user = auth()->user();
        // Vérifier si des établissements existent
        if (empty($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur introuvable',
            ], 404);
        }

        // Récupérer les établissements triés par ID décroissant
        $commissariats = Commissariat::where('commune_id', $request->commune_id)
        ->orderBy('id', 'desc')->get();

        // Vérifier si des établissements existent
        if ($commissariats->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune agent constat enregistré pour cette commune.',
            ], 404);
        }

        // Retourner la liste des établissements
        return response()->json([
            'success' => true,
            'message' => 'Liste des agents constats.',
            'commissariats' => $commissariats,
        ], 200);
    }


    /**
     * Display a listing of the resource.
     */
    public function getSapeurPompierByCommune(Request $request)
    {
        // Validation des données d'entrée
        $validator = Validator::make($request->all(), [
            'commune_id' => 'required|exists:communes,id',
        ]);

        $user = auth()->user();
        // Vérifier si des établissements existent
        if (empty($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur introuvable',
            ], 404);
        }

        // Récupérer les établissements triés par ID décroissant
        $sapeur_pompier = Sapeur_pompier::where('commune_id', $request->commune_id)
        ->orderBy('id', 'desc')->get();

        // Vérifier si des établissements existent
        if ($sapeur_pompier->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun service de sapeur pompier enregistré pour cette commune.',
            ], 404);
        }

        // Retourner la liste des établissements
        return response()->json([
            'success' => true,
            'message' => 'Liste des sapeurs-pompiers.',
            'sapeur_pompier' => $sapeur_pompier,
        ], 200);
    }


    /**
     * Display a listing of the resource.
     */
    public function getCommuneAll(Request $request)
    {
        $user = auth()->user();
        // Vérifier si des établissements existent
        if (empty($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur introuvable',
            ], 404);
        }

        // Récupérer les établissements triés par ID décroissant
        $commune = Commune::orderBy('id', 'desc')
        ->with('ville')
        ->get();

        // Vérifier si des établissements existent
        if ($commune->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun commune enregistré.',
            ], 404);
        }

        // Retourner la liste des établissements
        return response()->json([
            'success' => true,
            'message' => 'Liste des communes.',
            'commune' => $commune,
        ], 200);
    }


    /**
     * Display a listing of the resource.
     */
    public function getCabinetExpertise(Request $request)
    {
        $user = auth()->user();
        // Vérifier si des établissements existent
        if (empty($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur introuvable',
            ], 404);
        }

        // Récupérer les établissements triés par ID décroissant
        $cabinet_expertises = Cabinet_expertise::orderBy('id', 'desc')
        ->with('ville', 'commune')
        ->get();

        // Vérifier si des établissements existent
        if ($cabinet_expertises->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun Cabinet expertise enregistré.',
            ], 404);
        }

        // Retourner la liste des établissements
        return response()->json([
            'success' => true,
            'message' => 'Liste des communes.',
            'cabinet_expertises' => $cabinet_expertises,
        ], 200);
    }


    /**
     * Display a listing of the resource.
     */
    public function getEtablissementByTypeDePrestation(Request $request)
    {
        // Vérifier si l'utilisateur est authentifié
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur introuvable',
            ], 404);
        }

        // Validation des données d'entrée
        $validator = Validator::make($request->all(), [
            'type_de_prestation_id' => 'required|exists:type_de_prestations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Récupérer les établissements avec des données JSON valides
        $etablissements = Etablissement::whereNotNull('type_de_prestations')
            ->whereRaw('JSON_VALID(type_de_prestations)')
            ->whereJsonContains('type_de_prestations', $request->type_de_prestation_id)
            ->orderBy('id', 'desc')
            ->get();

        // Vérifier si des établissements existent
        if ($etablissements->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun établissement enregistré pour ce type de prestation.',
            ], 404);
        }

        // Récupérer tous les types de prestations en une seule requête
        $allTypesPrestations = Type_de_prestation::all()->keyBy('id');

        // Ajouter les libellés des types de prestations à chaque établissement
        $etablissements->transform(function ($etablissement) use ($allTypesPrestations) {
            $etablissement->types_prestations_libelles = $this->getTypesPrestationsLibelles($etablissement->type_de_prestations, $allTypesPrestations);
            $etablissement->types_prestations_complets = $this->getTypesPrestationsComplets($etablissement->type_de_prestations, $allTypesPrestations);
            return $etablissement;
        });

        // Récupérer le libelle du type de prestation recherché
        $typeDePrestation = Type_de_prestation::select('id', 'libelle')
            ->where('id', $request->type_de_prestation_id)
            ->first();

        // Retourner la liste des établissements avec le libelle
        return response()->json([
            'success' => true,
            'message' => 'Liste des établissements trouvés.',
            'type_de_prestation_libelle' => $typeDePrestation ? $typeDePrestation->libelle : null,
            'etablissement_by_type_de_prestation' => $etablissements,
        ], 200);
    }


}