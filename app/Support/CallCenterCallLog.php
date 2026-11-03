<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Lecture du journal des appels pour l'affichage : dernier appel de chaque contact (listes)
 * et derniers appels d'un contact (fenetre d'enregistrement), tous agents confondus.
 */
class CallCenterCallLog
{
    /**
     * Dernier appel et nombre total d'appels pour chaque contact de la page.
     *
     * @return array<int, object{agent: string, created_at: string, resultat: string, total: int}>
     */
    public static function lastCalls(string $cibleType, array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if ($ids === [] || ! SchemaCache::hasTable('call_center_interactions')) {
            return [];
        }

        $stats = DB::table('call_center_interactions')
            ->where('cible_type', $cibleType)
            ->whereIn('cible_id', $ids)
            ->groupBy('cible_id')
            ->select('cible_id', DB::raw('MAX(id) as last_id'), DB::raw('COUNT(*) as total'));

        return DB::table('call_center_interactions as i')
            ->joinSub($stats, 's', 's.last_id', '=', 'i.id')
            ->leftJoin('call_center_users as a', 'a.id', '=', 'i.call_center_user_id')
            ->select([
                'i.cible_id',
                'i.created_at',
                'i.resultat',
                's.total',
                DB::raw("TRIM(CONCAT(COALESCE(a.prenoms, ''), ' ', COALESCE(a.nom, ''))) as agent"),
            ])
            ->get()
            ->mapWithKeys(fn ($row) => [(int) $row->cible_id => (object) [
                'agent' => $row->agent !== '' ? $row->agent : 'Agent supprimé',
                'created_at' => $row->created_at,
                'resultat' => $row->resultat,
                'total' => (int) $row->total,
            ]])
            ->all();
    }

    /**
     * Joint chaque table de contact au journal (alias "i") et renvoie l'expression SQL du nom du contact.
     * Le journal melange usagers, pros, stations... : une jointure conditionnelle par type.
     */
    public static function joinContactNames(\Illuminate\Database\Query\Builder $query): string
    {
        return self::joinContacts($query)['nom'];
    }

    /**
     * Comme joinContactNames(), mais renvoie aussi le telephone :
     * ['nom' => sql, 'mobile' => sql, 'indicatif' => sql, 'whatsapp' => sql].
     */
    public static function joinContacts(\Illuminate\Database\Query\Builder $query): array
    {
        $parts = ['nom' => [], 'mobile' => [], 'indicatif' => [], 'whatsapp' => []];

        foreach (CallCenterOutcomes::CIBLES as $type => $config) {
            if (! SchemaCache::hasTable($type)) {
                continue;
            }

            $alias = 'c_' . $type;
            $query->leftJoin("{$type} as {$alias}", function ($join) use ($alias, $type) {
                $join->on("{$alias}.id", '=', 'i.cible_id')->where('i.cible_type', '=', $type);
            });
            $parts['nom'][] = "WHEN '{$type}' THEN " . preg_replace('/\\b(nom|prenoms|name)\\b/', "{$alias}.$1", $config['name']);

            foreach (['mobile', 'indicatif', 'whatsapp'] as $field) {
                $column = $config[$field] ?? null;
                if ($column && SchemaCache::hasColumn($type, $column)) {
                    $parts[$field][] = "WHEN '{$type}' THEN {$alias}.{$column}";
                }
            }
        }

        return array_map(
            fn ($cases) => $cases ? 'CASE i.cible_type ' . implode(' ', $cases) . ' END' : 'NULL',
            $parts
        );
    }

    /** Rappels d'un agent en retard ou prevus aujourd'hui (badge du menu « Mes rappels »). */
    public static function dueCallbacksCount(int $agentId): int
    {
        if (! SchemaCache::hasTable('call_center_interactions')) {
            return 0;
        }

        return DB::table('call_center_interactions')
            ->where('call_center_user_id', $agentId)
            ->whereNotNull('rappel_le')
            ->whereNull('rappel_fait_at')
            ->where('rappel_le', '<=', now()->endOfDay())
            ->count();
    }

    /** Derniers appels d'un contact, pour les afficher avant d'appeler. */
    public static function recent(string $cibleType, int $cibleId, int $limit = 5): array
    {
        if (! SchemaCache::hasTable('call_center_interactions')) {
            return [];
        }

        return DB::table('call_center_interactions as i')
            ->leftJoin('call_center_users as a', 'a.id', '=', 'i.call_center_user_id')
            ->leftJoin('alerts', 'alerts.id', '=', 'i.alert_id')
            ->leftJoin('type_alerts', 'type_alerts.id', '=', 'alerts.type_alert_id')
            ->where('i.cible_type', $cibleType)
            ->where('i.cible_id', $cibleId)
            ->orderByDesc('i.id')
            ->limit($limit)
            ->get([
                'i.created_at',
                'i.canal',
                'i.resultat',
                'i.commentaire',
                'i.rappel_le',
                'i.rappel_fait_at',
                'type_alerts.libelle as type_alerte',
                DB::raw("TRIM(CONCAT(COALESCE(a.prenoms, ''), ' ', COALESCE(a.nom, ''))) as agent"),
            ])
            ->map(fn ($row) => [
                'date' => \Carbon\Carbon::parse($row->created_at)->format('d/m/Y H:i'),
                'il_y_a' => \Carbon\Carbon::parse($row->created_at)->locale('fr')->diffForHumans(),
                'agent' => $row->agent !== '' ? $row->agent : 'Agent supprimé',
                'canal' => CallCenterOutcomes::label('canal', $row->canal),
                'resultat' => CallCenterOutcomes::label('resultat', $row->resultat),
                'tone' => CallCenterOutcomes::tone($row->resultat),
                'alerte' => $row->type_alerte,
                'commentaire' => $row->commentaire,
                'rappel' => $row->rappel_le && ! $row->rappel_fait_at ? \Carbon\Carbon::parse($row->rappel_le)->format('d/m/Y H:i') : null,
            ])
            ->all();
    }
}
