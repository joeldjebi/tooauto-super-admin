<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'professionnels',
        'etablissements',
        'concessionnaires',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'call_center_deja_appele')) {
                    $column = $table->boolean('call_center_deja_appele')->default(false);

                    if (Schema::hasColumn($tableName, 'statut')) {
                        $column->after('statut');
                    }
                }

                if (! Schema::hasColumn($tableName, 'call_center_commentaire')) {
                    $column = $table->text('call_center_commentaire')->nullable();

                    if (Schema::hasColumn($tableName, 'call_center_deja_appele')) {
                        $column->after('call_center_deja_appele');
                    }
                }

                if (! Schema::hasColumn($tableName, 'call_center_called_at')) {
                    $column = $table->timestamp('call_center_called_at')->nullable();

                    if (Schema::hasColumn($tableName, 'call_center_commentaire')) {
                        $column->after('call_center_commentaire');
                    }
                }
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                foreach (['call_center_called_at', 'call_center_commentaire', 'call_center_deja_appele'] as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
