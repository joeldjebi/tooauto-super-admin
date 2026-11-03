<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Verifications de schema mises en cache. Chaque Schema::hasColumn() coute une requete information_schema ;
 * les ecrans call center et notifications en faisaient plusieurs dizaines par affichage.
 * Apres une migration, `php artisan cache:clear` applique immediatement les nouvelles colonnes.
 */
class SchemaCache
{
    private const PREFIX = 'call_center.schema.';
    private const SECONDS = 600;

    private static array $columns = [];

    public static function columns(string $table): ?array
    {
        if (! array_key_exists($table, self::$columns)) {
            self::$columns[$table] = Cache::remember(
                self::PREFIX . $table,
                self::SECONDS,
                fn () => Schema::hasTable($table) ? Schema::getColumnListing($table) : null
            );
        }

        return self::$columns[$table];
    }

    public static function hasTable(string $table): bool
    {
        return self::columns($table) !== null;
    }

    public static function hasColumn(string $table, string $column): bool
    {
        return in_array($column, self::columns($table) ?? [], true);
    }
}
