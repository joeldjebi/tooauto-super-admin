<?php

namespace App\Http\Controllers;

use App\Services\WasabiService;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CallCenterSpaceController extends Controller
{
    public function __construct(private WasabiService $wasabiService)
    {
    }

    public function dashboard()
    {
        return view('call-centers.dashboard', [
            'title' => 'Dashboard Call Center',
            'menu' => 'call-center-dashboard',
            'user' => Auth::guard('call_center')->user(),
            'cards' => [
                ['label' => 'Users', 'route' => route('call-center.users')],
                ['label' => 'Professionnels', 'route' => route('call-center.professionnels')],
                ['label' => 'Vehicules', 'route' => route('call-center.vehicules')],
                ['label' => 'Station services', 'route' => route('call-center.station-services')],
                ['label' => 'Station de lavages', 'route' => route('call-center.station-de-lavages')],
                ['label' => 'Annonces', 'route' => route('call-center.annonces')],
                ['label' => 'Annonce concessionnaires', 'route' => route('call-center.annonce-concessionnaires')],
                ['label' => 'Annonce etablissements', 'route' => route('call-center.annonce-etablissements')],
                ['label' => 'Concessionnaires', 'route' => route('call-center.concessionnaires')],
                ['label' => 'Etablissements', 'route' => route('call-center.etablissements')],
                ['label' => 'Autodocs', 'route' => route('call-center.autodocs')],
            ],
        ]);
    }

    public function users(Request $request)
    {
        $query = DB::table('users');

        $selects = [];
        $searchColumns = [];

        $this->pushSelect($selects, 'users.nom', 'nom');
        $this->pushSelect($selects, 'users.prenoms', 'prenoms');
        $this->pushSelect($selects, 'users.name', 'nom_affiche');
        $this->pushSelect($selects, 'users.email', 'email');
        $this->pushSelect($selects, 'users.mobile', 'mobile');
        $this->pushSelect($selects, 'users.telephone', 'telephone');
        $this->pushSelect($selects, 'users.statut', 'statut');
        $this->pushSelect($selects, 'users.created_at', 'date_creation');
        $selects[] = DB::raw("
            CONCAT(
                '<div class=\"d-flex flex-wrap gap-1\">',
                '<a class=\"btn btn-sm btn-outline-warning mr-1 mb-1\" href=\"', '" . route('call-center.users') . "/', users.id, '/alertes', '\">Alertes</a>',
                '<a class=\"btn btn-sm btn-outline-success mr-1 mb-1\" href=\"', '" . route('call-center.users') . "/', users.id, '/vehicules', '\">Vehicules</a>',
                '<a class=\"btn btn-sm btn-outline-primary mr-1 mb-1\" href=\"', '" . route('call-center.users') . "/', users.id, '/annonces', '\">Annonces</a>',
                '<a class=\"btn btn-sm btn-outline-info mb-1\" href=\"', '" . route('call-center.users') . "/', users.id, '/autodocs', '\">Docs auto</a>',
                '</div>'
            ) as actions
        ");

        $this->pushSearchColumn($searchColumns, 'users.nom');
        $this->pushSearchColumn($searchColumns, 'users.prenoms');
        $this->pushSearchColumn($searchColumns, 'users.name');
        $this->pushSearchColumn($searchColumns, 'users.email');
        $this->pushSearchColumn($searchColumns, 'users.mobile');
        $this->pushSearchColumn($searchColumns, 'users.telephone');

        if ($this->hasColumn('users', 'commercial_id') && Schema::hasTable('commercials')) {
            $query->leftJoin('commercials', 'commercials.id', '=', 'users.commercial_id');
            $selects[] = DB::raw("TRIM(CONCAT(COALESCE(commercials.prenoms, ''), ' ', COALESCE(commercials.nom, ''))) as commercial");
        }

        $this->applySearch($query, $request->string('search')->toString(), $searchColumns);
        $this->applyStatusFilter($query, 'users', $request->input('statut'));

        return $this->renderList(
            $request,
            'Users',
            'call-center-users',
            $query->select($selects),
            [
                'nom' => 'Nom',
                'prenoms' => 'Prenoms',
                'nom_affiche' => 'Nom affiche',
                'email' => 'Email',
                'mobile' => 'Mobile',
                'telephone' => 'Telephone',
                'commercial' => 'Commercial',
                'statut' => 'Statut',
                'date_creation' => 'Date creation',
                'actions' => 'Actions',
            ],
            [
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Nom, prenoms, email, telephone',
                    'value' => $request->input('search', ''),
                ],
                $this->statusFilter($request->input('statut')),
            ]
        );
    }

    public function userAlerts(Request $request, int $user)
    {
        return $this->renderUserRelatedList(
            $request,
            $user,
            "Alertes de l'utilisateur #{$user}",
            function () use ($user) {
                return DB::table('alerts')
            ->leftJoin('vehicules', 'vehicules.id', '=', 'alerts.vehicule_id')
            ->leftJoin('type_alerts', 'type_alerts.id', '=', 'alerts.type_alert_id')
            ->where('alerts.user_id', $user);
            },
            function (Builder $query) use ($request) {
                $this->applySearch($query, $request->string('search')->toString(), array_filter([
                    $this->hasColumn('vehicules', 'matricule') ? 'vehicules.matricule' : null,
                    $this->hasColumn('type_alerts', 'libelle') ? 'type_alerts.libelle' : null,
                    $this->hasColumn('alerts', 'kilometrage') ? 'alerts.kilometrage' : null,
                    $this->hasColumn('alerts', 'autres') ? 'alerts.autres' : null,
                ]));
            },
            array_filter([
                $this->hasColumn('vehicules', 'matricule') ? 'vehicules.matricule as vehicule' : null,
                $this->hasColumn('type_alerts', 'libelle') ? 'type_alerts.libelle as type_alerte' : null,
                $this->hasColumn('alerts', 'date_debut') ? 'alerts.date_debut' : null,
                $this->hasColumn('alerts', 'date_fin') ? 'alerts.date_fin' : null,
                $this->hasColumn('alerts', 'kilometrage') ? 'alerts.kilometrage' : null,
                $this->hasColumn('alerts', 'autres') ? 'alerts.autres' : null,
                'alerts.created_at as date_creation',
            ]),
            array_filter([
                'vehicule' => 'Vehicule',
                'type_alerte' => 'Type alerte',
                'date_debut' => 'Date debut',
                'date_fin' => 'Date fin',
                'kilometrage' => 'Kilometrage',
                'autres' => 'Autres',
                'date_creation' => 'Date creation',
            ]),
            [
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Vehicule, type, kilometrage',
                    'value' => $request->input('search', ''),
                ],
            ]
        );
    }

    public function userVehicules(Request $request, int $user)
    {
        return $this->renderUserRelatedList(
            $request,
            $user,
            "Vehicules de l'utilisateur #{$user}",
            function () use ($user) {
                return DB::table('vehicules')
            ->leftJoin('marques', 'marques.id', '=', 'vehicules.marque_id')
            ->leftJoin('type_de_vehicules', 'type_de_vehicules.id', '=', 'vehicules.type_de_vehicule_id')
            ->where('vehicules.user_id', $user);
            },
            function (Builder $query) use ($request) {
                $this->applySearch($query, $request->string('search')->toString(), [
                    'vehicules.matricule',
                    'vehicules.carte_grise',
                    'vehicules.modele',
                    'vehicules.couleur',
                    'marques.libelle',
                    'type_de_vehicules.libelle',
                ]);
            },
            [
                'vehicules.id as row_id',
                'vehicules.matricule',
                'vehicules.carte_grise',
                'marques.libelle as marque',
                'type_de_vehicules.libelle as type_vehicule',
                'vehicules.modele',
                'vehicules.couleur',
                'vehicules.statut',
                'vehicules.created_at as date_creation',
                DB::raw("CONCAT('<a class=\"btn btn-sm btn-outline-primary\" href=\"', '" . route('call-center.vehicules') . "/', vehicules.id, '/details?back=user&user_id={$user}', '\">Details</a>') as actions"),
            ],
            [
                'matricule' => 'Matricule',
                'carte_grise' => 'Carte grise',
                'marque' => 'Marque',
                'type_vehicule' => 'Type vehicule',
                'modele' => 'Modele',
                'couleur' => 'Couleur',
                'statut' => 'Statut',
                'date_creation' => 'Date creation',
                'actions' => 'Actions',
            ],
            [
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Matricule, carte grise, modele',
                    'value' => $request->input('search', ''),
                ],
            ]
        );
    }

    public function userAnnonces(Request $request, int $user)
    {
        return $this->renderUserRelatedList(
            $request,
            $user,
            "Annonces de l'utilisateur #{$user}",
            function () use ($user) {
                return DB::table('annonces')
            ->leftJoin('marques', 'marques.id', '=', 'annonces.marque_id')
            ->where('annonces.usager_id', $user);
            },
            function (Builder $query) use ($request) {
                $searchColumns = array_filter([
                    $this->hasColumn('annonces', 'libelle') ? 'annonces.libelle' : null,
                    $this->hasColumn('annonces', 'description') ? 'annonces.description' : null,
                    $this->hasColumn('annonces', 'modele') ? 'annonces.modele' : null,
                    'marques.libelle',
                ]);
                $this->applySearch($query, $request->string('search')->toString(), $searchColumns);
            },
            array_filter([
                $this->hasColumn('annonces', 'libelle') ? 'annonces.libelle' : null,
                'marques.libelle as marque',
                $this->hasColumn('annonces', 'modele') ? 'annonces.modele' : null,
                $this->hasColumn('annonces', 'mobile') ? 'annonces.mobile' : null,
                $this->hasColumn('annonces', 'is_whatsapp') ? 'annonces.is_whatsapp' : null,
                'annonces.statut',
                'annonces.created_at as date_creation',
            ]),
            array_filter([
                'libelle' => 'Libelle',
                'marque' => 'Marque',
                'modele' => 'Modele',
                'mobile' => 'Mobile',
                'is_whatsapp' => 'WhatsApp',
                'statut' => 'Statut',
                'date_creation' => 'Date creation',
            ]),
            [
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Libelle, modele, marque',
                    'value' => $request->input('search', ''),
                ],
            ]
        );
    }

    public function userAutodocs(Request $request, int $user)
    {
        if (! Schema::hasTable('autodocs')) {
            return $this->renderList(
                $request,
                "Documents automobile de l'utilisateur #{$user}",
                'call-center-users',
                DB::table('users')->whereRaw('1 = 0')->selectRaw("'Aucun document automobile disponible' as information"),
                ['information' => 'Information'],
                []
            );
        }

        return $this->renderUserRelatedList(
            $request,
            $user,
            "Documents automobile de l'utilisateur #{$user}",
            function () use ($user) {
                $query = DB::table('autodocs');

                if ($this->hasColumn('autodocs', 'vehicule_id') && Schema::hasTable('vehicules')) {
                    $query->leftJoin('vehicules', 'vehicules.id', '=', 'autodocs.vehicule_id');
                }

                if ($this->hasColumn('autodocs', 'type_docauto_id') && Schema::hasTable('type_docautos')) {
                    $query->leftJoin('type_docautos', 'type_docautos.id', '=', 'autodocs.type_docauto_id');
                }

                if ($this->hasColumn('autodocs', 'user_id')) {
                    $query->where('autodocs.user_id', $user);
                } elseif ($this->hasColumn('autodocs', 'usager_id')) {
                    $query->where('autodocs.usager_id', $user);
                } else {
                    $query->whereRaw('1 = 0');
                }
                return $query;
            },
            function (Builder $query) use ($request) {
                $searchColumns = array_filter([
                    $this->hasColumn('vehicules', 'matricule') ? 'vehicules.matricule' : null,
                    $this->hasColumn('type_docautos', 'libelle') ? 'type_docautos.libelle' : null,
                    $this->hasColumn('autodocs', 'provenance') ? 'autodocs.provenance' : null,
                ]);
                $this->applySearch($query, $request->string('search')->toString(), $searchColumns);
            },
            array_filter([
                'autodocs.id as row_id',
                $this->hasColumn('vehicules', 'matricule') ? 'vehicules.matricule as vehicule' : null,
                $this->hasColumn('type_docautos', 'libelle') ? 'type_docautos.libelle as type_document' : null,
                $this->hasColumn('autodocs', 'images') ? DB::raw("
                    CASE
                        WHEN autodocs.images IS NULL OR autodocs.images = '' THEN '0 image'
                        ELSE CONCAT(
                            GREATEST(
                                (
                                    CHAR_LENGTH(autodocs.images)
                                    - CHAR_LENGTH(REPLACE(autodocs.images, '.jpg', ''))
                                ) / CHAR_LENGTH('.jpg')
                                +
                                (
                                    CHAR_LENGTH(autodocs.images)
                                    - CHAR_LENGTH(REPLACE(autodocs.images, '.jpeg', ''))
                                ) / CHAR_LENGTH('.jpeg')
                                +
                                (
                                    CHAR_LENGTH(autodocs.images)
                                    - CHAR_LENGTH(REPLACE(autodocs.images, '.png', ''))
                                ) / CHAR_LENGTH('.png')
                                +
                                (
                                    CHAR_LENGTH(autodocs.images)
                                    - CHAR_LENGTH(REPLACE(autodocs.images, '.pdf', ''))
                                ) / CHAR_LENGTH('.pdf'),
                                1
                            ),
                            ' fichier(s)'
                        )
                    END as images_resume
                ") : null,
                $this->hasColumn('autodocs', 'provenance_by') ? DB::raw("
                    CASE autodocs.provenance_by
                        WHEN 1 THEN 'Mobile'
                        WHEN 0 THEN 'Web'
                        ELSE autodocs.provenance_by
                    END as provenance_source
                ") : null,
                $this->hasColumn('autodocs', 'created_at') ? 'autodocs.created_at as date_creation' : null,
                DB::raw("CONCAT('<a class=\"btn btn-sm btn-outline-primary\" href=\"', '" . route('call-center.users') . "/', {$user}, '/autodocs/', autodocs.id, '/details', '\">Details</a>') as actions"),
            ]),
            array_filter([
                'vehicule' => 'Vehicule',
                'type_document' => 'Type document',
                'images_resume' => 'Fichiers',
                'provenance_source' => 'Source',
                'date_creation' => 'Date creation',
                'actions' => 'Actions',
            ]),
            [
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Vehicule, type document, provenance',
                    'value' => $request->input('search', ''),
                ],
            ]
        );
    }

    public function autodocDetails(Request $request, int $user, int $autodoc)
    {
        abort_unless(Schema::hasTable('autodocs'), 404);

        $query = DB::table('autodocs');

        if ($this->hasColumn('autodocs', 'vehicule_id') && Schema::hasTable('vehicules')) {
            $query->leftJoin('vehicules', 'vehicules.id', '=', 'autodocs.vehicule_id');
        }

        if ($this->hasColumn('autodocs', 'type_docauto_id') && Schema::hasTable('type_docautos')) {
            $query->leftJoin('type_docautos', 'type_docautos.id', '=', 'autodocs.type_docauto_id');
        }

        $query->where('autodocs.id', $autodoc);

        if ($this->hasColumn('autodocs', 'user_id')) {
            $query->where('autodocs.user_id', $user);
        } elseif ($this->hasColumn('autodocs', 'usager_id')) {
            $query->where('autodocs.usager_id', $user);
        }

        $autodocItem = $query->select(array_filter([
            'autodocs.id',
            $this->hasColumn('vehicules', 'matricule') ? 'vehicules.matricule as vehicule' : null,
            $this->hasColumn('type_docautos', 'libelle') ? 'type_docautos.libelle as type_document' : null,
            $this->hasColumn('autodocs', 'images') ? 'autodocs.images' : null,
            $this->hasColumn('autodocs', 'provenance_by') ? DB::raw("
                CASE autodocs.provenance_by
                    WHEN 1 THEN 'Mobile'
                    WHEN 0 THEN 'Web'
                    ELSE autodocs.provenance_by
                END as provenance_source
            ") : null,
            $this->hasColumn('autodocs', 'created_at') ? 'autodocs.created_at' : null,
        ]))->first();

        abort_unless($autodocItem, 404);

        $files = $this->extractPhotos($autodocItem->images ?? null);
        $signedFiles = array_values(array_filter(array_map(function ($file) {
            $signedUrl = $this->wasabiService->temporaryUrl($file);

            return [
                'path' => $file,
                'url' => $signedUrl ?? $file,
                'is_image' => $this->isImagePath($file),
            ];
        }, $files)));

        return view('call-centers.autodoc-details', [
            'title' => 'Details document automobile',
            'menu' => 'call-center-users',
            'user' => Auth::guard('call_center')->user(),
            'autodoc' => $autodocItem,
            'signedFiles' => $signedFiles,
            'backUrl' => route('call-center.users.autodocs', ['user' => $user]),
            'backLabel' => "Retour aux docs auto de l'utilisateur",
        ]);
    }

    public function professionnels(Request $request)
    {
        $query = DB::table('professionnels');

        $this->applySearch($query, $request->string('search')->toString(), [
            'professionnels.nom',
            'professionnels.prenoms',
            'professionnels.email',
            'professionnels.mobile',
            'professionnels.role',
        ]);
        $this->applyStatusFilter($query, 'professionnels', $request->input('statut'));

        if ($request->filled('role')) {
            $query->where('professionnels.role', $request->input('role'));
        }

        return $this->renderList(
            $request,
            'Professionnels',
            'call-center-professionnels',
            $query->select([
                'professionnels.id as row_id',
                'professionnels.nom',
                'professionnels.prenoms',
                'professionnels.role',
                'professionnels.email',
                'professionnels.mobile',
                'professionnels.statut',
                'professionnels.created_at as date_creation',
                DB::raw("CONCAT('<a class=\"btn btn-sm btn-outline-primary\" href=\"', '" . route('call-center.professionnels') . "/', professionnels.id, '/details', '\">Details</a>') as actions"),
            ]),
            [
                'nom' => 'Nom',
                'prenoms' => 'Prenoms',
                'role' => 'Role',
                'email' => 'Email',
                'mobile' => 'Mobile',
                'statut' => 'Statut',
                'date_creation' => 'Date creation',
                'actions' => 'Actions',
            ],
            [
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Nom, prenoms, email, mobile',
                    'value' => $request->input('search', ''),
                ],
                [
                    'name' => 'role',
                    'label' => 'Role',
                    'type' => 'select',
                    'value' => $request->input('role', ''),
                    'options' => $this->distinctOptions('professionnels', 'role', 'role'),
                ],
                $this->statusFilter($request->input('statut')),
            ]
        );
    }

    public function professionnelDetails(int $professionnel)
    {
        $professionnelItem = DB::table('professionnels')
            ->where('professionnels.id', $professionnel)
            ->select(array_filter([
                'professionnels.id',
                $this->hasColumn('professionnels', 'nom') ? 'professionnels.nom' : null,
                $this->hasColumn('professionnels', 'prenoms') ? 'professionnels.prenoms' : null,
                $this->hasColumn('professionnels', 'role') ? 'professionnels.role' : null,
                $this->hasColumn('professionnels', 'email') ? 'professionnels.email' : null,
                $this->hasColumn('professionnels', 'mobile') ? 'professionnels.mobile' : null,
                $this->hasColumn('professionnels', 'telephone') ? 'professionnels.telephone' : null,
                $this->hasColumn('professionnels', 'statut') ? 'professionnels.statut' : null,
                $this->hasColumn('professionnels', 'created_at') ? 'professionnels.created_at' : null,
            ]))
            ->first();

        abort_unless($professionnelItem, 404);

        $villeLabel = $this->resolveLabelColumn('villes');
        $communeLabel = $this->resolveLabelColumn('communes');

        $etablissements = collect();

        if (Schema::hasTable('etablissements') && $this->hasColumn('etablissements', 'professionnel_id')) {
            $etablissementsQuery = DB::table('etablissements')
                ->where('etablissements.professionnel_id', $professionnel);

            if (Schema::hasTable('categorie_services') && $this->hasColumn('etablissements', 'categorie_service_id')) {
                $etablissementsQuery->leftJoin('categorie_services', 'categorie_services.id', '=', 'etablissements.categorie_service_id');
            }

            if (Schema::hasTable('type_etablissements') && $this->hasColumn('etablissements', 'type_etablissement_id')) {
                $etablissementsQuery->leftJoin('type_etablissements', 'type_etablissements.id', '=', 'etablissements.type_etablissement_id');
            }

            if (Schema::hasTable('villes') && $this->hasColumn('etablissements', 'ville_id')) {
                $etablissementsQuery->leftJoin('villes', 'villes.id', '=', 'etablissements.ville_id');
            }

            if (Schema::hasTable('communes') && $this->hasColumn('etablissements', 'commune_id')) {
                $etablissementsQuery->leftJoin('communes', 'communes.id', '=', 'etablissements.commune_id');
            }

            $etablissements = $etablissementsQuery
                ->select(array_filter([
                    'etablissements.id',
                    $this->hasColumn('etablissements', 'name') ? 'etablissements.name' : null,
                    $this->hasColumn('etablissements', 'email') ? 'etablissements.email' : null,
                    $this->hasColumn('etablissements', 'mobile') ? 'etablissements.mobile' : null,
                    $this->hasColumn('etablissements', 'mobile_fix') ? 'etablissements.mobile_fix' : null,
                    $this->hasColumn('etablissements', 'adresse') ? 'etablissements.adresse' : null,
                    Schema::hasTable('categorie_services') && $this->hasColumn('etablissements', 'categorie_service_id') && $this->hasColumn('categorie_services', 'libelle') ? 'categorie_services.libelle as categorie' : null,
                    Schema::hasTable('type_etablissements') && $this->hasColumn('etablissements', 'type_etablissement_id') && $this->hasColumn('type_etablissements', 'libelle') ? 'type_etablissements.libelle as type_etablissement' : null,
                    Schema::hasTable('villes') && $this->hasColumn('etablissements', 'ville_id') && $villeLabel ? DB::raw('villes.' . $villeLabel . ' as ville') : null,
                    Schema::hasTable('communes') && $this->hasColumn('etablissements', 'commune_id') && $communeLabel ? DB::raw('communes.' . $communeLabel . ' as commune') : null,
                    $this->hasColumn('etablissements', 'statut') ? 'etablissements.statut' : null,
                    $this->hasColumn('etablissements', 'created_at') ? 'etablissements.created_at' : null,
                ]))
                ->orderByDesc('etablissements.id')
                ->get();
        }

        return view('call-centers.professionnel-details', [
            'title' => 'Details professionnel',
            'menu' => 'call-center-professionnels',
            'user' => Auth::guard('call_center')->user(),
            'professionnel' => $professionnelItem,
            'etablissements' => $etablissements,
            'backUrl' => route('call-center.professionnels'),
            'backLabel' => 'Retour aux professionnels',
        ]);
    }

    public function vehicules(Request $request)
    {
        $query = DB::table('vehicules')
            ->leftJoin('users', 'users.id', '=', 'vehicules.user_id')
            ->leftJoin('marques', 'marques.id', '=', 'vehicules.marque_id')
            ->leftJoin('type_de_vehicules', 'type_de_vehicules.id', '=', 'vehicules.type_de_vehicule_id')
            ->leftJoin('type_de_carburants', 'type_de_carburants.id', '=', 'vehicules.type_de_carburant_id');

        $this->applySearch($query, $request->string('search')->toString(), [
            'vehicules.matricule',
            'vehicules.modele',
            'users.nom',
            'users.prenoms',
            'marques.libelle',
            'type_de_vehicules.libelle',
        ]);
        $this->applyStatusFilter($query, 'vehicules', $request->input('statut'));

        if ($request->filled('marque_id')) {
            $query->where('vehicules.marque_id', $request->input('marque_id'));
        }

        if ($request->filled('type_de_vehicule_id')) {
            $query->where('vehicules.type_de_vehicule_id', $request->input('type_de_vehicule_id'));
        }

        return $this->renderList(
            $request,
            'Vehicules',
            'call-center-vehicules',
            $query->select([
                'vehicules.id as row_id',
                'vehicules.matricule',
                'vehicules.modele',
                DB::raw("TRIM(CONCAT(COALESCE(users.prenoms, ''), ' ', COALESCE(users.nom, ''))) as utilisateur"),
                'marques.libelle as marque',
                'type_de_vehicules.libelle as type_vehicule',
                'type_de_carburants.libelle as carburant',
                'vehicules.statut',
                'vehicules.created_at as date_creation',
                DB::raw("CONCAT('<a class=\"btn btn-sm btn-outline-primary\" href=\"', '" . route('call-center.vehicules') . "/', vehicules.id, '/details?back=list', '\">Details</a>') as actions"),
            ]),
            [
                'matricule' => 'Matricule',
                'modele' => 'Modele',
                'utilisateur' => 'Utilisateur',
                'marque' => 'Marque',
                'type_vehicule' => 'Type de vehicule',
                'carburant' => 'Carburant',
                'statut' => 'Statut',
                'date_creation' => 'Date creation',
                'actions' => 'Actions',
            ],
            [
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Matricule, modele, utilisateur',
                    'value' => $request->input('search', ''),
                ],
                [
                    'name' => 'marque_id',
                    'label' => 'Marque',
                    'type' => 'select',
                    'value' => $request->input('marque_id', ''),
                    'options' => $this->tableOptions('marques', 'id', 'libelle'),
                ],
                [
                    'name' => 'type_de_vehicule_id',
                    'label' => 'Type',
                    'type' => 'select',
                    'value' => $request->input('type_de_vehicule_id', ''),
                    'options' => $this->tableOptions('type_de_vehicules', 'id', 'libelle'),
                ],
                $this->statusFilter($request->input('statut')),
            ]
        );
    }

    public function stationServices(Request $request)
    {
        $villeLabel = $this->resolveLabelColumn('villes');
        $communeLabel = $this->resolveLabelColumn('communes');

        $query = DB::table('station_services')
            ->leftJoin('villes', 'villes.id', '=', 'station_services.ville_id')
            ->leftJoin('communes', 'communes.id', '=', 'station_services.commune_id');

        $this->applySearch($query, $request->string('search')->toString(), [
            'station_services.name',
            'station_services.email',
            'station_services.mobile',
            'station_services.adresse',
            $villeLabel ? 'villes.' . $villeLabel : 'station_services.name',
            $communeLabel ? 'communes.' . $communeLabel : 'station_services.name',
        ]);

        if ($request->filled('ville_id')) {
            $query->where('station_services.ville_id', $request->input('ville_id'));
        }

        if ($request->filled('commune_id')) {
            $query->where('station_services.commune_id', $request->input('commune_id'));
        }

        return $this->renderList(
            $request,
            'Station services',
            'call-center-station-services',
            $query->select([
                'station_services.name',
                'station_services.email',
                'station_services.mobile',
                'station_services.adresse',
                DB::raw(($villeLabel ? 'villes.' . $villeLabel : 'NULL') . ' as ville'),
                DB::raw(($communeLabel ? 'communes.' . $communeLabel : 'NULL') . ' as commune'),
                'station_services.borne_electrique',
                'station_services.created_at as date_creation',
            ]),
            [
                'name' => 'Nom',
                'email' => 'Email',
                'mobile' => 'Mobile',
                'adresse' => 'Adresse',
                'ville' => 'Ville',
                'commune' => 'Commune',
                'borne_electrique' => 'Borne electrique',
                'date_creation' => 'Date creation',
            ],
            [
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Nom, email, mobile, adresse',
                    'value' => $request->input('search', ''),
                ],
                [
                    'name' => 'ville_id',
                    'label' => 'Ville',
                    'type' => 'select',
                    'value' => $request->input('ville_id', ''),
                    'options' => $this->smartTableOptions('villes'),
                ],
                [
                    'name' => 'commune_id',
                    'label' => 'Commune',
                    'type' => 'select',
                    'value' => $request->input('commune_id', ''),
                    'options' => $this->smartTableOptions('communes'),
                ],
            ]
        );
    }

    public function vehiculeDetails(Request $request, int $vehicule)
    {
        $vehiculeItem = DB::table('vehicules')
            ->leftJoin('users', 'users.id', '=', 'vehicules.user_id')
            ->leftJoin('marques', 'marques.id', '=', 'vehicules.marque_id')
            ->leftJoin('type_de_vehicules', 'type_de_vehicules.id', '=', 'vehicules.type_de_vehicule_id')
            ->leftJoin('type_de_carburants', 'type_de_carburants.id', '=', 'vehicules.type_de_carburant_id')
            ->where('vehicules.id', $vehicule)
            ->select([
                'vehicules.id',
                'vehicules.matricule',
                'vehicules.carte_grise',
                'vehicules.photos',
                'vehicules.modele',
                'vehicules.couleur',
                'vehicules.statut',
                'vehicules.created_at',
                DB::raw("TRIM(CONCAT(COALESCE(users.prenoms, ''), ' ', COALESCE(users.nom, ''))) as utilisateur"),
                'marques.libelle as marque',
                'type_de_vehicules.libelle as type_vehicule',
                'type_de_carburants.libelle as carburant',
            ])
            ->first();

        abort_unless($vehiculeItem, 404);

        $photos = $this->extractPhotos($vehiculeItem->photos ?? null);
        $photoUrls = array_values(array_filter(array_map(
            fn ($photo) => $this->wasabiService->temporaryUrl($photo) ?? $photo,
            $photos
        )));

        $backUrl = route('call-center.vehicules');
        $backLabel = 'Retour aux vehicules';

        if ($request->input('back') === 'user' && $request->filled('user_id')) {
            $backUrl = route('call-center.users.vehicules', ['user' => $request->input('user_id')]);
            $backLabel = "Retour aux vehicules de l'utilisateur";
        }

        return view('call-centers.vehicule-details', [
            'title' => 'Details vehicule',
            'menu' => 'call-center-vehicules',
            'user' => Auth::guard('call_center')->user(),
            'vehicule' => $vehiculeItem,
            'photoUrls' => $photoUrls,
            'backUrl' => $backUrl,
            'backLabel' => $backLabel,
        ]);
    }

    public function stationDeLavages(Request $request)
    {
        $query = DB::table('station_de_lavages');
        $selects = [
            'station_de_lavages.name',
            'station_de_lavages.contact',
            'station_de_lavages.adresse',
            'station_de_lavages.longitude',
            'station_de_lavages.latitude',
            'station_de_lavages.statut',
            'station_de_lavages.created_at as date_creation',
        ];
        $columns = [
            'name' => 'Nom',
            'contact' => 'Contact',
            'adresse' => 'Adresse',
            'longitude' => 'Longitude',
            'latitude' => 'Latitude',
            'statut' => 'Statut',
            'date_creation' => 'Date creation',
        ];
        $searchColumns = [];
        $this->pushSearchColumn($searchColumns, 'station_de_lavages.name');
        $this->pushSearchColumn($searchColumns, 'station_de_lavages.contact');
        $this->pushSearchColumn($searchColumns, 'station_de_lavages.adresse');

        $this->applySearch($query, $request->string('search')->toString(), $searchColumns);
        $this->applyStatusFilter($query, 'station_de_lavages', $request->input('statut'));

        return $this->renderList(
            $request,
            'Station de lavages',
            'call-center-station-de-lavages',
            $query->select($selects),
            $columns,
            array_values(array_filter([
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Nom, contact, adresse',
                    'value' => $request->input('search', ''),
                ],
                $this->statusFilter($request->input('statut')),
            ]))
        );
    }

    public function annonces(Request $request)
    {
        $query = DB::table('annonces')
            ->leftJoin('users', 'users.id', '=', 'annonces.usager_id')
            ->leftJoin('marques', 'marques.id', '=', 'annonces.marque_id')
            ->leftJoin('type_de_pieces', 'type_de_pieces.id', '=', 'annonces.type_de_piece_id');

        $this->applySearch($query, $request->string('search')->toString(), [
            'users.nom',
            'users.prenoms',
            'marques.libelle',
            'type_de_pieces.libelle',
        ]);
        $this->applyStatusFilter($query, 'annonces', $request->input('statut'));

        if ($request->filled('marque_id')) {
            $query->where('annonces.marque_id', $request->input('marque_id'));
        }

        return $this->renderList(
            $request,
            'Annonces',
            'call-center-annonces',
            $query->select([
                DB::raw("TRIM(CONCAT(COALESCE(users.prenoms, ''), ' ', COALESCE(users.nom, ''))) as utilisateur"),
                'marques.libelle as marque',
                'type_de_pieces.libelle as type_piece',
                'annonces.statut',
                'annonces.created_at as date_creation',
            ]),
            [
                'utilisateur' => 'Utilisateur',
                'marque' => 'Marque',
                'type_piece' => 'Type de piece',
                'statut' => 'Statut',
                'date_creation' => 'Date creation',
            ],
            [
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Utilisateur, marque, type de piece',
                    'value' => $request->input('search', ''),
                ],
                [
                    'name' => 'marque_id',
                    'label' => 'Marque',
                    'type' => 'select',
                    'value' => $request->input('marque_id', ''),
                    'options' => $this->tableOptions('marques', 'id', 'libelle'),
                ],
                $this->statusFilter($request->input('statut')),
            ]
        );
    }

    public function annonceConcessionnaires(Request $request)
    {
        $query = DB::table('annonce_concessionnaires')
            ->leftJoin('users', 'users.id', '=', 'annonce_concessionnaires.user_id')
            ->leftJoin('concessionnaires', 'concessionnaires.id', '=', 'annonce_concessionnaires.concessionaire_id')
            ->leftJoin('marques', 'marques.id', '=', 'annonce_concessionnaires.marque_id')
            ->leftJoin('type_de_demandes', 'type_de_demandes.id', '=', 'annonce_concessionnaires.type_de_demande_id')
            ->leftJoin('type_de_vehicules', 'type_de_vehicules.id', '=', 'annonce_concessionnaires.type_de_vehicule_id');
        $selects = [
            DB::raw("TRIM(CONCAT(COALESCE(users.prenoms, ''), ' ', COALESCE(users.nom, ''))) as utilisateur"),
            'concessionnaires.name as concessionnaire',
            'marques.libelle as marque',
            'annonce_concessionnaires.modele',
            'type_de_demandes.libelle as type_demande',
            'type_de_vehicules.libelle as type_vehicule',
            'annonce_concessionnaires.statut',
        ];
        $columns = [
            'utilisateur' => 'Utilisateur',
            'concessionnaire' => 'Concessionnaire',
            'marque' => 'Marque',
            'modele' => 'Modele',
            'type_demande' => 'Type de demande',
            'type_vehicule' => 'Type de vehicule',
            'statut' => 'Statut',
        ];

        if ($this->hasColumn('annonce_concessionnaires', 'created_at')) {
            $selects[] = 'annonce_concessionnaires.created_at as date_creation';
            $columns['date_creation'] = 'Date creation';
        }

        $searchColumns = [
            'users.nom',
            'users.prenoms',
            'concessionnaires.name',
            'marques.libelle',
            'annonce_concessionnaires.modele',
        ];

        if ($this->hasColumn('annonce_concessionnaires', 'type_de_piece_id')) {
            $query->leftJoin('type_de_pieces', 'type_de_pieces.id', '=', 'annonce_concessionnaires.type_de_piece_id');
            $selects[] = 'type_de_pieces.libelle as type_piece';
            $columns['type_piece'] = 'Type de piece';
            $searchColumns[] = 'type_de_pieces.libelle';
        }

        $this->applySearch($query, $request->string('search')->toString(), $searchColumns);
        $this->applyStatusFilter($query, 'annonce_concessionnaires', $request->input('statut'));

        if ($request->filled('marque_id')) {
            $query->where('annonce_concessionnaires.marque_id', $request->input('marque_id'));
        }

        if ($request->filled('concessionaire_id')) {
            $query->where('annonce_concessionnaires.concessionaire_id', $request->input('concessionaire_id'));
        }

        return $this->renderList(
            $request,
            'Annonce concessionnaires',
            'call-center-annonce-concessionnaires',
            $query->select($selects),
            $columns,
            [
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Utilisateur, concessionnaire, modele',
                    'value' => $request->input('search', ''),
                ],
                [
                    'name' => 'marque_id',
                    'label' => 'Marque',
                    'type' => 'select',
                    'value' => $request->input('marque_id', ''),
                    'options' => $this->tableOptions('marques', 'id', 'libelle'),
                ],
                [
                    'name' => 'concessionaire_id',
                    'label' => 'Concessionnaire',
                    'type' => 'select',
                    'value' => $request->input('concessionaire_id', ''),
                    'options' => $this->tableOptions('concessionnaires', 'id', 'name'),
                ],
                $this->statusFilter($request->input('statut')),
            ]
        );
    }

    public function annonceEtablissements(Request $request)
    {
        $query = DB::table('annonce_etablissements')
            ->leftJoin('annonces', 'annonces.id', '=', 'annonce_etablissements.annonce_id')
            ->leftJoin('users', 'users.id', '=', 'annonces.usager_id')
            ->leftJoin('etablissements', 'etablissements.id', '=', 'annonce_etablissements.etablissement_id')
            ->leftJoin('marques', 'marques.id', '=', 'annonces.marque_id');

        $this->applySearch($query, $request->string('search')->toString(), [
            'users.nom',
            'users.prenoms',
            'etablissements.name',
            'marques.libelle',
        ]);

        if ($request->filled('etablissement_id')) {
            $query->where('annonce_etablissements.etablissement_id', $request->input('etablissement_id'));
        }

        if ($request->filled('is_visible')) {
            $query->where('annonce_etablissements.is_visible', $request->input('is_visible'));
        }

        return $this->renderList(
            $request,
            'Annonce etablissements',
            'call-center-annonce-etablissements',
            $query->select([
                DB::raw("TRIM(CONCAT(COALESCE(users.prenoms, ''), ' ', COALESCE(users.nom, ''))) as utilisateur"),
                'marques.libelle as marque',
                'etablissements.name as etablissement',
                'annonce_etablissements.is_visible',
                'annonce_etablissements.created_at as date_creation',
            ]),
            [
                'utilisateur' => 'Utilisateur',
                'marque' => 'Marque',
                'etablissement' => 'Etablissement',
                'is_visible' => 'Visible',
                'date_creation' => 'Date creation',
            ],
            [
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Utilisateur, etablissement, marque',
                    'value' => $request->input('search', ''),
                ],
                [
                    'name' => 'etablissement_id',
                    'label' => 'Etablissement',
                    'type' => 'select',
                    'value' => $request->input('etablissement_id', ''),
                    'options' => $this->tableOptions('etablissements', 'id', 'name'),
                ],
                [
                    'name' => 'is_visible',
                    'label' => 'Visible',
                    'type' => 'select',
                    'value' => $request->input('is_visible', ''),
                    'options' => [
                        ['value' => '1', 'label' => 'Oui'],
                        ['value' => '0', 'label' => 'Non'],
                    ],
                ],
            ]
        );
    }

    public function concessionnaires(Request $request)
    {
        $query = DB::table('concessionnaires');
        $selects = [
            'concessionnaires.name',
            'concessionnaires.email',
            'concessionnaires.contact',
            'concessionnaires.adresse',
            'concessionnaires.statut',
            'concessionnaires.created_at as date_creation',
        ];
        $columns = [
            'name' => 'Nom',
            'email' => 'Email',
            'contact' => 'Contact',
            'adresse' => 'Adresse',
        ];
        $searchColumns = [
            'concessionnaires.name',
            'concessionnaires.email',
            'concessionnaires.contact',
            'concessionnaires.adresse',
        ];

        if ($this->hasColumn('concessionnaires', 'userconcessionnaire_id') && Schema::hasTable('userconcessionnaires')) {
            $query->leftJoin('userconcessionnaires', 'userconcessionnaires.id', '=', 'concessionnaires.userconcessionnaire_id');
            $selects[] = DB::raw("TRIM(CONCAT(COALESCE(userconcessionnaires.prenoms, ''), ' ', COALESCE(userconcessionnaires.nom, ''))) as gestionnaire");
            $columns['gestionnaire'] = 'Gestionnaire';
            $searchColumns[] = 'userconcessionnaires.nom';
            $searchColumns[] = 'userconcessionnaires.prenoms';
        }

        $this->applySearch($query, $request->string('search')->toString(), $searchColumns);
        $this->applyStatusFilter($query, 'concessionnaires', $request->input('statut'));

        return $this->renderList(
            $request,
            'Concessionnaires',
            'call-center-concessionnaires',
            $query->select($selects),
            $columns + [
                'statut' => 'Statut',
                'date_creation' => 'Date creation',
            ],
            [
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Nom, email, contact, gestionnaire',
                    'value' => $request->input('search', ''),
                ],
                $this->statusFilter($request->input('statut')),
            ]
        );
    }

    public function etablissements(Request $request)
    {
        $villeLabel = $this->resolveLabelColumn('villes');
        $communeLabel = $this->resolveLabelColumn('communes');
        $hasCallFollowUp = $this->hasColumn('etablissements', 'call_center_deja_appele')
            && $this->hasColumn('etablissements', 'call_center_commentaire');

        $query = DB::table('etablissements')
            ->leftJoin('professionnels', 'professionnels.id', '=', 'etablissements.professionnel_id');
        $selects = [
            'etablissements.id as row_id',
            'etablissements.name',
            'etablissements.email',
            'etablissements.mobile',
            'etablissements.mobile_fix',
            'etablissements.adresse',
            DB::raw("TRIM(CONCAT(COALESCE(professionnels.prenoms, ''), ' ', COALESCE(professionnels.nom, ''))) as professionnel"),
            'etablissements.statut',
        ];
        $columns = [
            'name' => 'Nom',
            'email' => 'Email',
            'mobile' => 'Mobile',
            'mobile_fix' => 'Mobile fixe',
            'adresse' => 'Adresse',
            'professionnel' => 'Professionnel',
            'actions' => 'Actions',
        ];

        if ($this->hasColumn('etablissements', 'created_at')) {
            $selects[] = 'etablissements.created_at as date_creation';
            $columns['date_creation'] = 'Date creation';
        }

        if ($hasCallFollowUp) {
            $selects[] = 'etablissements.call_center_deja_appele';
            $selects[] = 'etablissements.call_center_commentaire';
        }

        if (Schema::hasTable('categorie_services') && $this->hasColumn('etablissements', 'categorie_service_id')) {
            $query->leftJoin('categorie_services', 'categorie_services.id', '=', 'etablissements.categorie_service_id');
            $selects[] = 'categorie_services.libelle as categorie';
            $columns['categorie'] = 'Categorie';
        }

        if (Schema::hasTable('type_etablissements') && $this->hasColumn('etablissements', 'type_etablissement_id')) {
            $query->leftJoin('type_etablissements', 'type_etablissements.id', '=', 'etablissements.type_etablissement_id');
            $selects[] = 'type_etablissements.libelle as type_etablissement';
            $columns['type_etablissement'] = 'Type etablissement';
        }

        if (Schema::hasTable('villes') && $this->hasColumn('etablissements', 'ville_id')) {
            $query->leftJoin('villes', 'villes.id', '=', 'etablissements.ville_id');
            $selects[] = DB::raw(($villeLabel ? 'villes.' . $villeLabel : 'NULL') . ' as ville');
            $columns['ville'] = 'Ville';
        }

        if (Schema::hasTable('communes') && $this->hasColumn('etablissements', 'commune_id')) {
            $query->leftJoin('communes', 'communes.id', '=', 'etablissements.commune_id');
            $selects[] = DB::raw(($communeLabel ? 'communes.' . $communeLabel : 'NULL') . ' as commune');
            $columns['commune'] = 'Commune';
        }

        $searchColumns = [
            'etablissements.name',
            'etablissements.email',
            'etablissements.mobile',
            'etablissements.mobile_fix',
            'etablissements.adresse',
            'professionnels.nom',
            'professionnels.prenoms',
        ];
        if (Schema::hasTable('villes') && $this->hasColumn('etablissements', 'ville_id')) {
            if ($villeLabel) {
                $searchColumns[] = 'villes.' . $villeLabel;
            }
        }
        if (Schema::hasTable('communes') && $this->hasColumn('etablissements', 'commune_id')) {
            if ($communeLabel) {
                $searchColumns[] = 'communes.' . $communeLabel;
            }
        }
        $this->applySearch($query, $request->string('search')->toString(), $searchColumns);
        $this->applyStatusFilter($query, 'etablissements', $request->input('statut'));

        if ($request->filled('professionnel_id')) {
            $query->where('etablissements.professionnel_id', $request->input('professionnel_id'));
        }

        if ($request->filled('ville_id') && $this->hasColumn('etablissements', 'ville_id')) {
            $query->where('etablissements.ville_id', $request->input('ville_id'));
        }

        if ($request->filled('commune_id') && $this->hasColumn('etablissements', 'commune_id')) {
            $query->where('etablissements.commune_id', $request->input('commune_id'));
        }

        return $this->renderList(
            $request,
            'Etablissements',
            'call-center-etablissements',
            $query->select($selects),
            $columns + [
                'statut' => 'Statut',
            ],
            [
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Nom, email, mobile, adresse, professionnel',
                    'value' => $request->input('search', ''),
                ],
                [
                    'name' => 'professionnel_id',
                    'label' => 'Professionnel',
                    'type' => 'select',
                    'value' => $request->input('professionnel_id', ''),
                    'options' => $this->nameOptions('professionnels'),
                ],
                [
                    'name' => 'ville_id',
                    'label' => 'Ville',
                    'type' => 'select',
                    'value' => $request->input('ville_id', ''),
                    'options' => $this->smartTableOptions('villes'),
                ],
                [
                    'name' => 'commune_id',
                    'label' => 'Commune',
                    'type' => 'select',
                    'value' => $request->input('commune_id', ''),
                    'options' => $this->smartTableOptions('communes'),
                ],
                $this->statusFilter($request->input('statut')),
            ]
        );
    }

    public function updateEtablissementCallFollowUp(Request $request, int $etablissement)
    {
        abort_unless(Schema::hasTable('etablissements'), 404);
        abort_unless($this->hasColumn('etablissements', 'call_center_deja_appele'), 404);
        abort_unless($this->hasColumn('etablissements', 'call_center_commentaire'), 404);

        $request->validate([
            'call_center_deja_appele' => ['sometimes', 'required', 'boolean'],
            'call_center_commentaire' => ['nullable', 'string', 'max:2000'],
        ]);

        $current = DB::table('etablissements')
            ->where('id', $etablissement)
            ->first(['id', 'call_center_deja_appele', 'call_center_commentaire']);

        abort_unless($current, 404);

        $dejaAppele = $request->has('call_center_deja_appele')
            ? $request->boolean('call_center_deja_appele')
            : (bool) $current->call_center_deja_appele;

        $data = ['call_center_deja_appele' => $dejaAppele];

        if ($request->has('call_center_commentaire')) {
            $data['call_center_commentaire'] = $request->input('call_center_commentaire');
        }

        if ($this->hasColumn('etablissements', 'call_center_called_at')) {
            $data['call_center_called_at'] = $dejaAppele ? now() : null;
        }

        DB::table('etablissements')
            ->where('id', $etablissement)
            ->update($data);

        return back()->with([
            'type' => 'alert-success',
            'message' => 'Suivi appel enregistre avec succes.',
        ]);
    }

    public function etablissementArticles(Request $request, int $etablissement)
    {
        abort_unless(Schema::hasTable('articles'), 404);

        $query = DB::table('articles')
            ->leftJoin('etablissements', 'etablissements.id', '=', 'articles.etablissement_id')
            ->where('articles.etablissement_id', $etablissement);

        $this->applySearch($query, $request->string('search')->toString(), array_filter([
            $this->hasColumn('articles', 'libelle') ? 'articles.libelle' : null,
            $this->hasColumn('articles', 'description') ? 'articles.description' : null,
            $this->hasColumn('articles', 'amount') ? 'articles.amount' : null,
            $this->hasColumn('etablissements', 'name') ? 'etablissements.name' : null,
        ]));

        return $this->renderChildList(
            $request,
            'Articles de l\'etablissement',
            'call-center-etablissements',
            $query->select(array_filter([
                'articles.id as row_id',
                $this->hasColumn('articles', 'libelle') ? 'articles.libelle' : null,
                $this->hasColumn('articles', 'description') ? 'articles.description' : null,
                $this->hasColumn('articles', 'amount') ? 'articles.amount' : null,
                $this->hasColumn('articles', 'image') ? 'articles.image' : null,
                $this->hasColumn('articles', 'created_at') ? 'articles.created_at as date_creation' : null,
            ])),
            array_filter([
                'libelle' => 'Libelle',
                'description' => 'Description',
                'amount' => 'Montant',
                'image' => 'Image',
                'date_creation' => 'Date creation',
            ]),
            [
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Libelle, description, montant',
                    'value' => $request->input('search', ''),
                ],
            ],
            route('call-center.etablissements'),
            'Retour aux etablissements'
        );
    }

    public function etablissementPromotions(Request $request, int $etablissement)
    {
        abort_unless(Schema::hasTable('promotions'), 404);

        $query = DB::table('promotions')
            ->leftJoin('etablissements', 'etablissements.id', '=', 'promotions.etablissement_id')
            ->where('promotions.etablissement_id', $etablissement);

        $this->applySearch($query, $request->string('search')->toString(), array_filter([
            $this->hasColumn('promotions', 'libelle') ? 'promotions.libelle' : null,
            $this->hasColumn('promotions', 'mobile') ? 'promotions.mobile' : null,
            $this->hasColumn('promotions', 'description') ? 'promotions.description' : null,
        ]));
        $this->applyStatusFilter($query, 'promotions', $request->input('statut'));

        return $this->renderChildList(
            $request,
            'Promotions de l\'etablissement',
            'call-center-etablissements',
            $query->select(array_filter([
                'promotions.id as row_id',
                $this->hasColumn('promotions', 'libelle') ? 'promotions.libelle' : null,
                $this->hasColumn('promotions', 'mobile') ? 'promotions.mobile' : null,
                $this->hasColumn('promotions', 'date_debut') ? 'promotions.date_debut' : null,
                $this->hasColumn('promotions', 'date_fin') ? 'promotions.date_fin' : null,
                $this->hasColumn('promotions', 'statut') ? 'promotions.statut' : null,
                $this->hasColumn('promotions', 'created_at') ? 'promotions.created_at as date_creation' : null,
            ])),
            array_filter([
                'libelle' => 'Libelle',
                'mobile' => 'Mobile',
                'date_debut' => 'Date debut',
                'date_fin' => 'Date fin',
                'statut' => 'Statut',
                'date_creation' => 'Date creation',
            ]),
            array_values(array_filter([
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Libelle, mobile, description',
                    'value' => $request->input('search', ''),
                ],
                $this->statusFilter($request->input('statut')),
            ])),
            route('call-center.etablissements'),
            'Retour aux etablissements'
        );
    }

    public function etablissementAbonnements(Request $request, int $etablissement)
    {
        $abonnementTable = $this->findExistingTable(['abonnement_pros', 'abonnement_pro', 'abonnements_pro']);
        abort_unless($abonnementTable !== null, 404);

        $forfaitForeignKey = $this->resolveExistingColumn($abonnementTable, ['forfait_id', 'forfait_pro_id']);
        $forfaitTable = $forfaitForeignKey === 'forfait_pro_id'
            ? $this->findExistingTable(['forfait_pros', 'forfaits'])
            : $this->findExistingTable(['forfaits', 'forfait_pros']);

        $query = DB::table($abonnementTable)
            ->where($abonnementTable . '.etablissement_id', $etablissement);

        if ($forfaitForeignKey !== null && $forfaitTable !== null && $this->hasColumn($forfaitTable, 'id')) {
            $query->leftJoin($forfaitTable, $forfaitTable . '.id', '=', $abonnementTable . '.' . $forfaitForeignKey);
        }

        $searchColumns = array_filter([
            $this->hasColumn($abonnementTable, 'reference') ? $abonnementTable . '.reference' : null,
            $forfaitTable !== null && $this->hasColumn($forfaitTable, 'nom') ? $forfaitTable . '.nom' : null,
        ]);
        $this->applySearch($query, $request->string('search')->toString(), $searchColumns);
        $this->applyStatusFilter($query, $abonnementTable, $request->input('statut'));

        return $this->renderChildList(
            $request,
            'Abonnements de l\'etablissement',
            'call-center-etablissements',
            $query->select(array_filter([
                $abonnementTable . '.id as row_id',
                $forfaitTable !== null && $this->hasColumn($forfaitTable, 'nom') ? $forfaitTable . '.nom as forfait' : null,
                $forfaitTable !== null && $this->hasColumn($forfaitTable, 'duree') ? $forfaitTable . '.duree' : null,
                $forfaitTable !== null && $this->hasColumn($forfaitTable, 'prix') ? $forfaitTable . '.prix' : null,
                $this->hasColumn($abonnementTable, 'date_debut') ? $abonnementTable . '.date_debut' : null,
                $this->hasColumn($abonnementTable, 'date_fin') ? $abonnementTable . '.date_fin' : null,
                $this->hasColumn($abonnementTable, 'statut') ? $abonnementTable . '.statut' : null,
                $this->hasColumn($abonnementTable, 'created_at') ? $abonnementTable . '.created_at as date_creation' : null,
            ])),
            array_filter([
                'forfait' => 'Forfait',
                'duree' => 'Duree',
                'prix' => 'Prix',
                'date_debut' => 'Date debut',
                'date_fin' => 'Date fin',
                'statut' => 'Statut',
                'date_creation' => 'Date creation',
            ]),
            array_values(array_filter([
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Forfait',
                    'value' => $request->input('search', ''),
                ],
                $this->hasColumn($abonnementTable, 'statut') ? $this->statusFilter($request->input('statut')) : null,
            ])),
            route('call-center.etablissements'),
            'Retour aux etablissements'
        );
    }

    public function autodocs(Request $request)
    {
        $query = DB::table('type_docautos');

        $this->applySearch($query, $request->string('search')->toString(), [
            'type_docautos.libelle',
        ]);

        return $this->renderList(
            $request,
            'Autodocs',
            'call-center-autodocs',
            $query->select([
                'type_docautos.libelle',
                'type_docautos.image',
                'type_docautos.created_at as date_creation',
                'type_docautos.updated_at as date_mise_a_jour',
            ]),
            [
                'libelle' => 'Libelle',
                'image' => 'Image',
                'date_creation' => 'Date creation',
                'date_mise_a_jour' => 'Date mise a jour',
            ],
            [
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Libelle',
                    'value' => $request->input('search', ''),
                ],
            ]
        );
    }

    private function renderList(
        Request $request,
        string $title,
        string $menu,
        Builder $query,
        array $columns,
        array $filters,
        ?callable $decorateRows = null
    ) {
        if (array_key_exists('date_creation', $columns)) {
            $this->applyDateCreationFilter($query, $request);
            $filters = array_merge($filters, $this->dateCreationFilters($request));
        }

        $items = $query
            ->orderByDesc($this->resolveOrderColumn(array_keys($columns)))
            ->paginate(15)
            ->withQueryString();

        if ($decorateRows !== null) {
            $items->setCollection($items->getCollection()->map($decorateRows));
        }

        return view('call-centers.table', [
            'title' => $title,
            'menu' => $menu,
            'user' => Auth::guard('call_center')->user(),
            'columns' => $columns,
            'items' => $items,
            'filters' => $filters,
            'hasActiveFilters' => collect($request->except('page'))->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty(),
        ]);
    }

    private function renderUserRelatedList(
        Request $request,
        int $userId,
        string $title,
        callable $queryFactory,
        callable $configureQuery,
        array $selects,
        array $columns,
        array $filters
    ) {
        $query = $queryFactory();
        $configureQuery($query);

        $response = $this->renderList($request, $title, 'call-center-users', $query->select($selects), $columns, $filters);
        $response->with('backUrl', route('call-center.users'));
        $response->with('backLabel', 'Retour aux users');
        $response->with('relatedUserId', $userId);

        return $response;
    }

    private function renderChildList(
        Request $request,
        string $title,
        string $menu,
        Builder $query,
        array $columns,
        array $filters,
        string $backUrl,
        string $backLabel
    ) {
        $response = $this->renderList($request, $title, $menu, $query, $columns, $filters);
        $response->with('backUrl', $backUrl);
        $response->with('backLabel', $backLabel);

        return $response;
    }

    private function applySearch(Builder $query, string $search, array $columns): void
    {
        if ($search === '' || empty($columns)) {
            return;
        }

        $query->where(function (Builder $subQuery) use ($search, $columns) {
            foreach ($columns as $index => $column) {
                if ($index === 0) {
                    $subQuery->where($column, 'like', '%' . $search . '%');
                } else {
                    $subQuery->orWhere($column, 'like', '%' . $search . '%');
                }
            }
        });
    }

    private function applyStatusFilter(Builder $query, string $table, mixed $status): void
    {
        if ($status === null || $status === '' || ! $this->hasColumn($table, 'statut')) {
            return;
        }

        $query->where("{$table}.statut", $status);
    }

    private function applyDateCreationFilter(Builder $query, Request $request): void
    {
        if ($request->filled('date_from')) {
            $query->havingRaw('DATE(date_creation) >= ?', [$request->input('date_from')]);
        }

        if ($request->filled('date_to')) {
            $query->havingRaw('DATE(date_creation) <= ?', [$request->input('date_to')]);
        }
    }

    private function dateCreationFilters(Request $request): array
    {
        return [
            [
                'name' => 'date_from',
                'label' => 'Date debut',
                'type' => 'date',
                'value' => $request->input('date_from', ''),
            ],
            [
                'name' => 'date_to',
                'label' => 'Date fin',
                'type' => 'date',
                'value' => $request->input('date_to', ''),
            ],
        ];
    }

    private function resolveOrderColumn(array $aliases): string
    {
        return in_array('date_creation', $aliases, true) ? 'date_creation' : $aliases[0];
    }

    private function statusFilter(mixed $value): array
    {
        return [
            'name' => 'statut',
            'label' => 'Statut',
            'type' => 'select',
            'value' => (string) ($value ?? ''),
            'options' => [
                ['value' => '1', 'label' => 'Actif'],
                ['value' => '0', 'label' => 'Inactif'],
            ],
        ];
    }

    private function tableOptions(string $table, string $valueColumn, string $labelColumn): array
    {
        if (! Schema::hasTable($table) || ! $this->hasColumn($table, $valueColumn) || ! $this->hasColumn($table, $labelColumn)) {
            return [];
        }

        return DB::table($table)
            ->select("{$valueColumn} as value", "{$labelColumn} as label")
            ->orderBy($labelColumn)
            ->get()
            ->map(fn ($item) => ['value' => (string) $item->value, 'label' => $item->label])
            ->all();
    }

    private function smartTableOptions(string $table, string $valueColumn = 'id'): array
    {
        $labelColumn = $this->resolveLabelColumn($table);

        if ($labelColumn === null) {
            return [];
        }

        return $this->tableOptions($table, $valueColumn, $labelColumn);
    }

    private function distinctOptions(string $table, string $column, string $labelColumn): array
    {
        if (! Schema::hasTable($table) || ! $this->hasColumn($table, $column) || ! $this->hasColumn($table, $labelColumn)) {
            return [];
        }

        return DB::table($table)
            ->select("{$column} as value", "{$labelColumn} as label")
            ->whereNotNull($column)
            ->distinct()
            ->orderBy($labelColumn)
            ->get()
            ->map(fn ($item) => ['value' => (string) $item->value, 'label' => $item->label])
            ->all();
    }

    private function nameOptions(string $table): array
    {
        if (! Schema::hasTable($table) || ! $this->hasColumn($table, 'id') || ! $this->hasColumn($table, 'nom')) {
            return [];
        }

        return DB::table($table)
            ->select(
                'id as value',
                DB::raw("TRIM(CONCAT(COALESCE(prenoms, ''), ' ', COALESCE(nom, ''))) as label")
            )
            ->orderBy('prenoms')
            ->orderBy('nom')
            ->get()
            ->map(fn ($item) => ['value' => (string) $item->value, 'label' => trim($item->label) !== '' ? $item->label : 'Sans nom'])
            ->all();
    }

    private function hasColumn(string $table, string $column): bool
    {
        return Schema::hasTable($table) && Schema::hasColumn($table, $column);
    }

    private function resolveLabelColumn(string $table): ?string
    {
        foreach (['libelle', 'name', 'nom'] as $column) {
            if ($this->hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    private function findExistingTable(array $tables): ?string
    {
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                return $table;
            }
        }

        return null;
    }

    private function resolveExistingColumn(string $table, array $columns): ?string
    {
        foreach ($columns as $column) {
            if ($this->hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    private function extractPhotos(mixed $photos): array
    {
        if (empty($photos)) {
            return [];
        }

        if (is_array($photos)) {
            return $photos;
        }

        if (! is_string($photos)) {
            return [];
        }

        $decoded = json_decode($photos, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        $decodedTwice = json_decode(trim($photos, '"'), true);
        if (is_array($decodedTwice)) {
            return $decodedTwice;
        }

        return [$photos];
    }

    private function isImagePath(string $path): bool
    {
        $path = strtolower($path);

        foreach (['.jpg', '.jpeg', '.png', '.gif', '.webp'] as $extension) {
            if (str_ends_with($path, $extension)) {
                return true;
            }
        }

        return false;
    }

    private function pushSelect(array &$selects, string $column, string $alias): void
    {
        [$table, $field] = explode('.', $column);

        if ($this->hasColumn($table, $field)) {
            $selects[] = "{$column} as {$alias}";
        }
    }

    private function pushSearchColumn(array &$columns, string $column): void
    {
        [$table, $field] = explode('.', $column);

        if ($this->hasColumn($table, $field)) {
            $columns[] = $column;
        }
    }
}
