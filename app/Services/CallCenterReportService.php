<?php

namespace App\Services;

use App\Support\CallCenterOutcomes;
use App\Support\SchemaCache;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Rapports du call center calcules a partir du journal des appels (call_center_interactions).
 * Periode [from, to] et agent optionnel, appliques a tous les indicateurs.
 */
class CallCenterReportService
{
    /** Delai pendant lequel un abonnement est attribue a l'appel qui le precede. */
    public const CONVERSION_DAYS = 30;

    /** Fenetre "alertes a relancer" : echeance dans les N prochains jours. */
    public const ALERT_WINDOW_DAYS = 30;

    public const PERIODS = [
        'aujourdhui' => "Aujourd'hui",
        '7j' => '7 derniers jours',
        '30j' => '30 derniers jours',
        'mois' => 'Ce mois-ci',
        'mois_precedent' => 'Mois precedent',
        'personnalisee' => 'Personnalisee',
    ];

    /**
     * Lit la periode demandee (?periode=..., ?du=..., ?au=...) : [debut, fin, cle de periode].
     *
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    public static function resolvePeriod(\Illuminate\Http\Request $request, string $default = '30j'): array
    {
        $request->validate([
            'periode' => ['nullable', 'in:' . implode(',', array_keys(self::PERIODS))],
            'du' => ['nullable', 'date'],
            'au' => ['nullable', 'date'],
        ]);

        $period = $request->input('periode', $default);
        $today = now();

        [$from, $to] = match ($period) {
            'aujourdhui' => [$today->copy(), $today->copy()],
            '7j' => [$today->copy()->subDays(6), $today->copy()],
            'mois' => [$today->copy()->startOfMonth(), $today->copy()],
            'mois_precedent' => [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()],
            'personnalisee' => [
                Carbon::parse($request->input('du', $today->copy()->subDays(29)->toDateString())),
                Carbon::parse($request->input('au', $today->toDateString())),
            ],
            default => [$today->copy()->subDays(29), $today->copy()],
        };

        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        // Une periode trop longue rendrait la courbe illisible et les requetes lourdes.
        if ($from->diffInDays($to) > 366) {
            $from = $to->copy()->subDays(366);
        }

        return [$from->startOfDay(), $to->endOfDay(), $period];
    }

    public function __construct(
        private Carbon $from,
        private Carbon $to,
        private ?int $agentId = null,
    ) {
        $this->from = $from->copy()->startOfDay();
        $this->to = $to->copy()->endOfDay();
    }

    public static function available(): bool
    {
        return SchemaCache::hasTable('call_center_interactions');
    }

    /** Appels de la periode (alias "i"), filtres par agent. */
    public function calls(): Builder
    {
        return DB::table('call_center_interactions as i')
            ->whereBetween('i.created_at', [$this->from, $this->to])
            ->when($this->agentId, fn ($q) => $q->where('i.call_center_user_id', $this->agentId));
    }

    private function contactSql(string $alias = 'i'): string
    {
        $list = implode(',', array_map(fn ($r) => "'{$r}'", CallCenterOutcomes::contactResultats()));

        return "{$alias}.resultat IN ({$list})";
    }

    public function summary(): array
    {
        $row = $this->calls()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(' . $this->contactSql() . ') as joints')
            ->selectRaw("COUNT(DISTINCT CONCAT(i.cible_type, ':', i.cible_id)) as contacts")
            ->selectRaw('COUNT(DISTINCT CASE WHEN ' . $this->contactSql() . " THEN CONCAT(i.cible_type, ':', i.cible_id) END) as contacts_joints")
            ->selectRaw('COALESCE(SUM(i.duree_minutes), 0) as minutes')
            ->selectRaw('COUNT(DISTINCT i.call_center_user_id) as agents')
            ->first();

        $total = (int) $row->total;

        return [
            'total' => $total,
            'joints' => (int) $row->joints,
            'taux_joints' => $total > 0 ? round($row->joints * 100 / $total) : null,
            'contacts' => (int) $row->contacts,
            'contacts_joints' => (int) $row->contacts_joints,
            'minutes' => (int) $row->minutes,
            'agents' => (int) $row->agents,
            'resultats' => $this->countBy('resultat'),
        ];
    }

