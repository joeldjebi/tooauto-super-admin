<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Index pour les ecrans du call center sur de gros volumes :
     * - alerts.date_fin : compteurs du dashboard, filtres d'echeance, users a rappeler ;
     * - notification_campaign_logs (sent_at, created_at) : tri de la page des logs, la table qui grossit le plus vite.
     */
    private array $indexes = [
        'alerts' => ['alerts_date_fin_index' => ['date_fin']],
        'notification_campaign_logs' => ['ncl_sent_at_created_at_index' => ['sent_at', 'created_at']],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $tableName => $indexes) {
            foreach ($indexes as $indexName => $columns) {
                if (! $this->canIndex($tableName, $columns) || $this->hasIndex($tableName, $indexName)) {
                    continue;
                }

                Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
                    $table->index($columns, $indexName);
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $tableName => $indexes) {
            foreach (array_keys($indexes) as $indexName) {
                if (! Schema::hasTable($tableName) || ! $this->hasIndex($tableName, $indexName)) {
                    continue;
                }

                Schema::table($tableName, function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
            }
        }
    }

    private function canIndex(string $tableName, array $columns): bool
    {
        if (! Schema::hasTable($tableName)) {
            return false;
        }

        foreach ($columns as $column) {
            if (! Schema::hasColumn($tableName, $column)) {
                return false;
            }
        }

        return true;
    }

    private function hasIndex(string $tableName, string $indexName): bool
    {
        return DB::select("SHOW INDEX FROM `{$tableName}` WHERE Key_name = ?", [$indexName]) !== [];
    }
};
