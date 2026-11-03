<?php

namespace App\Http\Controllers;

use App\Models\CallCenterInteraction;
use App\Services\CallCenterReportService;
use App\Support\CallCenterCallLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Espace personnel de l'agent : « Mon activite » (ses chiffres) et « Mes rappels » (sa liste du jour).
 */
class CallCenterMySpaceController extends Controller
{
    public function activity(Request $request)
    {
        $agent = Auth::guard('call_center')->user();
        [$from, $to, $period] = CallCenterReportService::resolvePeriod($request, '7j');

        $data = [
            'title' => 'Mon activite',
            'menu' => 'call-center-mon-activite',
            'user' => $agent,
            'periods' => CallCenterReportService::PERIODS,
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'available' => CallCenterReportService::available(),
        ];

        if ($data['available']) {
            $report = new CallCenterReportService($from, $to, $agent->id);
            $data += [
                'summary' => $report->summary(),
                'daily' => $report->daily(),
                'acquisition' => $report->acquisition(),
                'callbacks' => $report->callbacks(),
                'byMotif' => $report->countBy('motif'),
                'quick' => $this->quickCounts($agent->id),
                'team' => $this->teamComparison($from, $to, $agent->id),
            ];
        }

        return view('call-centers.me.activity', $data);
    }

    public function callbacks(Request $request)
    {
        $agent = Auth::guard('call_center')->user();
        $isSupervisor = $agent->isSuperviseur();
        // Le superviseur peut voir les rappels de toute l'equipe (?agent=tous).
        $showAll = $isSupervisor && $request->input('agent') === 'tous';

        $groups = ['en_retard' => collect(), 'aujourdhui' => collect(), 'a_venir' => collect()];

        if (CallCenterReportService::available()) {
            $query = DB::table('call_center_interactions as i')
                ->leftJoin('call_center_users as a', 'a.id', '=', 'i.call_center_user_id')
                ->leftJoin('alerts', 'alerts.id', '=', 'i.alert_id')
                ->leftJoin('type_alerts', 'type_alerts.id', '=', 'alerts.type_alert_id')
                ->leftJoin('vehicules', 'vehicules.id', '=', 'alerts.vehicule_id')
                ->whereNotNull('i.rappel_le')
                ->whereNull('i.rappel_fait_at')
                ->when(! $showAll, fn ($q) => $q->where('i.call_center_user_id', $agent->id));

            $contact = CallCenterCallLog::joinContacts($query);

            $rows = $query
                ->orderBy('i.rappel_le')
                ->limit(500)
                ->get([
                    'i.*',
                    DB::raw("{$contact['nom']} as cible_nom"),
                    DB::raw("{$contact['mobile']} as cible_mobile"),
                    DB::raw("{$contact['indicatif']} as cible_indicatif"),
                    DB::raw("{$contact['whatsapp']} as cible_whatsapp"),
                    DB::raw("TRIM(CONCAT(COALESCE(a.prenoms, ''), ' ', COALESCE(a.nom, ''))) as agent"),
                    'type_alerts.libelle as type_alerte',
                    'vehicules.matricule as vehicule',
                    'alerts.date_fin as alerte_date_fin',
                ]);

            $todayStart = now()->startOfDay();
            $todayEnd = now()->endOfDay();

            foreach ($rows as $row) {
                $at = \Carbon\Carbon::parse($row->rappel_le);
                $key = $at->lt($todayStart) ? 'en_retard' : ($at->lte($todayEnd) ? 'aujourdhui' : 'a_venir');
                $groups[$key]->push($row);
            }
        }

        return view('call-centers.me.callbacks', [
            'title' => 'Mes rappels',
            'menu' => 'call-center-mes-rappels',
            'user' => $agent,
            'groups' => $groups,
            'isSupervisor' => $isSupervisor,
            'showAll' => $showAll,
            'available' => CallCenterReportService::available(),
        ]);
    }

    /** Rappel traite sans nouvel appel (ex. le client a rappele de lui-meme). */
    public function markCallbackDone(int $interaction)
    {
        $agent = Auth::guard('call_center')->user();
        $row = CallCenterInteraction::query()->findOrFail($interaction);

        abort_unless((int) $row->call_center_user_id === (int) $agent->id || $agent->isSuperviseur(), 403, 'Ce rappel appartient a un autre agent.');

        if ($row->rappel_le && ! $row->rappel_fait_at) {
            $row->update(['rappel_fait_at' => now()]);
        }

        return back()->with(['type' => 'alert-success', 'message' => 'Rappel marque comme traite.']);
    }

    /** Appels d'aujourd'hui, des 7 et des 30 derniers jours (tuiles du haut, independantes du filtre). */
    private function quickCounts(int $agentId): array
    {
        $row = DB::table('call_center_interactions')
            ->where('call_center_user_id', $agentId)
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('SUM(created_at >= ?) as aujourdhui', [now()->startOfDay()])
            ->selectRaw('SUM(created_at >= ?) as semaine', [now()->subDays(6)->startOfDay()])
            ->selectRaw('COUNT(*) as mois')
            ->first();

        return [
            'aujourdhui' => (int) $row->aujourdhui,
            'semaine' => (int) $row->semaine,
            'mois' => (int) $row->mois,
        ];
    }

    /** Moyenne de l'equipe sur la periode, pour se situer sans classement nominatif. */
    private function teamComparison($from, $to, int $agentId): array
    {
        $rows = DB::table('call_center_interactions')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('call_center_user_id')
            ->selectRaw('call_center_user_id, COUNT(*) as total')
            ->pluck('total', 'call_center_user_id');

        $active = $rows->count();

        return [
            'agents_actifs' => $active,
            'moyenne' => $active > 0 ? (int) round($rows->avg()) : 0,
            'moi' => (int) ($rows[$agentId] ?? 0),
        ];
    }
}