    /** Nombre d'appels par valeur d'une colonne (resultat, motif, canal, cible_type). */
    public function countBy(string $column): array
    {
        return $this->calls()
            ->groupBy("i.{$column}")
            ->selectRaw("i.{$column} as cle, COUNT(*) as total")
            ->pluck('total', 'cle')
            ->map(fn ($v) => (int) $v)
            ->sortDesc()
            ->all();
    }

    /** Appels par jour (jours sans appel inclus), pour la courbe d'activite. */
    public function daily(): array
    {
        $rows = $this->calls()
            ->groupBy(DB::raw('DATE(i.created_at)'))
            ->selectRaw('DATE(i.created_at) as jour, COUNT(*) as total, SUM(' . $this->contactSql() . ') as joints')
            ->get()
            ->keyBy('jour');

        $days = [];
        for ($day = $this->from->copy(); $day->lte($this->to) && count($days) < 370; $day->addDay()) {
            $key = $day->toDateString();
            $days[] = [
                'date' => $key,
                'label' => $day->format('d/m'),
                'total' => (int) ($rows[$key]->total ?? 0),
                'joints' => (int) ($rows[$key]->joints ?? 0),
            ];
        }

        return $days;
    }

    /** Une ligne par agent : volume, joignabilite, resultats et conversions. */
    public function byAgent(): array
    {
        $stats = $this->calls()
            ->groupBy('i.call_center_user_id')
            ->select('i.call_center_user_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(' . $this->contactSql() . ') as joints')
            ->selectRaw("COUNT(DISTINCT CONCAT(i.cible_type, ':', i.cible_id)) as contacts")
            ->selectRaw("SUM(i.resultat = 'interesse') as interesses")
            ->selectRaw("SUM(i.resultat = 'a_rappeler') as a_rappeler")
            ->selectRaw("SUM(i.resultat = 'refus') as refus")
            ->selectRaw("SUM(i.resultat = 'inscrit') as inscrits")
            ->selectRaw("SUM(i.resultat = 'converti') as convertis")
            ->selectRaw("SUM(i.resultat = 'alerte_renouvelee') as alertes_renouvelees")
            ->selectRaw("SUM(i.motif = 'relance_alerte') as relances")
            ->selectRaw('MAX(i.created_at) as dernier_appel')
            ->get()
            ->keyBy('call_center_user_id');

        $abonnements = $this->subscriptionsAfterCalls(true);
        $rappels = $this->pendingCallbacksByAgent();

        return DB::table('call_center_users')
            ->when($this->agentId, fn ($q) => $q->where('id', $this->agentId))
            ->orderBy('prenoms')
            ->get(['id', 'nom', 'prenoms', 'statut', 'role'])
            ->map(function ($agent) use ($stats, $abonnements, $rappels) {
                $s = $stats[$agent->id] ?? null;
                $total = (int) ($s->total ?? 0);

                return (object) [
                    'id' => $agent->id,
                    'nom' => trim($agent->prenoms . ' ' . $agent->nom),
                    'actif' => (bool) $agent->statut,
                    'role' => $agent->role ?? 'agent',
                    'total' => $total,
                    'joints' => (int) ($s->joints ?? 0),
                    'taux_joints' => $total > 0 ? round(($s->joints ?? 0) * 100 / $total) : null,
                    'contacts' => (int) ($s->contacts ?? 0),
                    'interesses' => (int) ($s->interesses ?? 0),
                    'a_rappeler' => (int) ($s->a_rappeler ?? 0),
                    'refus' => (int) ($s->refus ?? 0),
                    'inscrits' => (int) ($s->inscrits ?? 0),
                    'convertis' => (int) ($s->convertis ?? 0),
                    'alertes_renouvelees' => (int) ($s->alertes_renouvelees ?? 0),
                    'relances' => (int) ($s->relances ?? 0),
                    'abonnements' => (int) ($abonnements['par_agent'][$agent->id]['total'] ?? 0),
                    'abonnements_payants' => (int) ($abonnements['par_agent'][$agent->id]['payants'] ?? 0),
                    'rappels_en_attente' => (int) ($rappels[$agent->id]->en_attente ?? 0),
                    'rappels_en_retard' => (int) ($rappels[$agent->id]->en_retard ?? 0),
                    'dernier_appel' => $s->dernier_appel ?? null,
                ];
            })
            // Les agents sans appel restent visibles (inactivite), mais apres les autres.
            ->sortByDesc(fn ($row) => [$row->total, $row->actif])
            ->values()
            ->all();
    }

