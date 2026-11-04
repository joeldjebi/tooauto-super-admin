<?php

namespace App\Http\Controllers;

use App\Models\CallCenterInteraction;
use App\Support\CallCenterCallLog;
use App\Support\CallCenterOutcomes;
use App\Support\SchemaCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Journal des appels du call center : enregistrement d'un appel et historique d'une cible.
 */
class CallCenterInteractionController extends Controller
{
    // Meme cle que CallCenterSpaceController : invalide les compteurs des listes apres un appel.
    private const COUNT_VERSION_KEY = 'call_center.count_version';

    public function store(Request $request)
    {
        abort_unless(SchemaCache::hasTable('call_center_interactions'), 503, "Le journal des appels n'est pas encore installe (migration manquante).");

        $validated = $request->validate([
            'cible_type' => ['required', Rule::in(array_keys(CallCenterOutcomes::CIBLES))],
            'cible_id' => ['required', 'integer', 'min:1'],
            'alert_id' => ['nullable', 'integer', 'min:1'],
            'canal' => ['required', Rule::in(array_keys(CallCenterOutcomes::CANAUX))],
            'motif' => ['required', Rule::in(array_keys(CallCenterOutcomes::motifs()))],
            'motif_autre' => ['nullable', 'required_if:motif,' . CallCenterOutcomes::AUTRE, 'string', 'max:100'],
            'resultat' => ['required', Rule::in([...array_keys(CallCenterOutcomes::resultats()), CallCenterOutcomes::AUTRE])],
            'resultat_autre' => ['nullable', 'required_if:resultat,' . CallCenterOutcomes::AUTRE, 'string', 'max:100'],
            'resultat_autre_contact' => ['nullable', 'boolean'],
            'duree_minutes' => ['nullable', 'integer', 'min:0', 'max:600'],
            'commentaire' => ['nullable', 'string', 'max:2000'],
            'rappel_le' => [
                Rule::requiredIf(in_array($request->input('resultat'), CallCenterOutcomes::RESULTATS_AVEC_RAPPEL, true)),
                'nullable',
                'date',
                'after_or_equal:today',
            ],
        ], [
            'rappel_le.required' => 'Indiquez la date du rappel.',
            'rappel_le.after_or_equal' => 'La date de rappel ne peut pas être dans le passé.',
            'motif_autre.required_if' => 'Saisissez le libellé du motif.',
            'resultat_autre.required_if' => 'Saisissez le libellé du résultat.',
        ]);

        $table = $validated['cible_type'];
        $cibleId = (int) $validated['cible_id'];

        if (! SchemaCache::hasTable($table) || ! DB::table($table)->where('id', $cibleId)->exists()) {
            throw ValidationException::withMessages(['cible_id' => 'Contact introuvable.']);
        }

        $alertId = $validated['alert_id'] ?? null;
        if ($alertId) {
            $alertQuery = DB::table('alerts')->where('id', $alertId);
            if ($table === 'users') {
                $alertQuery->where('user_id', $cibleId);
            }
            if (! $alertQuery->exists()) {
                throw ValidationException::withMessages(['alert_id' => 'Alerte introuvable pour ce contact.']);
            }
        }

        $commentaire = trim((string) ($validated['commentaire'] ?? ''));

        DB::transaction(function () use (&$validated, $table, $cibleId, $alertId, $commentaire) {
            // « Autre » : le libelle saisi devient une option permanente de la liste.
            $agentId = Auth::guard('call_center')->id();
            if ($validated['motif'] === CallCenterOutcomes::AUTRE) {
                $validated['motif'] = CallCenterOutcomes::addOption('motif', $validated['motif_autre'], true, $agentId);
            }
            if ($validated['resultat'] === CallCenterOutcomes::AUTRE) {
                $validated['resultat'] = CallCenterOutcomes::addOption(
                    'resultat',
                    $validated['resultat_autre'],
                    (bool) ($validated['resultat_autre_contact'] ?? false),
                    $agentId
                );
            }

            // Un nouvel appel traite les rappels encore en attente sur ce contact.
            CallCenterInteraction::query()
                ->where('cible_type', $table)
                ->where('cible_id', $cibleId)
                ->whereNotNull('rappel_le')
                ->whereNull('rappel_fait_at')
                ->update(['rappel_fait_at' => now()]);

            CallCenterInteraction::create([
                'call_center_user_id' => Auth::guard('call_center')->id(),
                'cible_type' => $table,
                'cible_id' => $cibleId,
                'alert_id' => $alertId,
                'canal' => $validated['canal'],
                'motif' => $validated['motif'],
                'resultat' => $validated['resultat'],
                'duree_minutes' => $validated['duree_minutes'] ?? null,
                'commentaire' => $commentaire !== '' ? $commentaire : null,
                'rappel_le' => $validated['rappel_le'] ?? null,
            ]);

            // Garde a jour le suivi existant (badge "Deja appele", filtre, apercu du commentaire).
            $followUp = [];
            if (SchemaCache::hasColumn($table, 'call_center_deja_appele')) {
                $followUp['call_center_deja_appele'] = true;
            }
            if (SchemaCache::hasColumn($table, 'call_center_called_at')) {
                $followUp['call_center_called_at'] = now();
            }
            if ($commentaire !== '' && SchemaCache::hasColumn($table, 'call_center_commentaire')) {
                $followUp['call_center_commentaire'] = $commentaire;
            }
            if ($followUp !== []) {
                DB::table($table)->where('id', $cibleId)->update($followUp);
            }
        });

        Cache::forever(self::COUNT_VERSION_KEY, (int) Cache::get(self::COUNT_VERSION_KEY, 0) + 1);

        $message = 'Appel enregistré : ' . CallCenterOutcomes::label('resultat', $validated['resultat']) . '.';

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with(['type' => 'alert-success', 'message' => $message]);
    }

