<?php

namespace App\Http\Controllers;

use App\Services\CallCenterReportService;
use App\Support\CallCenterOutcomes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Rapports du call center, reserves aux superviseurs (role cree par le super admin).
 */
class CallCenterReportController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeSupervisor();
        [$from, $to, $period, $agentId] = $this->resolveFilters($request);

        $data = [
            'title' => 'Rapports',
            'menu' => 'call-center-rapports',
            'user' => Auth::guard('call_center')->user(),
            'periods' => CallCenterReportService::PERIODS,
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'agentId' => $agentId,
            'agents' => DB::table('call_center_users')->orderBy('prenoms')->get(['id', 'nom', 'prenoms']),
            'available' => CallCenterReportService::available(),
        ];

        if ($data['available']) {
            $report = new CallCenterReportService($from, $to, $agentId);
            $data += [
                'summary' => $report->summary(),
                'daily' => $report->daily(),
                'agentsReport' => $report->byAgent(),
                'callbacks' => $report->callbacks(),
                'alerts' => $report->alerts(),
                'acquisition' => $report->acquisition(),
                'byMotif' => $report->countBy('motif'),
                'byCible' => $report->countBy('cible_type'),
                'byCanal' => $report->countBy('canal'),
            ];
        }

        return view('call-centers.reports.index', $data);
    }

    /** Export CSV (separateur ";" et BOM UTF-8 : s'ouvre directement dans Excel). */
    public function export(Request $request): StreamedResponse
    {
        $this->authorizeSupervisor();
        abort_unless(CallCenterReportService::available(), 404);
        [$from, $to, , $agentId] = $this->resolveFilters($request);

        $report = new CallCenterReportService($from, $to, $agentId);
        $filename = 'appels-call-center-' . $from->format('Y-m-d') . '-au-' . $to->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Date', 'Agent', 'Type de contact', 'Contact', 'Canal', 'Motif', 'Résultat', 'Joint', 'Alerte', 'Véhicule', 'Durée (min)', 'Rappel prévu', 'Rappel traité', 'Commentaire'], ';');

            foreach ($report->exportRows() as $row) {
                fputcsv($out, [
                    Carbon::parse($row->created_at)->format('d/m/Y H:i'),
                    $row->agent,
                    CallCenterOutcomes::label('cible', $row->cible_type),
                    trim((string) $row->cible_nom) ?: '#' . $row->cible_id,
                    CallCenterOutcomes::label('canal', $row->canal),
                    CallCenterOutcomes::label('motif', $row->motif),
                    CallCenterOutcomes::label('resultat', $row->resultat),
                    in_array($row->resultat, CallCenterOutcomes::contactResultats(), true) ? 'Oui' : 'Non',
                    $row->type_alerte,
                    $row->vehicule,
                    $row->duree_minutes,
                    $row->rappel_le ? Carbon::parse($row->rappel_le)->format('d/m/Y H:i') : '',
                    $row->rappel_fait_at ? 'Oui' : ($row->rappel_le ? 'Non' : ''),
                    $this->csvSafe((string) $row->commentaire),
                ], ';');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function authorizeSupervisor(): void
    {
        abort_unless(Auth::guard('call_center')->user()?->isSuperviseur(), 403, 'Rapports réservés aux superviseurs.');
    }

    /** @return array{0: Carbon, 1: Carbon, 2: string, 3: ?int} */
    private function resolveFilters(Request $request): array
    {
        $request->validate(['agent_id' => ['nullable', 'integer']]);
        [$from, $to, $period] = CallCenterReportService::resolvePeriod($request);

        return [$from, $to, $period, $request->filled('agent_id') ? (int) $request->input('agent_id') : null];
    }

    /** Empeche Excel d'interpreter un commentaire comme une formule. */
    private function csvSafe(string $value): string
    {
        return preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value;
    }
}