    /** Rappels en attente a ce jour (independant de la periode). */
    public function callbacks(): array
    {
        $row = DB::table('call_center_interactions')
            ->whereNotNull('rappel_le')
            ->whereNull('rappel_fait_at')
            ->when($this->agentId, fn ($q) => $q->where('call_center_user_id', $this->agentId))
            ->selectRaw('COUNT(*) as en_attente')
            ->selectRaw('SUM(rappel_le < ?) as en_retard', [now()->startOfDay()])
            ->selectRaw('SUM(DATE(rappel_le) = ?) as aujourdhui', [now()->toDateString()])
            ->first();

        return [
            'en_attente' => (int) $row->en_attente,
            'en_retard' => (int) $row->en_retard,
            'aujourdhui' => (int) $row->aujourdhui,
        ];
    }

    private function pendingCallbacksByAgent(): array
    {
        return DB::table('call_center_interactions')
            ->whereNotNull('rappel_le')
            ->whereNull('rappel_fait_at')
            ->groupBy('call_center_user_id')
            ->select('call_center_user_id')
            ->selectRaw('COUNT(*) as en_attente')
            ->selectRaw('SUM(rappel_le < ?) as en_retard', [now()->startOfDay()])
            ->get()
            ->keyBy('call_center_user_id')
            ->all();
    }

    /**
     * Relances d'alertes :
     *  - a venir : alertes qui expirent dans les 30 prochains jours, et part deja relancee ;
     *  - expirees sur la periode : relancees ou non, puis renouvelees (nouvelle alerte du meme type
     *    pour le meme vehicule, avec une echeance plus lointaine).
     */
    public function alerts(): array
    {
        if (! SchemaCache::hasTable('alerts')) {
            return [];
        }

        $relanceExists = function ($q) {
            $q->select(DB::raw(1))
                ->from('call_center_interactions as r')
                ->whereColumn('r.alert_id', 'alerts.id')
                ->when($this->agentId, fn ($qq) => $qq->where('r.call_center_user_id', $this->agentId));
        };

        $renewedSql = 'EXISTS (SELECT 1 FROM alerts a2 WHERE a2.vehicule_id = alerts.vehicule_id'
            . ' AND a2.type_alert_id = alerts.type_alert_id AND a2.id <> alerts.id AND a2.date_fin > alerts.date_fin)';

        $today = now()->startOfDay();

        $upcoming = DB::table('alerts')
            ->whereNotNull('alerts.user_id')
            ->whereBetween('alerts.date_fin', [$today->toDateString(), $today->copy()->addDays(self::ALERT_WINDOW_DAYS)->toDateString()])
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(alerts.date_fin <= ?) as sous_7j', [$today->copy()->addDays(7)->toDateString()])
            ->first();

        $upcomingRelancees = DB::table('alerts')
            ->whereNotNull('alerts.user_id')
            ->whereBetween('alerts.date_fin', [$today->toDateString(), $today->copy()->addDays(self::ALERT_WINDOW_DAYS)->toDateString()])
            ->whereExists($relanceExists)
            ->count();

        // Alertes arrivees a echeance pendant la periode (jusqu'a hier au plus tard).
        $expiredTo = $this->to->copy()->min($today->copy()->subDay()->endOfDay());
        $expired = (object) ['total' => 0, 'relancees' => 0, 'renouvelees' => 0, 'renouvelees_relancees' => 0];

        if ($expiredTo->gte($this->from)) {
            $expired = DB::table('alerts')
                ->whereNotNull('alerts.user_id')
                ->whereBetween('alerts.date_fin', [$this->from->toDateString(), $expiredTo->toDateString()])
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM({$renewedSql}) as renouvelees")
                ->first();

            $relancees = DB::table('alerts')
                ->whereNotNull('alerts.user_id')
                ->whereBetween('alerts.date_fin', [$this->from->toDateString(), $expiredTo->toDateString()])
                ->whereExists($relanceExists)
                ->selectRaw('COUNT(*) as relancees')
                ->selectRaw("SUM({$renewedSql}) as renouvelees_relancees")
                ->first();

            $expired->relancees = (int) $relancees->relancees;
            $expired->renouvelees_relancees = (int) $relancees->renouvelees_relancees;
        }

        $expiredTotal = (int) $expired->total;
        $expiredRelancees = (int) $expired->relancees;
        $nonRelancees = $expiredTotal - $expiredRelancees;
        $renouveleesNonRelancees = (int) $expired->renouvelees - (int) $expired->renouvelees_relancees;

        return [
            'a_venir' => (int) $upcoming->total,
            'a_venir_7j' => (int) $upcoming->sous_7j,
            'a_venir_relancees' => $upcomingRelancees,
            'couverture' => $upcoming->total > 0 ? round($upcomingRelancees * 100 / $upcoming->total) : null,
            'expirees' => $expiredTotal,
            'expirees_relancees' => $expiredRelancees,
            'expirees_sans_relance' => $nonRelancees,
            'renouvelees_relancees' => (int) $expired->renouvelees_relancees,
            'taux_renouvellement_relancees' => $expiredRelancees > 0 ? round($expired->renouvelees_relancees * 100 / $expiredRelancees) : null,
            'taux_renouvellement_non_relancees' => $nonRelancees > 0 ? round($renouveleesNonRelancees * 100 / $nonRelancees) : null,
            'relances_periode' => (clone $this->calls())->where('i.motif', 'relance_alerte')->count(),
            'par_type' => $this->alertRelancesByType(),
        ];
    }

