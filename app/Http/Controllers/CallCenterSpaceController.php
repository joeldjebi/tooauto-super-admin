<?php

namespace App\Http\Controllers;

use App\Services\WasabiService;
use App\Support\CallCenterCallLog;
use App\Support\CallCenterOutcomes;
use App\Support\SchemaCache;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CallCenterSpaceController extends Controller
{
    public function __construct(private WasabiService $wasabiService)
    {
    }

    private const ALERT_SOON_DAYS = 30;
    private const OPTIONS_CACHE_SECONDS = 600;
    private const PER_PAGE_OPTIONS = [15, 25, 50, 100];
    private const REMOTE_OPTIONS_THRESHOLD = 150;
    private const REMOTE_OPTIONS_PREFIX = 'call_center.remote_options.';
    private const REMOTE_OPTIONS_SECONDS = 86400;
    private const REMOTE_OPTIONS_LIMIT = 30;
    private const COUNT_CACHE_SECONDS = 60;
    private const COUNT_VERSION_KEY = 'call_center.count_version';

    private const ALERT_URGENT_DAYS = 7;

    public function dashboard()
    {
        return view('call-centers.dashboard', [
            'title' => 'Tableau de bord',
            'menu' => 'call-center-dashboard',
            'user' => Auth::guard('call_center')->user(),
            'alertStats' => $this->cachedDashboard('stats', fn () => $this->alertStats()),
            'upcomingAlerts' => $this->cachedDashboard('upcoming', fn () => $this->dashboardAlerts('upcoming')),
            'expiredAlerts' => $this->cachedDashboard('expired', fn () => $this->dashboardAlerts('expired')),
            'usersToCall' => $this->cachedDashboard('to_call', fn () => $this->usersWithExpiredAlerts()),
            'alertFilter' => $this->alertExpirationFilter(null),
            'cards' => [
                ['label' => 'Alertes', 'route' => route('call-center.alertes')],
                ['label' => 'Usagers', 'route' => route('call-center.users')],
                ['label' => 'Professionnels', 'route' => route('call-center.professionnels')],
                ['label' => 'Véhicules', 'route' => route('call-center.vehicules')],
                ['label' => 'Stations service', 'route' => route('call-center.station-services')],
                ['label' => 'Stations de lavage', 'route' => route('call-center.station-de-lavages')],
                ['label' => 'Annonces', 'route' => route('call-center.annonces')],
                ['label' => 'Annonces concessionnaires', 'route' => route('call-center.annonce-concessionnaires')],
                ['label' => 'Annonces établissements', 'route' => route('call-center.annonce-etablissements')],
                ['label' => 'Concessionnaires', 'route' => route('call-center.concessionnaires')],
                ['label' => 'Établissements', 'route' => route('call-center.etablissements')],
                ['label' => 'Autodocs', 'route' => route('call-center.autodocs')],
                ['label' => 'Envoi de notifications', 'route' => route('call-center.notification-send.index')],
            ],
        ]);
    }

    public function users(Request $request)
    {
        $hasCallFollowUp = $this->hasCallFollowUpColumns('users');
        $query = DB::table('users');

        $selects = ['users.id as row_id'];
        $searchColumns = [];

        $this->pushSelect($selects, 'users.nom', 'nom');
        $this->pushSelect($selects, 'users.prenoms', 'prenoms');
        $this->pushSelect($selects, 'users.name', 'nom_affiche');
        $this->pushSelect($selects, 'users.email', 'email');
        $this->pushSelect($selects, 'users.mobile', 'mobile');
        $this->pushSelect($selects, 'users.indicatif', 'indicatif');
        $this->pushSelect($selects, 'users.is_whatsapp', 'is_whatsapp');
        $this->pushSelect($selects, 'users.telephone', 'telephone');
        $this->pushSelect($selects, 'users.statut', 'statut');
        $this->pushSelect($selects, 'users.created_at', 'date_creation');
        $selects[] = DB::raw("
            CONCAT(
                '<div class=\"d-flex flex-wrap gap-1\">',
                '<a class=\"btn btn-sm btn-outline-warning mr-1 mb-1\" href=\"', '" . route('call-center.users') . "/', users.id, '/alertes', '\">Alertes</a>',
                '<a class=\"btn btn-sm btn-outline-success mr-1 mb-1\" href=\"', '" . route('call-center.users') . "/', users.id, '/vehicules', '\">Véhicules</a>',
                '<a class=\"btn btn-sm btn-outline-primary mr-1 mb-1\" href=\"', '" . route('call-center.users') . "/', users.id, '/annonces', '\">Annonces</a>',
                '<a class=\"btn btn-sm btn-outline-info mb-1\" href=\"', '" . route('call-center.users') . "/', users.id, '/autodocs', '\">Documents auto</a>',
                '</div>'
            ) as actions
        ");

        $this->pushSearchColumn($searchColumns, 'users.nom');
        $this->pushSearchColumn($searchColumns, 'users.prenoms');
        $this->pushSearchColumn($searchColumns, 'users.name');
        $this->pushSearchColumn($searchColumns, 'users.email');
        $this->pushSearchColumn($searchColumns, 'users.mobile');
        $this->pushSearchColumn($searchColumns, 'users.telephone');

        if ($this->hasColumn('users', 'commercial_id') && $this->tableExists('commercials')) {
            $query->leftJoin('commercials', 'commercials.id', '=', 'users.commercial_id');
            $selects[] = DB::raw("TRIM(CONCAT(COALESCE(commercials.prenoms, ''), ' ', COALESCE(commercials.nom, ''))) as commercial");
        }

        if ($hasCallFollowUp) {
            $selects[] = 'users.call_center_deja_appele';
            $selects[] = 'users.call_center_commentaire';
        }

        $this->applySearch($query, $request->string('search')->toString(), $searchColumns);
        $this->applyStatusFilter($query, 'users', $request->input('statut'));

        if ($hasCallFollowUp && $request->filled('call_center_deja_appele')) {
            $query->where('users.call_center_deja_appele', $request->boolean('call_center_deja_appele'));
        }

        $columns = [
            'nom' => 'Nom',
            'prenoms' => 'Prénoms',
            'nom_affiche' => 'Nom affiché',
            'email' => 'Email',
            'mobile' => 'Mobile',
            'telephone' => 'Téléphone',
            'commercial' => 'Commercial',
            'statut' => 'Statut',
        ];

        if ($hasCallFollowUp) {
            $columns['suivi_appel'] = 'Suivi appel';
        }

        $columns += [
            'date_creation' => 'Date de création',
            'actions' => 'Actions',
        ];

        return $this->renderList(
            $request,
            'Usagers',
            'call-center-users',
            $query->select($selects),
            $columns,
            array_filter([
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Nom, prénoms, email, téléphone',
                    'value' => $request->input('search', ''),
                ],
                $this->statusFilter($request->input('statut')),
                $hasCallFollowUp ? [
                    'name' => 'call_center_deja_appele',
                    'label' => 'Appel',
                    'type' => 'select',
                    'value' => $request->input('call_center_deja_appele', ''),
                    'options' => [
                        ['value' => '1', 'label' => 'Déjà appelé'],
                        ['value' => '0', 'label' => 'Non appelé'],
                    ],
                ] : null,
            ])
        );
    }

    public function updateUserCallFollowUp(Request $request, int $user)
    {
        return $this->updateCallFollowUp($request, 'users', $user, 'Usager');
    }

    public function userAlerts(Request $request, int $user)
    {
        $response = $this->renderUserRelatedList(
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
                $this->applyAlertExpirationFilter($query, $request->input('echeance'));
            },
            array_filter([
                $this->hasColumn('vehicules', 'matricule') ? 'vehicules.matricule as vehicule' : null,
                $this->hasColumn('type_alerts', 'libelle') ? 'type_alerts.libelle as type_alerte' : null,
                $this->hasColumn('alerts', 'date_debut') ? 'alerts.date_debut' : null,
                $this->hasColumn('alerts', 'date_fin') ? 'alerts.date_fin' : null,
                $this->hasColumn('alerts', 'date_fin') ? DB::raw('DATEDIFF(alerts.date_fin, CURDATE()) as echeance') : null,
                $this->hasColumn('alerts', 'kilometrage') ? 'alerts.kilometrage' : null,
                $this->hasColumn('alerts', 'autres') ? 'alerts.autres' : null,
                'alerts.created_at as date_creation',
            ]),
            array_filter([
                'vehicule' => 'Véhicule',
                'type_alerte' => 'Type d’alerte',
                'date_fin' => "Date d'expiration",
                'echeance' => 'Échéance',
                'date_debut' => 'Date de début',
                'kilometrage' => 'Kilométrage',
                'autres' => 'Autres',
                'date_creation' => 'Date de création',
            ]),
            [
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Véhicule, type, kilométrage',
                    'value' => $request->input('search', ''),
                ],
                $this->alertExpirationFilter($request->input('echeance')),
            ],
            fn (Builder $query) => $this->applyAlertOrder($query, $request->input('echeance'))
        );

        return $response->with('contactUser', $this->contactUser($user));
    }

    public function alerts(Request $request)
    {
        $hasCallFollowUp = $this->hasCallFollowUpColumns('users');
        $query = $this->alertsBaseQuery();

        $selects = [
            'users.id as row_id',
            'alerts.id as alert_id',
            DB::raw("TRIM(CONCAT(COALESCE(users.nom, ''), ' ', COALESCE(users.prenoms, ''))) as utilisateur"),
        ];
        $this->pushSelect($selects, 'users.mobile', 'mobile');
        $this->pushSelect($selects, 'users.indicatif', 'indicatif');
        $this->pushSelect($selects, 'users.is_whatsapp', 'is_whatsapp');
        $this->pushSelect($selects, 'vehicules.matricule', 'vehicule');
        $this->pushSelect($selects, 'type_alerts.libelle', 'type_alerte');
        $selects[] = 'alerts.date_fin';
        $selects[] = DB::raw('DATEDIFF(alerts.date_fin, CURDATE()) as echeance');
        $this->pushSelect($selects, 'alerts.date_debut', 'date_debut');
        $selects[] = DB::raw("
            CONCAT(
                '<a class=\"btn btn-sm btn-outline-warning mr-1 mb-1\" href=\"', '" . route('call-center.users') . "/', users.id, '/alertes', '\">Alertes de l’usager</a>',
                '<a class=\"btn btn-sm btn-outline-success mb-1\" href=\"', '" . route('call-center.users') . "/', users.id, '/vehicules', '\">Véhicules</a>'
            ) as actions
        ");

        if ($hasCallFollowUp) {
            $selects[] = 'users.call_center_deja_appele';
            $selects[] = 'users.call_center_commentaire';
        }

        $searchColumns = [];
        $this->pushSearchColumn($searchColumns, 'users.nom');
        $this->pushSearchColumn($searchColumns, 'users.prenoms');
        $this->pushSearchColumn($searchColumns, 'users.email');
        $this->pushSearchColumn($searchColumns, 'users.mobile');
        $this->pushSearchColumn($searchColumns, 'vehicules.matricule');
        $this->pushSearchColumn($searchColumns, 'type_alerts.libelle');

        $this->applySearch($query, $request->string('search')->toString(), $searchColumns);
        $this->applyAlertExpirationFilter($query, $request->input('echeance'));

        if ($request->filled('type_alert_id')) {
            $query->where('alerts.type_alert_id', $request->input('type_alert_id'));
        }

        if ($request->filled('fin_from')) {
            $query->whereDate('alerts.date_fin', '>=', $request->input('fin_from'));
        }

        if ($request->filled('fin_to')) {
            $query->whereDate('alerts.date_fin', '<=', $request->input('fin_to'));
        }

        if ($hasCallFollowUp && $request->filled('call_center_deja_appele')) {
            $query->where('users.call_center_deja_appele', $request->boolean('call_center_deja_appele'));
        }

        $columns = [
            'utilisateur' => 'Utilisateur',
            'mobile' => 'Téléphone',
            'vehicule' => 'Véhicule',
            'type_alerte' => 'Type d’alerte',
            'date_fin' => "Date d'expiration",
            'echeance' => 'Échéance',
            'date_debut' => 'Date de début',
        ];

        if ($hasCallFollowUp) {
            $columns['suivi_appel'] = 'Suivi appel';
        }

        $columns['actions'] = 'Actions';

        return $this->renderList(
            $request,
            'Alertes',
            'call-center-alertes',
            $query->select($selects),
            $columns,
            array_values(array_filter([
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Nom, mobile, véhicule, type',
                    'value' => $request->input('search', ''),
                ],
                $this->alertExpirationFilter($request->input('echeance')),
                [
                    'name' => 'type_alert_id',
                    'label' => 'Type d’alerte',
                    'type' => 'select',
                    'value' => (string) $request->input('type_alert_id', ''),
                    'options' => $this->tableOptions('type_alerts', 'id', 'libelle'),
                ],
                $hasCallFollowUp ? [
                    'name' => 'call_center_deja_appele',
                    'label' => 'Appel',
                    'type' => 'select',
                    'value' => $request->input('call_center_deja_appele', ''),
                    'options' => [
                        ['value' => '1', 'label' => 'Déjà appelé'],
                        ['value' => '0', 'label' => 'Non appelé'],
                    ],
                ] : null,
                [
                    'name' => 'fin_from',
                    'label' => 'Expire à partir du',
                    'type' => 'date',
                    'value' => $request->input('fin_from', ''),
                ],
                [
                    'name' => 'fin_to',
                    'label' => "Expire jusqu'au",
                    'type' => 'date',
                    'value' => $request->input('fin_to', ''),
                ],
            ])),
            null,
            fn (Builder $query) => $this->applyAlertOrder($query, $request->input('echeance'))
        );
    }

    public function userVehicules(Request $request, int $user)
    {
        return $this->renderUserRelatedList(
            $request,
            $user,
            "Véhicules de l'utilisateur #{$user}",
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
                DB::raw("CONCAT('<a class=\"btn btn-sm btn-outline-primary\" href=\"', '" . route('call-center.vehicules') . "/', vehicules.id, '/details?back=user&user_id={$user}', '\">Détails</a>') as actions"),
            ],
            [
                'matricule' => 'Matricule',
                'carte_grise' => 'Carte grise',
                'marque' => 'Marque',
                'type_vehicule' => 'Type de véhicule',
                'modele' => 'Modèle',
                'couleur' => 'Couleur',
                'statut' => 'Statut',
                'date_creation' => 'Date de création',
                'actions' => 'Actions',
            ],
            [
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Matricule, carte grise, modèle',
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
                'libelle' => 'Libellé',
                'marque' => 'Marque',
                'modele' => 'Modèle',
                'mobile' => 'Mobile',
                'is_whatsapp' => 'WhatsApp',
                'statut' => 'Statut',
                'date_creation' => 'Date de création',
            ]),
            [
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Libellé, modèle, marque',
                    'value' => $request->input('search', ''),
                ],
            ]
        );
    }

    public function userAutodocs(Request $request, int $user)
    {
        if (! $this->tableExists('autodocs')) {
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

                if ($this->hasColumn('autodocs', 'vehicule_id') && $this->tableExists('vehicules')) {
                    $query->leftJoin('vehicules', 'vehicules.id', '=', 'autodocs.vehicule_id');
                }

                if ($this->hasColumn('autodocs', 'type_docauto_id') && $this->tableExists('type_docautos')) {
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
                DB::raw("CONCAT('<a class=\"btn btn-sm btn-outline-primary\" href=\"', '" . route('call-center.users') . "/', {$user}, '/autodocs/', autodocs.id, '/details', '\">Détails</a>') as actions"),
            ]),
            array_filter([
                'vehicule' => 'Véhicule',
                'type_document' => 'Type document',
                'images_resume' => 'Fichiers',
                'provenance_source' => 'Source',
                'date_creation' => 'Date de création',
                'actions' => 'Actions',
            ]),
            [
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Véhicule, type document, provenance',
                    'value' => $request->input('search', ''),
                ],
            ]
        );
    }

    public function autodocDetails(Request $request, int $user, int $autodoc)
    {
        abort_unless($this->tableExists('autodocs'), 404);

        $query = DB::table('autodocs');

        if ($this->hasColumn('autodocs', 'vehicule_id') && $this->tableExists('vehicules')) {
            $query->leftJoin('vehicules', 'vehicules.id', '=', 'autodocs.vehicule_id');
        }

        if ($this->hasColumn('autodocs', 'type_docauto_id') && $this->tableExists('type_docautos')) {
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
            'title' => 'Détails du document automobile',
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
        $hasCallFollowUp = $this->hasCallFollowUpColumns('professionnels');
        $query = DB::table('professionnels');
        $selects = [
            'professionnels.id as row_id',
            'professionnels.nom',
            'professionnels.prenoms',
            'professionnels.role',
            'professionnels.email',
            'professionnels.mobile',
            'professionnels.statut',
            'professionnels.created_at as date_creation',
            DB::raw("CONCAT('<a class=\"btn btn-sm btn-outline-primary\" href=\"', '" . route('call-center.professionnels') . "/', professionnels.id, '/details', '\">Détails</a>') as actions"),
        ];
        $columns = [
            'nom' => 'Nom',
            'prenoms' => 'Prénoms',
            'role' => 'Rôle',
            'email' => 'Email',
            'mobile' => 'Mobile',
            'statut' => 'Statut',
        ];

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

        if ($hasCallFollowUp) {
            $selects[] = 'professionnels.call_center_deja_appele';
            $selects[] = 'professionnels.call_center_commentaire';
            $columns['suivi_appel'] = 'Suivi appel';
            $this->applyCallFollowUpFilter($query, 'professionnels', $request);
        }

        $columns += [
            'date_creation' => 'Date de création',
            'actions' => 'Actions',
        ];

        return $this->renderList(
            $request,
            'Professionnels',
            'call-center-professionnels',
            $query->select($selects),
            $columns,
            array_values(array_filter([
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Nom, prénoms, email, mobile',
                    'value' => $request->input('search', ''),
                ],
                [
                    'name' => 'role',
                    'label' => 'Rôle',
                    'type' => 'select',
                    'value' => $request->input('role', ''),
                    'options' => $this->distinctOptions('professionnels', 'role', 'role'),
                ],
                $this->statusFilter($request->input('statut')),
                $hasCallFollowUp ? $this->callFollowUpFilter($request->input('call_center_deja_appele')) : null,
            ]))
        );
    }

    public function updateProfessionnelCallFollowUp(Request $request, int $professionnel)
    {
        return $this->updateCallFollowUp($request, 'professionnels', $professionnel, 'Professionnel');
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

        if ($this->tableExists('etablissements') && $this->hasColumn('etablissements', 'professionnel_id')) {
            $etablissementsQuery = DB::table('etablissements')
                ->where('etablissements.professionnel_id', $professionnel);

            if ($this->tableExists('categorie_services') && $this->hasColumn('etablissements', 'categorie_service_id')) {
                $etablissementsQuery->leftJoin('categorie_services', 'categorie_services.id', '=', 'etablissements.categorie_service_id');
            }

            if ($this->tableExists('type_etablissements') && $this->hasColumn('etablissements', 'type_etablissement_id')) {
                $etablissementsQuery->leftJoin('type_etablissements', 'type_etablissements.id', '=', 'etablissements.type_etablissement_id');
            }

            if ($this->tableExists('villes') && $this->hasColumn('etablissements', 'ville_id')) {
                $etablissementsQuery->leftJoin('villes', 'villes.id', '=', 'etablissements.ville_id');
            }

            if ($this->tableExists('communes') && $this->hasColumn('etablissements', 'commune_id')) {
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
                    $this->tableExists('categorie_services') && $this->hasColumn('etablissements', 'categorie_service_id') && $this->hasColumn('categorie_services', 'libelle') ? 'categorie_services.libelle as categorie' : null,
                    $this->tableExists('type_etablissements') && $this->hasColumn('etablissements', 'type_etablissement_id') && $this->hasColumn('type_etablissements', 'libelle') ? 'type_etablissements.libelle as type_etablissement' : null,
                    $this->tableExists('villes') && $this->hasColumn('etablissements', 'ville_id') && $villeLabel ? DB::raw('villes.' . $villeLabel . ' as ville') : null,
                    $this->tableExists('communes') && $this->hasColumn('etablissements', 'commune_id') && $communeLabel ? DB::raw('communes.' . $communeLabel . ' as commune') : null,
                    $this->hasColumn('etablissements', 'statut') ? 'etablissements.statut' : null,
                    $this->hasColumn('etablissements', 'created_at') ? 'etablissements.created_at' : null,
                ]))
                ->orderByDesc('etablissements.id')
                ->get();
        }

        return view('call-centers.professionnel-details', [
            'title' => 'Détails du professionnel',
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
            'Véhicules',
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
                DB::raw("CONCAT('<a class=\"btn btn-sm btn-outline-primary\" href=\"', '" . route('call-center.vehicules') . "/', vehicules.id, '/details?back=list', '\">Détails</a>') as actions"),
            ]),
            [
                'matricule' => 'Matricule',
                'modele' => 'Modèle',
                'utilisateur' => 'Utilisateur',
                'marque' => 'Marque',
                'type_vehicule' => 'Type de véhicule',
                'carburant' => 'Carburant',
                'statut' => 'Statut',
                'date_creation' => 'Date de création',
                'actions' => 'Actions',
            ],
            [
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Matricule, modèle, utilisateur',
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
        $hasCallFollowUp = $this->hasCallFollowUpColumns('station_services');

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

        $selects = [
            'station_services.id as row_id',
            'station_services.name',
            'station_services.email',
            'station_services.mobile',
            'station_services.adresse',
            DB::raw(($villeLabel ? 'villes.' . $villeLabel : 'NULL') . ' as ville'),
            DB::raw(($communeLabel ? 'communes.' . $communeLabel : 'NULL') . ' as commune'),
            'station_services.borne_electrique',
            'station_services.created_at as date_creation',
            DB::raw("'' as actions"),
        ];
        $columns = [
            'name' => 'Nom',
            'email' => 'Email',
            'mobile' => 'Mobile',
            'adresse' => 'Adresse',
            'ville' => 'Ville',
            'commune' => 'Commune',
            'borne_electrique' => 'Borne électrique',
            'date_creation' => 'Date de création',
        ];

        if ($hasCallFollowUp) {
            $selects[] = 'station_services.call_center_deja_appele';
            $selects[] = 'station_services.call_center_commentaire';
            $columns['suivi_appel'] = 'Suivi appel';
            $this->applyCallFollowUpFilter($query, 'station_services', $request);
        }

        return $this->renderList(
            $request,
            'Stations service',
            'call-center-station-services',
            $query->select($selects),
            $columns + [
                'actions' => 'Actions',
            ],
            array_values(array_filter([
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
                $hasCallFollowUp ? $this->callFollowUpFilter($request->input('call_center_deja_appele')) : null,
            ]))
        );
    }

    public function updateStationServiceCallFollowUp(Request $request, int $stationService)
    {
        return $this->updateCallFollowUp($request, 'station_services', $stationService, 'Station service');
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
        $backLabel = 'Retour aux véhicules';

        if ($request->input('back') === 'user' && $request->filled('user_id')) {
            $backUrl = route('call-center.users.vehicules', ['user' => $request->input('user_id')]);
            $backLabel = "Retour aux véhicules de l'utilisateur";
        }

        return view('call-centers.vehicule-details', [
            'title' => 'Détails du véhicule',
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
        $hasCallFollowUp = $this->hasCallFollowUpColumns('station_de_lavages');
        $query = DB::table('station_de_lavages');
        $selects = [
            'station_de_lavages.id as row_id',
            'station_de_lavages.name',
            'station_de_lavages.contact',
            'station_de_lavages.adresse',
            'station_de_lavages.longitude',
            'station_de_lavages.latitude',
            'station_de_lavages.statut',
            'station_de_lavages.created_at as date_creation',
            DB::raw("'' as actions"),
        ];
        $columns = [
            'name' => 'Nom',
            'contact' => 'Contact',
            'adresse' => 'Adresse',
            'longitude' => 'Longitude',
            'latitude' => 'Latitude',
            'statut' => 'Statut',
            'date_creation' => 'Date de création',
        ];

        if ($this->hasColumn('station_de_lavages', 'created_by') && $this->tableExists('commercials')) {
            $query->leftJoin('commercials', 'commercials.id', '=', 'station_de_lavages.created_by');
            $selects[] = DB::raw("TRIM(CONCAT(COALESCE(commercials.prenoms, ''), ' ', COALESCE(commercials.nom, ''))) as commercial");
            $columns['commercial'] = 'Commercial';

            if ($request->filled('commercial_id')) {
                $query->where('station_de_lavages.created_by', $request->input('commercial_id'));
            }
        }

        $searchColumns = [];
        $this->pushSearchColumn($searchColumns, 'station_de_lavages.name');
        $this->pushSearchColumn($searchColumns, 'station_de_lavages.contact');
        $this->pushSearchColumn($searchColumns, 'station_de_lavages.adresse');

        $this->applySearch($query, $request->string('search')->toString(), $searchColumns);
        $this->applyStatusFilter($query, 'station_de_lavages', $request->input('statut'));

        if ($hasCallFollowUp) {
            $selects[] = 'station_de_lavages.call_center_deja_appele';
            $selects[] = 'station_de_lavages.call_center_commentaire';
            $columns['suivi_appel'] = 'Suivi appel';
            $this->applyCallFollowUpFilter($query, 'station_de_lavages', $request);
        }

        return $this->renderList(
            $request,
            'Stations de lavage',
            'call-center-station-de-lavages',
            $query->select($selects),
            $columns + [
                'actions' => 'Actions',
            ],
            array_values(array_filter([
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Nom, contact, adresse',
                    'value' => $request->input('search', ''),
                ],
                $this->stationLavageCommercialFilter($request->input('commercial_id')),
                $this->statusFilter($request->input('statut')),
                $hasCallFollowUp ? $this->callFollowUpFilter($request->input('call_center_deja_appele')) : null,
            ]))
        );
    }

    public function updateStationDeLavageCallFollowUp(Request $request, int $stationDeLavage)
    {
        return $this->updateCallFollowUp($request, 'station_de_lavages', $stationDeLavage, 'Station de lavage');
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
                'type_piece' => 'Type de pièce',
                'statut' => 'Statut',
                'date_creation' => 'Date de création',
            ],
            [
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Utilisateur, marque, type de pièce',
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
            'modele' => 'Modèle',
            'type_demande' => 'Type de demande',
            'type_vehicule' => 'Type de véhicule',
            'statut' => 'Statut',
        ];

        if ($this->hasColumn('annonce_concessionnaires', 'created_at')) {
            $selects[] = 'annonce_concessionnaires.created_at as date_creation';
            $columns['date_creation'] = 'Date de création';
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
            $columns['type_piece'] = 'Type de pièce';
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
            'Annonces concessionnaires',
            'call-center-annonce-concessionnaires',
            $query->select($selects),
            $columns,
            [
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Utilisateur, concessionnaire, modèle',
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
            'Annonces établissements',
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
                'etablissement' => 'Établissement',
                'is_visible' => 'Visible',
                'date_creation' => 'Date de création',
            ],
            [
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Utilisateur, établissement, marque',
                    'value' => $request->input('search', ''),
                ],
                [
                    'name' => 'etablissement_id',
                    'label' => 'Établissement',
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
        $hasCallFollowUp = $this->hasCallFollowUpColumns('concessionnaires');
        $query = DB::table('concessionnaires');
        $selects = [
            'concessionnaires.id as row_id',
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

        if ($this->hasColumn('concessionnaires', 'userconcessionnaire_id') && $this->tableExists('userconcessionnaires')) {
            $query->leftJoin('userconcessionnaires', 'userconcessionnaires.id', '=', 'concessionnaires.userconcessionnaire_id');
            $selects[] = DB::raw("TRIM(CONCAT(COALESCE(userconcessionnaires.prenoms, ''), ' ', COALESCE(userconcessionnaires.nom, ''))) as gestionnaire");
            $columns['gestionnaire'] = 'Gestionnaire';
            $searchColumns[] = 'userconcessionnaires.nom';
            $searchColumns[] = 'userconcessionnaires.prenoms';
        }

        $this->applySearch($query, $request->string('search')->toString(), $searchColumns);
        $this->applyStatusFilter($query, 'concessionnaires', $request->input('statut'));

        if ($hasCallFollowUp) {
            $selects[] = 'concessionnaires.call_center_deja_appele';
            $selects[] = 'concessionnaires.call_center_commentaire';
            $columns['suivi_appel'] = 'Suivi appel';
            $this->applyCallFollowUpFilter($query, 'concessionnaires', $request);
        }

        return $this->renderList(
            $request,
            'Concessionnaires',
            'call-center-concessionnaires',
            $query->select($selects),
            $columns + [
                'statut' => 'Statut',
                'date_creation' => 'Date de création',
                'actions' => 'Actions',
            ],
            array_values(array_filter([
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Nom, email, contact, gestionnaire',
                    'value' => $request->input('search', ''),
                ],
                $this->statusFilter($request->input('statut')),
                $hasCallFollowUp ? $this->callFollowUpFilter($request->input('call_center_deja_appele')) : null,
            ]))
        );
    }

    public function updateConcessionnaireCallFollowUp(Request $request, int $concessionnaire)
    {
        return $this->updateCallFollowUp($request, 'concessionnaires', $concessionnaire, 'Concessionnaire');
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
        ];

        if ($this->hasColumn('etablissements', 'created_at')) {
            $selects[] = 'etablissements.created_at as date_creation';
            $columns['date_creation'] = 'Date de création';
        }

        if ($hasCallFollowUp) {
            $selects[] = 'etablissements.call_center_deja_appele';
            $selects[] = 'etablissements.call_center_commentaire';
            $columns['suivi_appel'] = 'Suivi appel';
        }

        if ($this->tableExists('categorie_services') && $this->hasColumn('etablissements', 'categorie_service_id')) {
            $query->leftJoin('categorie_services', 'categorie_services.id', '=', 'etablissements.categorie_service_id');
            $selects[] = 'categorie_services.libelle as categorie';
            $columns['categorie'] = 'Catégorie';
        }

        if ($this->tableExists('type_etablissements') && $this->hasColumn('etablissements', 'type_etablissement_id')) {
            $query->leftJoin('type_etablissements', 'type_etablissements.id', '=', 'etablissements.type_etablissement_id');
            $selects[] = 'type_etablissements.libelle as type_etablissement';
            $columns['type_etablissement'] = 'Type d’établissement';
        }

        if ($this->tableExists('villes') && $this->hasColumn('etablissements', 'ville_id')) {
            $query->leftJoin('villes', 'villes.id', '=', 'etablissements.ville_id');
            $selects[] = DB::raw(($villeLabel ? 'villes.' . $villeLabel : 'NULL') . ' as ville');
            $columns['ville'] = 'Ville';
        }

        if ($this->tableExists('communes') && $this->hasColumn('etablissements', 'commune_id')) {
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
        if ($this->tableExists('villes') && $this->hasColumn('etablissements', 'ville_id')) {
            if ($villeLabel) {
                $searchColumns[] = 'villes.' . $villeLabel;
            }
        }
        if ($this->tableExists('communes') && $this->hasColumn('etablissements', 'commune_id')) {
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

        if ($hasCallFollowUp) {
            $this->applyCallFollowUpFilter($query, 'etablissements', $request);
        }

        return $this->renderList(
            $request,
            'Établissements',
            'call-center-etablissements',
            $query->select($selects),
            $columns + [
                'statut' => 'Statut',
                'actions' => 'Actions',
            ],
            array_values(array_filter([
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
                $hasCallFollowUp ? $this->callFollowUpFilter($request->input('call_center_deja_appele')) : null,
            ]))
        );
    }

    public function updateEtablissementCallFollowUp(Request $request, int $etablissement)
    {
        return $this->updateCallFollowUp($request, 'etablissements', $etablissement, 'Établissement');
    }

    public function etablissementArticles(Request $request, int $etablissement)
    {
        abort_unless($this->tableExists('articles'), 404);

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
            'Articles de l\'établissement',
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
                'libelle' => 'Libellé',
                'description' => 'Description',
                'amount' => 'Montant',
                'image' => 'Image',
                'date_creation' => 'Date de création',
            ]),
            [
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Libellé, description, montant',
                    'value' => $request->input('search', ''),
                ],
            ],
            route('call-center.etablissements'),
            'Retour aux établissements'
        );
    }

    public function etablissementPromotions(Request $request, int $etablissement)
    {
        abort_unless($this->tableExists('promotions'), 404);

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
            'Promotions de l\'établissement',
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
                'libelle' => 'Libellé',
                'mobile' => 'Mobile',
                'date_debut' => 'Date de début',
                'date_fin' => 'Date fin',
                'statut' => 'Statut',
                'date_creation' => 'Date de création',
            ]),
            array_values(array_filter([
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Libellé, mobile, description',
                    'value' => $request->input('search', ''),
                ],
                $this->statusFilter($request->input('statut')),
            ])),
            route('call-center.etablissements'),
            'Retour aux établissements'
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
            'Abonnements de l\'établissement',
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
                'duree' => 'Durée',
                'prix' => 'Prix',
                'date_debut' => 'Date de début',
                'date_fin' => 'Date fin',
                'statut' => 'Statut',
                'date_creation' => 'Date de création',
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
            'Retour aux établissements'
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
                'libelle' => 'Libellé',
                'image' => 'Image',
                'date_creation' => 'Date de création',
                'date_mise_a_jour' => 'Date de mise à jour',
            ],
            [
                [
                    'name' => 'search',
                    'label' => 'Recherche',
                    'type' => 'text',
                    'placeholder' => 'Libellé',
                    'value' => $request->input('search', ''),
                ],
            ]
        );
    }

    public function filterOptions(Request $request, string $token)
    {
        $options = Cache::get(self::REMOTE_OPTIONS_PREFIX . $token, []);
        // Insensible a la casse et aux accents : "kone" trouve "Koné".
        $normalize = fn ($value) => mb_strtolower(\Illuminate\Support\Str::ascii((string) $value));
        $keyword = $normalize(trim((string) $request->input('q', '')));

        $matches = collect($options)
            ->when($keyword !== '', fn ($items) => $items->filter(
                fn ($option) => str_contains($normalize($option['label']), $keyword) || (string) $option['value'] === $keyword
            ))
            ->take(self::REMOTE_OPTIONS_LIMIT)
            ->values();

        return response()->json($matches);
    }

    private function renderList(
        Request $request,
        string $title,
        string $menu,
        Builder $query,
        array $columns,
        array $filters,
        ?callable $decorateRows = null,
        ?callable $applyOrder = null
    ) {
        if (array_key_exists('date_creation', $columns)) {
            $this->applyDateCreationFilter($query, $request);
            $filters = array_merge($filters, $this->dateCreationFilters($request));
        }

        $filters = array_map(fn ($filter) => $this->withRemoteOptions($filter), $filters);

        if ($applyOrder !== null) {
            $applyOrder($query);
        } elseif (($baseTable = $this->baseTable($query)) !== null && $this->hasColumn($baseTable, 'id')) {
            // Meme ordre chronologique que created_at, mais via la cle primaire : MySQL lit seulement
            // les lignes de la page au lieu de trier toute la table (created_at n'est pas indexe).
            $query->orderByDesc("{$baseTable}.id");
        } else {
            $query->orderByDesc($this->resolveOrderColumn(array_keys($columns)));
        }

        $perPage = (int) $request->input('per_page', self::PER_PAGE_OPTIONS[0]);
        $perPage = in_array($perPage, self::PER_PAGE_OPTIONS, true) ? $perPage : self::PER_PAGE_OPTIONS[0];

        $total = $this->cachedCount($query);
        $page = min(max(1, (int) $request->input('page', 1)), max(1, (int) ceil($total / $perPage)));
        $rows = $total > 0 ? (clone $query)->forPage($page, $perPage)->get() : collect();

        $items = (new LengthAwarePaginator($rows, $total, $perPage, $page, [
            'path' => $request->url(),
            'pageName' => 'page',
        ]))->withQueryString();

        if ($decorateRows !== null) {
            $items->setCollection($items->getCollection()->map($decorateRows));
        }

        // Les colonnes absentes du schema ne sont pas selectionnees : inutile d'afficher une colonne de tirets.
        // Exceptions : colonnes construites par la vue (suivi d'appel, menu actions des contacts appelables).
        $firstItem = $items->getCollection()->first();
        $hasCallActions = array_key_exists($menu, CallCenterOutcomes::MENU_CIBLES);
        if ($firstItem !== null) {
            $columns = array_filter(
                $columns,
                fn ($label, $key) => $key === 'suivi_appel'
                    || ($key === 'actions' && $hasCallActions)
                    || property_exists($firstItem, $key),
                ARRAY_FILTER_USE_BOTH
            );
        }

        // Dernier appel de chaque contact (tous agents) : evite qu'un autre agent rappelle sans le savoir.
        if ($hasCallActions && array_key_exists('suivi_appel', $columns) && $items->count() > 0) {
            $lastCalls = CallCenterCallLog::lastCalls(
                CallCenterOutcomes::MENU_CIBLES[$menu],
                $items->getCollection()->pluck('row_id')->all()
            );
            $items->getCollection()->each(function ($row) use ($lastCalls) {
                $row->last_call = $lastCalls[(int) ($row->row_id ?? 0)] ?? null;
            });
        }

        return view('call-centers.table', [
            'title' => $title,
            'menu' => $menu,
            'user' => Auth::guard('call_center')->user(),
            'columns' => $columns,
            'items' => $items,
            'filters' => $filters,
            'hasActiveFilters' => collect($request->except(['page', 'per_page']))->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty(),
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
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
        array $filters,
        ?callable $applyOrder = null
    ) {
        $query = $queryFactory();
        $configureQuery($query);

        $response = $this->renderList($request, $title, 'call-center-users', $query->select($selects), $columns, $filters, null, $applyOrder);
        $response->with('backUrl', route('call-center.users'));
        $response->with('backLabel', 'Retour aux usagers');
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
        $from = $this->parseFilterDate($request->input('date_from'));
        $to = $this->parseFilterDate($request->input('date_to'));
        $column = $this->selectedColumnFor($query, 'date_creation');

        if ($column === null) {
            // Expression calculee : on garde le filtre sur l'alias.
            if ($from) {
                $query->havingRaw('DATE(date_creation) >= ?', [$from->toDateString()]);
            }
            if ($to) {
                $query->havingRaw('DATE(date_creation) <= ?', [$to->toDateString()]);
            }

            return;
        }

        // Filtre sur la vraie colonne (WHERE) et non sur l'alias (HAVING) : le comptage n'a plus
        // besoin de materialiser toute la requete, et un index sur la colonne reste utilisable.
        if ($from) {
            $query->where($column, '>=', $from->startOfDay()->toDateTimeString());
        }
        if ($to) {
            $query->where($column, '<', $to->addDay()->startOfDay()->toDateTimeString());
        }
    }

    private function parseFilterDate(mixed $value): ?\Carbon\Carbon
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return \Carbon\Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        } catch (\Throwable $exception) {
            return null;
        }
    }

    /** Colonne reelle d'un alias du select ("users.created_at as date_creation" -> "users.created_at"). */
    private function selectedColumnFor(Builder $query, string $alias): ?string
    {
        foreach ($query->columns ?? [] as $column) {
            if (is_string($column) && preg_match('/^([\w.]+)\s+as\s+' . preg_quote($alias, '/') . '$/i', trim($column), $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    private function baseTable(Builder $query): ?string
    {
        if (! is_string($query->from)) {
            return null;
        }

        $parts = preg_split('/\s+as\s+/i', trim($query->from));

        return trim(end($parts), '` ');
    }

    /**
     * Total de la liste, mis en cache brievement : sur des milliers de lignes le COUNT est la requete
     * la plus couteuse et il est rejoue a chaque changement de page.
     */
    private function cachedCount(Builder $query): int
    {
        $key = 'call_center.count.' . Cache::get(self::COUNT_VERSION_KEY, 0) . '.'
            . md5($query->toSql() . '|' . serialize($query->getBindings()));

        return (int) Cache::remember($key, self::COUNT_CACHE_SECONDS, function () use ($query) {
            $baseTable = $this->baseTable($query);

            // Sans filtre, les LEFT JOIN sur une cle primaire ne changent pas le nombre de lignes :
            // on compte directement la table principale.
            if ($baseTable !== null && empty($query->wheres) && empty($query->havings) && empty($query->groups)
                && collect($query->joins ?? [])->every(fn ($join) => $this->isLeftJoinOnPrimaryKey($join))) {
                return DB::table($baseTable)->count();
            }

            return (clone $query)->getCountForPagination();
        });
    }

    private function isLeftJoinOnPrimaryKey($join): bool
    {
        if ($join->type !== 'left' || count($join->wheres) !== 1) {
            return false;
        }

        $where = $join->wheres[0];
        $joinedKey = trim(preg_split('/\s+as\s+/i', $join->table)[0]) . '.id';

        return ($where['type'] ?? null) === 'Column'
            && ($where['operator'] ?? null) === '='
            && in_array($joinedKey, [$where['first'] ?? null, $where['second'] ?? null], true);
    }

    private function dateCreationFilters(Request $request): array
    {
        return [
            [
                'name' => 'date_from',
                'label' => 'Date de début',
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

    /**
     * Au-dela de quelques centaines d'options (professionnels, etablissements...), un <select> alourdit
     * chaque page de centaines de Ko. On garde la liste cote serveur et le filtre devient une recherche.
     */
    private function withRemoteOptions(array $filter): array
    {
        $options = $filter['options'] ?? [];

        if (($filter['type'] ?? null) !== 'select' || count($options) <= self::REMOTE_OPTIONS_THRESHOLD) {
            return $filter;
        }

        $token = md5(serialize($options));
        if (! Cache::has(self::REMOTE_OPTIONS_PREFIX . $token)) {
            Cache::put(self::REMOTE_OPTIONS_PREFIX . $token, $options, self::REMOTE_OPTIONS_SECONDS);
        }

        $selected = collect($options)->firstWhere('value', (string) ($filter['value'] ?? ''));

        return array_merge($filter, [
            'remote' => route('call-center.filter-options', $token),
            'options' => [],
            'selected_label' => $selected['label'] ?? '',
        ]);
    }

    private function alertsBaseQuery(): Builder
    {
        return DB::table('alerts')
            ->leftJoin('users', 'users.id', '=', 'alerts.user_id')
            ->leftJoin('vehicules', 'vehicules.id', '=', 'alerts.vehicule_id')
            ->leftJoin('type_alerts', 'type_alerts.id', '=', 'alerts.type_alert_id');
    }

    private function alertExpirationFilter(mixed $value): array
    {
        return [
            'name' => 'echeance',
            'label' => 'Échéance',
            'type' => 'select',
            'value' => (string) ($value ?? ''),
            'options' => [
                ['value' => 'expirees', 'label' => 'Expirées'],
                ['value' => 'aujourdhui', 'label' => "Expirent aujourd'hui"],
                ['value' => '7j', 'label' => 'Expirent sous ' . self::ALERT_URGENT_DAYS . ' jours'],
                ['value' => '30j', 'label' => 'Expirent sous ' . self::ALERT_SOON_DAYS . ' jours'],
                ['value' => 'a_traiter', 'label' => 'Expirées ou sous ' . self::ALERT_SOON_DAYS . ' jours'],
                ['value' => 'valides', 'label' => 'Valides (+' . self::ALERT_SOON_DAYS . ' jours)'],
            ],
        ];
    }

    private function applyAlertExpirationFilter(Builder $query, mixed $value): void
    {
        $today = now()->toDateString();

        match ((string) $value) {
            'expirees' => $query->where('alerts.date_fin', '<', $today),
            'aujourdhui' => $query->where('alerts.date_fin', $today),
            '7j' => $query->whereBetween('alerts.date_fin', [$today, now()->addDays(self::ALERT_URGENT_DAYS)->toDateString()]),
            '30j' => $query->whereBetween('alerts.date_fin', [$today, now()->addDays(self::ALERT_SOON_DAYS)->toDateString()]),
            'a_traiter' => $query->where('alerts.date_fin', '<=', now()->addDays(self::ALERT_SOON_DAYS)->toDateString()),
            'valides' => $query->where('alerts.date_fin', '>', now()->addDays(self::ALERT_SOON_DAYS)->toDateString()),
            default => null,
        };
    }

    private function applyAlertOrder(Builder $query, mixed $expiration): void
    {
        if ((string) $expiration === 'expirees') {
            // Les plus recemment expirees d'abord : ce sont les plus faciles a relancer.
            $query->orderByDesc('alerts.date_fin');
        } elseif (in_array((string) $expiration, ['aujourdhui', '7j', '30j', 'valides'], true)) {
            $query->orderBy('alerts.date_fin');
        } else {
            // Sans filtre precis : les echeances les plus proches d'aujourd'hui (passees ou a venir) d'abord.
            $query->orderByRaw('ABS(DATEDIFF(alerts.date_fin, CURDATE()))')->orderBy('alerts.date_fin');
        }

        $query->orderByDesc('alerts.id');
    }

    /** Les agregats du dashboard parcourent toute la table alerts : on les garde une minute. */
    private function cachedDashboard(string $block, callable $compute): mixed
    {
        $key = 'call_center.dashboard.' . Cache::get(self::COUNT_VERSION_KEY, 0) . '.' . now()->toDateString() . '.' . $block;

        return Cache::remember($key, self::COUNT_CACHE_SECONDS, $compute);
    }

    private function alertStats(): ?object
    {
        if (! $this->tableExists('alerts') || ! $this->hasColumn('alerts', 'date_fin')) {
            return null;
        }

        $today = now()->toDateString();

        return DB::table('alerts')
            ->selectRaw('SUM(date_fin < ?) as expirees', [$today])
            ->selectRaw('SUM(date_fin = ?) as aujourdhui', [$today])
            ->selectRaw('SUM(date_fin BETWEEN ? AND ?) as sous_7j', [$today, now()->addDays(self::ALERT_URGENT_DAYS)->toDateString()])
            ->selectRaw('SUM(date_fin BETWEEN ? AND ?) as sous_30j', [$today, now()->addDays(self::ALERT_SOON_DAYS)->toDateString()])
            ->first();
    }

    private function dashboardAlerts(string $kind)
    {
        if (! $this->tableExists('alerts') || ! $this->hasColumn('alerts', 'date_fin')) {
            return collect();
        }

        $selects = [
            'alerts.id',
            'alerts.user_id',
            'alerts.date_fin',
            DB::raw('DATEDIFF(alerts.date_fin, CURDATE()) as echeance'),
            DB::raw("TRIM(CONCAT(COALESCE(users.nom, ''), ' ', COALESCE(users.prenoms, ''))) as utilisateur"),
        ];
        $this->pushSelect($selects, 'users.mobile', 'mobile');
        $this->pushSelect($selects, 'users.indicatif', 'indicatif');
        $this->pushSelect($selects, 'users.is_whatsapp', 'is_whatsapp');
        $this->pushSelect($selects, 'vehicules.matricule', 'vehicule');
        $this->pushSelect($selects, 'type_alerts.libelle', 'type_alerte');

        $query = $this->alertsBaseQuery()->select($selects);
        $today = now()->toDateString();

        if ($kind === 'expired') {
            $query->where('alerts.date_fin', '<', $today)->orderByDesc('alerts.date_fin');
        } else {
            $query->whereBetween('alerts.date_fin', [$today, now()->addDays(self::ALERT_SOON_DAYS)->toDateString()])->orderBy('alerts.date_fin');
        }

        return $query->limit(6)->get();
    }

    private function contactUser(int $userId): ?object
    {
        $columns = array_values(array_filter(
            ['id', 'nom', 'prenoms', 'indicatif', 'mobile', 'is_whatsapp', 'email', 'call_center_deja_appele', 'call_center_commentaire'],
            fn ($column) => $this->hasColumn('users', $column)
        ));

        $contact = DB::table('users')->where('id', $userId)->first($columns);

        if ($contact && $this->hasColumn('alerts', 'date_fin')) {
            $contact->alertes_expirees = DB::table('alerts')
                ->where('user_id', $userId)
                ->where('date_fin', '<', now()->toDateString())
                ->count();
        }

        if ($contact) {
            $contact->last_call = CallCenterCallLog::lastCalls('users', [$userId])[$userId] ?? null;
        }

        return $contact;
    }

    private function usersWithExpiredAlerts(): array
    {
        if (! $this->tableExists('alerts') || ! $this->hasColumn('alerts', 'date_fin')) {
            return ['total' => 0, 'items' => collect()];
        }

        $hasCallFollowUp = $this->hasCallFollowUpColumns('users');
        $groupColumns = array_values(array_filter(
            ['users.id', 'users.nom', 'users.prenoms', 'users.indicatif', 'users.mobile', 'users.is_whatsapp', $hasCallFollowUp ? 'users.call_center_deja_appele' : null],
            fn ($column) => $column !== null && $this->hasColumn('users', explode('.', $column)[1])
        ));

        $query = DB::table('alerts')
            ->join('users', 'users.id', '=', 'alerts.user_id')
            ->where('alerts.date_fin', '<', now()->toDateString());

        $total = (clone $query)->distinct()->count('users.id');

        $items = $query
            ->select($groupColumns)
            ->selectRaw('COUNT(*) as nb_expirees')
            ->selectRaw('MAX(alerts.date_fin) as derniere_expiration')
            ->selectRaw('DATEDIFF(MAX(alerts.date_fin), CURDATE()) as echeance')
            ->groupBy($groupColumns)
            // Les users pas encore appeles d'abord, puis les expirations les plus recentes.
            ->when($hasCallFollowUp, fn ($q) => $q->orderBy('users.call_center_deja_appele'))
            ->orderByDesc('derniere_expiration')
            ->limit(8)
            ->get();

        return ['total' => $total, 'items' => $items];
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

    private function stationLavageCommercialFilter(mixed $value): ?array
    {
        if (! $this->tableExists('station_de_lavages')
            || ! $this->tableExists('commercials')
            || ! $this->hasColumn('station_de_lavages', 'created_by')) {
            return null;
        }

        $commercials = DB::table('commercials')
            ->join('station_de_lavages', 'station_de_lavages.created_by', '=', 'commercials.id')
            ->whereNotNull('station_de_lavages.created_by')
            ->select('commercials.id', 'commercials.nom', 'commercials.prenoms', 'commercials.mobile')
            ->distinct()
            ->orderBy('commercials.nom')
            ->orderBy('commercials.prenoms')
            ->get();

        return [
            'name' => 'commercial_id',
            'label' => 'Commercial',
            'type' => 'select',
            'value' => (string) ($value ?? ''),
            'options' => $commercials->map(fn ($commercial) => [
                'value' => $commercial->id,
                'label' => trim(($commercial->nom ?? '') . ' ' . ($commercial->prenoms ?? '')) . (!empty($commercial->mobile) ? ' - ' . $commercial->mobile : ''),
            ])->all(),
        ];
    }

    private function callFollowUpFilter(mixed $value): array
    {
        return [
            'name' => 'call_center_deja_appele',
            'label' => 'Appel',
            'type' => 'select',
            'value' => (string) ($value ?? ''),
            'options' => [
                ['value' => '1', 'label' => 'Déjà appelé'],
                ['value' => '0', 'label' => 'Non appelé'],
            ],
        ];
    }

    private function hasCallFollowUpColumns(string $table): bool
    {
        return $this->hasColumn($table, 'call_center_deja_appele')
            && $this->hasColumn($table, 'call_center_commentaire');
    }

    private function applyCallFollowUpFilter(Builder $query, string $table, Request $request): void
    {
        if ($request->filled('call_center_deja_appele')) {
            $query->where("{$table}.call_center_deja_appele", $request->boolean('call_center_deja_appele'));
        }
    }

    private function updateCallFollowUp(Request $request, string $table, int $id, string $label)
    {
        if (! $this->tableExists($table) || ! $this->hasCallFollowUpColumns($table)) {
            return back()->with([
                'type' => 'alert-danger',
                'message' => "Le suivi d'appel {$label} n'est pas encore disponible. Créez les colonnes avant d'enregistrer une note.",
            ]);
        }

        $request->validate([
            'call_center_deja_appele' => ['sometimes', 'required', 'boolean'],
            'call_center_commentaire' => ['nullable', 'string', 'max:2000'],
        ]);

        $current = DB::table($table)
            ->where('id', $id)
            ->first(['id', 'call_center_deja_appele', 'call_center_commentaire']);

        if (! $current) {
            return back()->with([
                'type' => 'alert-danger',
                'message' => "{$label} introuvable.",
            ]);
        }

        $dejaAppele = $request->has('call_center_deja_appele')
            ? $request->boolean('call_center_deja_appele')
            : (bool) $current->call_center_deja_appele;

        $data = ['call_center_deja_appele' => $dejaAppele];

        if ($request->has('call_center_commentaire')) {
            $data['call_center_commentaire'] = $request->input('call_center_commentaire');
        }

        if ($this->hasColumn($table, 'call_center_called_at')) {
            $data['call_center_called_at'] = $dejaAppele ? now() : null;
        }

        DB::table($table)
            ->where('id', $id)
            ->update($data);

        Cache::forever(self::COUNT_VERSION_KEY, (int) Cache::get(self::COUNT_VERSION_KEY, 0) + 1);

        return back()->with([
            'type' => 'alert-success',
            'message' => 'Suivi appel enregistré avec succès.',
        ]);
    }

    private function tableOptions(string $table, string $valueColumn, string $labelColumn): array
    {
        if (! $this->tableExists($table) || ! $this->hasColumn($table, $valueColumn) || ! $this->hasColumn($table, $labelColumn)) {
            return [];
        }

        return Cache::remember(
            'call_center.options.' . implode('.', [__FUNCTION__, $table, $valueColumn, $labelColumn]),
            self::OPTIONS_CACHE_SECONDS,
            fn () => DB::table($table)
                ->select("{$valueColumn} as value", "{$labelColumn} as label")
                ->orderBy($labelColumn)
                ->get()
                ->map(fn ($item) => ['value' => (string) $item->value, 'label' => $item->label])
                ->all()
        );
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
        if (! $this->tableExists($table) || ! $this->hasColumn($table, $column) || ! $this->hasColumn($table, $labelColumn)) {
            return [];
        }

        return Cache::remember(
            'call_center.options.' . implode('.', [__FUNCTION__, $table, $column, $labelColumn]),
            self::OPTIONS_CACHE_SECONDS,
            fn () => DB::table($table)
                ->select("{$column} as value", "{$labelColumn} as label")
                ->whereNotNull($column)
                ->distinct()
                ->orderBy($labelColumn)
                ->get()
                ->map(fn ($item) => ['value' => (string) $item->value, 'label' => $item->label])
                ->all()
        );
    }

    private function nameOptions(string $table): array
    {
        if (! $this->tableExists($table) || ! $this->hasColumn($table, 'id') || ! $this->hasColumn($table, 'nom')) {
            return [];
        }

        return Cache::remember(
            'call_center.options.' . implode('.', [__FUNCTION__, $table]),
            self::OPTIONS_CACHE_SECONDS,
            fn () => DB::table($table)
                ->select(
                    'id as value',
                    DB::raw("TRIM(CONCAT(COALESCE(prenoms, ''), ' ', COALESCE(nom, ''))) as label")
                )
                ->orderBy('prenoms')
                ->orderBy('nom')
                ->get()
                ->map(fn ($item) => ['value' => (string) $item->value, 'label' => trim($item->label) !== '' ? $item->label : 'Sans nom'])
                ->all()
        );
    }

    private function hasColumn(string $table, string $column): bool
    {
        return in_array($column, $this->tableColumns($table) ?? [], true);
    }

    private function tableExists(string $table): bool
    {
        return $this->tableColumns($table) !== null;
    }

    private function tableColumns(string $table): ?array
    {
        return SchemaCache::columns($table);
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
            if ($this->tableExists($table)) {
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

        // L'API usager encode les chemins avec json_encode() alors que le modele a deja le cast "array" :
        // la colonne contient alors une chaine JSON encodee plusieurs fois ("[\"images\/vehicule\/...\"]").
        $decoded = $photos;
        for ($depth = 0; $depth < 3 && is_string($decoded); $depth++) {
            $next = json_decode($decoded, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                break;
            }

            $decoded = $next;
        }

        if (is_array($decoded)) {
            return array_values(array_filter($decoded, fn ($item) => is_string($item) && trim($item) !== ''));
        }

        return is_string($decoded) && trim($decoded) !== '' ? [$decoded] : [];
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
