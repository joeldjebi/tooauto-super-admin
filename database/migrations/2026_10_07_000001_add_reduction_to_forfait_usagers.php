<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('forfait_usagers', 'reduction_type')) {
            Schema::table('forfait_usagers', function (Blueprint $table) {
                $table->string('reduction_type', 20)->default('fixed')->after('prix');
            });
        }

        if (!Schema::hasColumn('forfait_usagers', 'reduction')) {
            Schema::table('forfait_usagers', function (Blueprint $table) {
                $table->decimal('reduction', 12, 2)->default(0)->after('reduction_type');
            });
        }

        if (!Schema::hasColumn('forfait_usagers', 'montant_apres_reduction')) {
            Schema::table('forfait_usagers', function (Blueprint $table) {
                $table->decimal('montant_apres_reduction', 12, 2)->default(0)->after('reduction');
            });
        }

        DB::table('forfait_usagers')->update([
            'montant_apres_reduction' => DB::raw('prix'),
        ]);
    }

    public function down(): void
    {
        Schema::table('forfait_usagers', function (Blueprint $table) {
            $table->dropColumn(['reduction_type', 'reduction', 'montant_apres_reduction']);
        });
    }
};