    private function alertRelancesByType(): array
    {
        return $this->calls()
            ->join('alerts', 'alerts.id', '=', 'i.alert_id')
            ->leftJoin('type_alerts', 'type_alerts.id', '=', 'alerts.type_alert_id')
            ->groupBy('type_alerts.libelle')
            ->selectRaw("COALESCE(type_alerts.libelle, 'Autre') as type, COUNT(*) as relances")
            ->selectRaw('SUM(' . $this->contactSql() . ') as joints')
            ->selectRaw("SUM(i.resultat = 'alerte_renouvelee') as renouvelees")
            ->orderByDesc('relances')
            ->get()
            ->all();
    }

    /**
     * Entonnoir d'acquisition sur les contacts appeles pendant la periode.
     * "Abonnements apres appel" est calcule (souscription dans les 30 jours suivant le 1er appel),
     * "inscrits" / "convertis" sont declares par l'agent.
     */
    public function acquisition(): array
    {
        $distinct = fn (?string $resultat = null, bool $contact = false) => (int) $this->calls()
            ->when($resultat, fn ($q) => $q->where('i.resultat', $resultat))
            ->when($contact, fn ($q) => $q->whereRaw($this->contactSql()))
            ->selectRaw("COUNT(DISTINCT CONCAT(i.cible_type, ':', i.cible_id)) as n")
            ->value('n');

        $abonnements = $this->subscriptionsAfterCalls();

        return [
            'appeles' => $distinct(),
            'joints' => $distinct(null, true),
            'interesses' => $distinct('interesse'),
            'inscrits' => $distinct('inscrit'),
            'convertis' => $distinct('converti'),
            'abonnements' => $abonnements['total'],
            'abonnements_payants' => $abonnements['payants'],
            'abonnements_usagers' => $abonnements['usagers'],
            'abonnements_pros' => $abonnements['pros'],
        ];
    }