    /** Journal de tous les appels de l'equipe, filtrable : chaque agent voit qui a appele qui. */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'agent_id' => ['nullable', 'integer'],
            'resultat' => ['nullable', Rule::in(array_keys(CallCenterOutcomes::resultats()))],
            'cible_type' => ['nullable', Rule::in(array_keys(CallCenterOutcomes::CIBLES))],
            'motif' => ['nullable', Rule::in(array_keys(CallCenterOutcomes::motifs()))],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $interactions = null;

        if (SchemaCache::hasTable('call_center_interactions')) {
            $query = DB::table('call_center_interactions as i')
                ->leftJoin('call_center_users as a', 'a.id', '=', 'i.call_center_user_id')
                ->leftJoin('alerts', 'alerts.id', '=', 'i.alert_id')
                ->leftJoin('type_alerts', 'type_alerts.id', '=', 'alerts.type_alert_id')
                ->when($filters['agent_id'] ?? null, fn ($q, $v) => $q->where('i.call_center_user_id', $v))
                ->when($filters['resultat'] ?? null, fn ($q, $v) => $q->where('i.resultat', $v))
                ->when($filters['cible_type'] ?? null, fn ($q, $v) => $q->where('i.cible_type', $v))
                ->when($filters['motif'] ?? null, fn ($q, $v) => $q->where('i.motif', $v))
                ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->where('i.created_at', '>=', \Carbon\Carbon::parse($v)->startOfDay()))
                ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->where('i.created_at', '<=', \Carbon\Carbon::parse($v)->endOfDay()));

            $nameSql = CallCenterCallLog::joinContactNames($query);

            if (filled($filters['search'] ?? null)) {
                $term = '%' . $filters['search'] . '%';
                $query->where(function ($q) use ($nameSql, $term) {
                    $q->whereRaw("{$nameSql} LIKE ?", [$term])->orWhere('i.commentaire', 'like', $term);
                });
            }

            $interactions = $query
                ->orderByDesc('i.id')
                ->select([
                    'i.*',
                    DB::raw("{$nameSql} as cible_nom"),
                    DB::raw("TRIM(CONCAT(COALESCE(a.prenoms, ''), ' ', COALESCE(a.nom, ''))) as agent"),
                    'type_alerts.libelle as type_alerte',
                ])
                ->paginate(25)
                ->withQueryString();
        }

        return view('call-centers.interactions.index', [
            'title' => 'Journal des appels',
            'menu' => 'call-center-appels',
            'user' => Auth::guard('call_center')->user(),
            'interactions' => $interactions,
            'agents' => DB::table('call_center_users')->orderBy('prenoms')->get(['id', 'nom', 'prenoms']),
            'filters' => $filters,
        ]);
    }

    public function history(Request $request, string $cibleType, int $cibleId)
    {
        abort_unless(array_key_exists($cibleType, CallCenterOutcomes::CIBLES), 404);
        abort_unless(SchemaCache::hasTable($cibleType), 404);

        $cible = DB::table($cibleType)
            ->where('id', $cibleId)
            ->selectRaw(CallCenterOutcomes::CIBLES[$cibleType]['name'] . ' as nom, id')
            ->first();

        abort_unless($cible, 404);

        // Utilise par la fenetre "Enregistrer un appel" pour montrer les derniers appels avant de composer.
        if ($request->wantsJson()) {
            return response()->json(['appels' => CallCenterCallLog::recent($cibleType, $cibleId)]);
        }

        $interactions = SchemaCache::hasTable('call_center_interactions')
            ? DB::table('call_center_interactions as i')
                ->leftJoin('call_center_users as a', 'a.id', '=', 'i.call_center_user_id')
                ->leftJoin('alerts', 'alerts.id', '=', 'i.alert_id')
                ->leftJoin('type_alerts', 'type_alerts.id', '=', 'alerts.type_alert_id')
                ->leftJoin('vehicules', 'vehicules.id', '=', 'alerts.vehicule_id')
                ->where('i.cible_type', $cibleType)
                ->where('i.cible_id', $cibleId)
                ->orderByDesc('i.created_at')
                ->orderByDesc('i.id')
                ->select([
                    'i.*',
                    DB::raw("TRIM(CONCAT(COALESCE(a.prenoms, ''), ' ', COALESCE(a.nom, ''))) as agent"),
                    'type_alerts.libelle as type_alerte',
                    'vehicules.matricule as vehicule',
                    'alerts.date_fin as alerte_date_fin',
                ])
                ->paginate(20)
                ->withQueryString()
            : null;

        return view('call-centers.interactions.history', [
            'title' => 'Historique des appels',
            'menu' => array_search($cibleType, CallCenterOutcomes::MENU_CIBLES, true) ?: '',
            'user' => Auth::guard('call_center')->user(),
            'cibleType' => $cibleType,
            'cibleLabel' => CallCenterOutcomes::label('cible', $cibleType),
            'cible' => $cible,
            'interactions' => $interactions,
            'backUrl' => url()->previous() !== url()->current() ? url()->previous() : route('call-center.dashboard'),
        ]);
    }
}
