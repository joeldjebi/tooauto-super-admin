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
use App\Models\Abonnement_usager;
use App\Models\Forfait_usager;
use App\Models\UserReductionCard;
use App\Services\CommercialWalletService;
use App\Services\ReductionCardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Redirector;
use Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UsagerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function indexUsager(Request $request)
    {
        $data['title'] ='Liste des usagers';
        $data['menu'] ='usager';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        $today = Carbon::today()->toDateString();

        $presenceFilters = [
            'vehicule_filter' => $request->get('vehicule_filter'),
            'alerte_filter' => $request->get('alerte_filter'),
            'abonnement_filter' => $request->get('abonnement_filter'),
            'annonce_filter' => $request->get('annonce_filter'),
            'document_filter' => $request->get('document_filter'),
        ];

        $perPage = (int) $request->get('per_page', 50);
        $perPage = in_array($perPage, [25, 50, 100], true) ? $perPage : 50;

        $usagersQuery = User::select(['id', 'nom', 'prenoms', 'indicatif', 'mobile', 'email', 'statut', 'commercial_id'])
            ->with([
                'commercial:id,nom,prenoms,mobile',
                'abonnementUsagers' => function ($query) {
                    $query->select(['id', 'user_id', 'forfait_id', 'date_debut', 'date_fin', 'statut', 'is_free'])
                        ->with('forfait_usager:id,libelle')
                        ->orderByDesc('date_fin')
                        ->orderByDesc('id');
                },
            ])
            ->withCount(['vehicules', 'alerts', 'annonces'])
            ->orderBy('id', 'desc');

        $this->addAutodocsCountSelect($usagersQuery);

        $this->applyRelationPresenceFilter($usagersQuery, 'vehicules', $presenceFilters['vehicule_filter']);
        $this->applyRelationPresenceFilter($usagersQuery, 'alerts', $presenceFilters['alerte_filter']);
        $this->applyRelationPresenceFilter($usagersQuery, 'annonces', $presenceFilters['annonce_filter']);

        if ($presenceFilters['abonnement_filter'] === 'with') {
            $usagersQuery->whereHas('abonnementUsagers', function ($query) use ($today) {
                $query->where('statut', 1)->whereDate('date_fin', '>=', $today);
            });
        } elseif ($presenceFilters['abonnement_filter'] === 'without') {
            $usagersQuery->whereDoesntHave('abonnementUsagers', function ($query) use ($today) {
                $query->where('statut', 1)->whereDate('date_fin', '>=', $today);
            });
        }

        $this->applyAutodocPresenceFilter($usagersQuery, $presenceFilters['document_filter']);

        $data["usagers"] = $usagersQuery->simplePaginate($perPage)->appends($request->query());
        $data["presence_filters"] = $presenceFilters;
        $data["per_page"] = $perPage;

        $data["forfait_usagers"] = Forfait_usager::where('statut', 1)
            ->orderBy('libelle')
            ->get();

        $data["usagers"]->each(function ($usager) use ($today) {
            $activeAbonnement = $usager->abonnementUsagers->first(function ($abonnement) use ($today) {
                return (int) $abonnement->statut === 1 && $abonnement->date_fin >= $today;
            });

            $usager->abonnement_affiche = $activeAbonnement ?: $usager->abonnementUsagers->first();
            $usager->abonnement_est_actif = ! empty($activeAbonnement);
        });

        // dd($data["usagers"]);

        return view('usagers.index',$data);
    }

    private function applyRelationPresenceFilter($query, string $relation, ?string $filter): void
    {
        if ($filter === 'with') {
            $query->has($relation);
        } elseif ($filter === 'without') {
            $query->doesntHave($relation);
        }
    }

    private function applyAutodocPresenceFilter($query, ?string $filter): void
    {
        if (! in_array($filter, ['with', 'without'], true) || ! $this->autodocsCanBeLinkedToUsagers()) {
            return;
        }

        $method = $filter === 'with' ? 'whereExists' : 'whereNotExists';

        $query->{$method}(function ($subQuery) {
            $subQuery->selectRaw('1')
                ->from('autodocs')
                ->where(function ($documentQuery) {
                    if (Schema::hasColumn('autodocs', 'user_id')) {
                        $documentQuery->orWhereColumn('autodocs.user_id', 'users.id');
                    }

                    if (Schema::hasColumn('autodocs', 'usager_id')) {
                        $documentQuery->orWhereColumn('autodocs.usager_id', 'users.id');
                    }

                    if (Schema::hasColumn('autodocs', 'vehicule_id') && Schema::hasTable('vehicules')) {
                        $documentQuery->orWhereExists(function ($vehiculeQuery) {
                            $vehiculeQuery->selectRaw('1')
                                ->from('vehicules')
                                ->whereColumn('vehicules.id', 'autodocs.vehicule_id')
                                ->whereColumn('vehicules.user_id', 'users.id');
                        });
                    }
                });
        });
    }

    private function addAutodocsCountSelect($query): void
    {
        if (! $this->autodocsCanBeLinkedToUsagers()) {
            $query->selectRaw('0 as autodocs_count');
            return;
        }

        $query->selectSub(function ($subQuery) {
            $subQuery->from('autodocs')
                ->selectRaw('COUNT(DISTINCT autodocs.id)')
                ->where(function ($documentQuery) {
                    if (Schema::hasColumn('autodocs', 'user_id')) {
                        $documentQuery->orWhereColumn('autodocs.user_id', 'users.id');
                    }

                    if (Schema::hasColumn('autodocs', 'usager_id')) {
                        $documentQuery->orWhereColumn('autodocs.usager_id', 'users.id');
                    }

                    if (Schema::hasColumn('autodocs', 'vehicule_id') && Schema::hasTable('vehicules')) {
                        $documentQuery->orWhereExists(function ($vehiculeQuery) {
                            $vehiculeQuery->selectRaw('1')
                                ->from('vehicules')
                                ->whereColumn('vehicules.id', 'autodocs.vehicule_id')
                                ->whereColumn('vehicules.user_id', 'users.id');
                        });
                    }
                });
        }, 'autodocs_count');
    }

    private function autodocsCanBeLinkedToUsagers(): bool
    {
        return Schema::hasTable('autodocs')
            && (
                Schema::hasColumn('autodocs', 'user_id')
                || Schema::hasColumn('autodocs', 'usager_id')
                || (
                    Schema::hasColumn('autodocs', 'vehicule_id')
                    && Schema::hasTable('vehicules')
                    && Schema::hasColumn('vehicules', 'user_id')
                )
            );
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
        return $this->destroyUsager($userId);
    }

    public function destroyUsager($id)
    {
        DB::beginTransaction();

        try {
            $usager = User::findOrFail($id);
            $vehiculeIds = Vehicule::where('user_id', $id)->pluck('id');

            $deletedCounts = [
                'vehicules' => 0,
                'alerts' => 0,
                'autodocs' => 0,
                'annonces' => 0,
            ];

            $deletedCounts['alerts'] += Alert::where('user_id', $id)->delete();

            if (Schema::hasColumn('alerts', 'usager_id')) {
                $deletedCounts['alerts'] += Alert::where('usager_id', $id)->delete();
            }

            if ($vehiculeIds->isNotEmpty()) {
                $deletedCounts['alerts'] += Alert::whereIn('vehicule_id', $vehiculeIds)->delete();
            }

            if (Schema::hasTable('autodocs')) {
                $autodocsHasUserId = Schema::hasColumn('autodocs', 'user_id');
                $autodocsHasUsagerId = Schema::hasColumn('autodocs', 'usager_id');
                $autodocsHasVehiculeId = Schema::hasColumn('autodocs', 'vehicule_id');

                if ($autodocsHasUserId || $autodocsHasUsagerId || ($autodocsHasVehiculeId && $vehiculeIds->isNotEmpty())) {
                    $autodocsQuery = DB::table('autodocs');

                    $autodocsQuery->where(function ($query) use ($id, $vehiculeIds, $autodocsHasUserId, $autodocsHasUsagerId, $autodocsHasVehiculeId) {
                        if ($autodocsHasUserId) {
                            $query->orWhere('user_id', $id);
                        }

                        if ($autodocsHasUsagerId) {
                            $query->orWhere('usager_id', $id);
                        }

                        if ($autodocsHasVehiculeId && $vehiculeIds->isNotEmpty()) {
                            $query->orWhereIn('vehicule_id', $vehiculeIds);
                        }
                    });

                    $deletedCounts['autodocs'] = $autodocsQuery->delete();
                }
            }

            $annonceQuery = Annonce::query()->where('usager_id', $id);

            if (Schema::hasColumn('annonces', 'user_id')) {
                $annonceQuery->orWhere('user_id', $id);
            }

            $annonceIds = $annonceQuery->pluck('id');

            if (Schema::hasTable('annonce_etablissements')) {
                if ($annonceIds->isNotEmpty()) {
                    DB::table('annonce_etablissements')
                        ->whereIn('annonce_id', $annonceIds)
                        ->delete();
                }
            }

            if ($annonceIds->isNotEmpty()) {
                $deletedCounts['annonces'] = Annonce::whereIn('id', $annonceIds)->delete();
            }

            $deletedCounts['vehicules'] = Vehicule::where('user_id', $id)->delete();

            $usager->delete();

            DB::commit();

            session()->flash('type', 'alert-success');
            session()->flash(
                'message',
                'Usager supprimé avec succès. ' .
                $deletedCounts['vehicules'] . ' véhicule(s), ' .
                $deletedCounts['alerts'] . ' alerte(s), ' .
                $deletedCounts['autodocs'] . ' autodoc(s), ' .
                $deletedCounts['annonces'] . ' annonce(s) supprimé(s).'
            );
        } catch (\Exception $e) {
            DB::rollBack();

            session()->flash('type', 'alert-danger');
            session()->flash(
                'message',
                'Une erreur est survenue lors de la suppression de l\'usager: ' . $e->getMessage()
            );
        }

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

        $data["usager"] = User::with([
            'commercial',
            'abonnementUsagers' => function ($query) {
                $query->with('forfait_usager')
                    ->orderByDesc('date_fin')
                    ->orderByDesc('id');
            },
        ])->find($id);
        if (!$data["usager"]) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Usager introuvable');
            return back();
        }

        $today = Carbon::today()->toDateString();
        $activeAbonnement = $data["usager"]->abonnementUsagers->first(function ($abonnement) use ($today) {
            return (int) $abonnement->statut === 1 && $abonnement->date_fin >= $today;
        });

        $data["usager"]->abonnement_affiche = $activeAbonnement ?: $data["usager"]->abonnementUsagers->first();
        $data["usager"]->abonnement_est_actif = ! empty($activeAbonnement);
        $data["forfait_usagers"] = Forfait_usager::where('statut', 1)
            ->orderBy('libelle')
            ->get();

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

        $data["reduction_cards"] = Schema::hasTable('user_reduction_cards')
            ? UserReductionCard::with(['reductionCard.forfaitUsager', 'abonnementUsager', 'histories'])
                ->where('user_id', $id)
                ->orderByDesc('statut')
                ->orderByDesc('date_fin')
                ->orderByDesc('id')
                ->get()
            : collect();

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

    public function bulkChangeForfaitUsager(Request $request)
    {
        $validated = $request->validate([
            'forfait_id' => 'required|integer|exists:forfait_usagers,id',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
        ], [
            'user_ids.required' => 'Sélectionnez au moins un usager.',
            'user_ids.min' => 'Sélectionnez au moins un usager.',
        ]);

        $forfait = Forfait_usager::find($validated['forfait_id']);
        if (!$forfait) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Forfait introuvable.');
            return back();
        }

        $userIds = collect($validated['user_ids'])->map(fn ($id) => (int) $id)->unique()->values();
        $usagers = User::whereIn('id', $userIds)->get();

        DB::beginTransaction();

        try {
            $createdCount = 0;
            $commissionCount = 0;

            foreach ($usagers as $usager) {
                $result = $this->createForfaitAbonnement($usager, $forfait);
                $createdCount++;

                if ($result['wallet_transaction']) {
                    $commissionCount++;
                }
            }

            DB::commit();

            session()->flash('type', 'alert-success');
            session()->flash(
                'message',
                $createdCount . ' forfait(s) attribué(s) avec succès.' .
                ($commissionCount > 0 ? ' ' . $commissionCount . ' commission(s) commercial créditée(s).' : '')
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors de l\'attribution globale: ' . $e->getMessage());
        }

        return back();
    }
    public function changeForfaitUsager(Request $request, $id)
    {
        $validated = $request->validate([
            'forfait_id' => 'required|integer|exists:forfait_usagers,id',
        ]);

        $usager = User::find($id);
        if (!$usager) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Usager introuvable.');
            return back();
        }

        $forfait = Forfait_usager::find($validated['forfait_id']);
        if (!$forfait) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Forfait introuvable.');
            return back();
        }

        DB::beginTransaction();

        try {
            $today = Carbon::today();
            $lastActiveAbonnement = Abonnement_usager::where('user_id', $usager->id)
                ->where('statut', 1)
                ->whereDate('date_fin', '>=', $today->toDateString())
                ->orderByDesc('date_fin')
                ->first();

            $dateDebut = $lastActiveAbonnement
                ? Carbon::parse($lastActiveAbonnement->date_fin)->addDay()
                : $today;
            $months = max(0, (int) $forfait->duree);
            $dateFin = $months > 0
                ? $dateDebut->copy()->addMonths($months)->subDay()
                : $dateDebut->copy();

            $abonnement = Abonnement_usager::create([
                'user_id' => $usager->id,
                'forfait_id' => $forfait->id,
                'date_debut' => $dateDebut->toDateString(),
                'date_fin' => $dateFin->toDateString(),
                'statut' => 1,
                'is_free' => ((float) $forfait->prix <= 0) ? 1 : 0,
            ]);

            $walletTransaction = app(CommercialWalletService::class)
                ->creditCommissionForAbonnement($abonnement);
            app(ReductionCardService::class)->syncForAbonnement($abonnement);

            DB::commit();

            session()->flash('type', 'alert-success');
            session()->flash(
                'message',
                'Forfait changé avec succès. Nouvelle période: ' .
                $dateDebut->format('d/m/Y') . ' au ' . $dateFin->format('d/m/Y') .
                ($walletTransaction ? ' Commission commercial créditée.' : '')
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue lors du changement de forfait: ' . $e->getMessage());
        }

        return back();
    }

    private function createForfaitAbonnement(User $usager, Forfait_usager $forfait): array
    {
        $today = Carbon::today();
        $lastActiveAbonnement = Abonnement_usager::where('user_id', $usager->id)
            ->where('statut', 1)
            ->whereDate('date_fin', '>=', $today->toDateString())
            ->orderByDesc('date_fin')
            ->first();

        $dateDebut = $lastActiveAbonnement
            ? Carbon::parse($lastActiveAbonnement->date_fin)->addDay()
            : $today;
        $months = max(0, (int) $forfait->duree);
        $dateFin = $months > 0
            ? $dateDebut->copy()->addMonths($months)->subDay()
            : $dateDebut->copy();

        $abonnement = Abonnement_usager::create([
            'user_id' => $usager->id,
            'forfait_id' => $forfait->id,
            'date_debut' => $dateDebut->toDateString(),
            'date_fin' => $dateFin->toDateString(),
            'statut' => 1,
            'is_free' => ((float) $forfait->prix <= 0) ? 1 : 0,
        ]);

        $walletTransaction = app(CommercialWalletService::class)
            ->creditCommissionForAbonnement($abonnement);
        app(ReductionCardService::class)->syncForAbonnement($abonnement);

        return [
            'abonnement' => $abonnement,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'wallet_transaction' => $walletTransaction,
        ];
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