    /**
     * Contacts ayant souscrit un abonnement dans les 30 jours apres leur premier appel de la periode.
     * Usagers : abonnement_usagers (is_free = 0 => payant). Pros / etablissements : abonnement_pros.
     */
    private function subscriptionsAfterCalls(bool $withAgents = false): array
    {
        $result = ['total' => 0, 'payants' => 0, 'usagers' => 0, 'pros' => 0, 'par_agent' => []];

        $firstCalls = fn (array $types) => $this->calls()
            ->whereIn('i.cible_type', $types)
            ->groupBy('i.cible_type', 'i.cible_id', 'i.call_center_user_id')
            ->select('i.cible_type', 'i.cible_id', 'i.call_center_user_id')
            ->selectRaw('MIN(i.created_at) as premier_appel');

        $add = function ($rows, string $bucket) use (&$result) {
            $seen = [];
            foreach ($rows as $row) {
                $key = $row->cible_type . ':' . $row->cible_id;
                $result['par_agent'][$row->call_center_user_id]['total'] = ($result['par_agent'][$row->call_center_user_id]['total'] ?? 0) + 1;
                $result['par_agent'][$row->call_center_user_id]['payants'] = ($result['par_agent'][$row->call_center_user_id]['payants'] ?? 0) + (int) $row->payant;
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $result['total']++;
                $result[$bucket]++;
                $result['payants'] += (int) $row->payant;
            }
        };

        $window = self::CONVERSION_DAYS;

        if (SchemaCache::hasTable('abonnement_usagers')) {
            $paidSql = SchemaCache::hasColumn('abonnement_usagers', 'is_free') ? 'MAX(au.is_free = 0)' : 'MAX(1)';
            $rows = DB::query()->fromSub($firstCalls(['users']), 'f')
                ->join('abonnement_usagers as au', function ($join) use ($window) {
                    $join->on('au.user_id', '=', 'f.cible_id')
                        ->whereColumn('au.created_at', '>=', 'f.premier_appel')
                        ->whereRaw("au.created_at <= DATE_ADD(f.premier_appel, INTERVAL {$window} DAY)");
                })
                ->groupBy('f.cible_type', 'f.cible_id', 'f.call_center_user_id')
                ->select('f.cible_type', 'f.cible_id', 'f.call_center_user_id')
                ->selectRaw("{$paidSql} as payant")
                ->get();
            $add($rows, 'usagers');
        }

        if (SchemaCache::hasTable('abonnement_pros') && SchemaCache::hasTable('etablissements')) {
            // Un pro est rattache a ses etablissements ; un etablissement l'est directement.
            $rows = DB::query()->fromSub($firstCalls(['etablissements', 'professionnels']), 'f')
                ->join('etablissements as e', function ($join) {
                    $join->on(function ($on) {
                        $on->where('f.cible_type', '=', 'etablissements')->whereColumn('e.id', 'f.cible_id');
                    })->orOn(function ($on) {
                        $on->where('f.cible_type', '=', 'professionnels')->whereColumn('e.professionnel_id', 'f.cible_id');
                    });
                })
                ->join('abonnement_pros as ap', function ($join) use ($window) {
                    $join->on('ap.etablissement_id', '=', 'e.id')
                        ->whereColumn('ap.created_at', '>=', 'f.premier_appel')
                        ->whereRaw("ap.created_at <= DATE_ADD(f.premier_appel, INTERVAL {$window} DAY)");
                })
                ->groupBy('f.cible_type', 'f.cible_id', 'f.call_center_user_id')
                ->select('f.cible_type', 'f.cible_id', 'f.call_center_user_id')
                ->selectRaw('1 as payant')
                ->get();
            $add($rows, 'pros');
        }

        if (! $withAgents) {
            unset($result['par_agent']);
        }

        return $result;
    }

    /** Lignes du journal de la periode pour l'export CSV. */
    public function exportRows(): \Generator
    {
        $query = $this->calls()
            ->leftJoin('call_center_users as a', 'a.id', '=', 'i.call_center_user_id')
            ->leftJoin('alerts', 'alerts.id', '=', 'i.alert_id')
            ->leftJoin('type_alerts', 'type_alerts.id', '=', 'alerts.type_alert_id')
            ->leftJoin('vehicules', 'vehicules.id', '=', 'alerts.vehicule_id');

        $nameSql = \App\Support\CallCenterCallLog::joinContactNames($query);

        $query->orderBy('i.id')->select([
            'i.*',
            DB::raw("{$nameSql} as cible_nom"),
            DB::raw("TRIM(CONCAT(COALESCE(a.prenoms, ''), ' ', COALESCE(a.nom, ''))) as agent"),
            'type_alerts.libelle as type_alerte',
            'vehicules.matricule as vehicule',
        ]);

        foreach ($query->cursor() as $row) {
            yield $row;
        }
    }
}
