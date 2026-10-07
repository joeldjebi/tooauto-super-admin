<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('forfait_pros', 'reduction_type')) {
            Schema::table('forfait_pros', function (Blueprint $table) {
                $table->string('reduction_type', 20)->default('fixed')->after('prix');
            });
        }

        if (!Schema::hasColumn('forfait_pros', 'reduction')) {
            Schema::table('forfait_pros', function (Blueprint $table) {
                $table->decimal('reduction', 12, 2)->default(0)->after('reduction_type');
            });
        }

        if (!Schema::hasColumn('forfait_pros', 'montant_apres_reduction')) {
            Schema::table('forfait_pros', function (Blueprint $table) {
                $table->decimal('montant_apres_reduction', 12, 2)->default(0)->after('reduction');
            });
        }

        DB::table('forfait_pros')->update([
            'montant_apres_reduction' => DB::raw('prix'),
        ]);

        if (!Schema::hasTable('forfait_update_histories')) {
            Schema::create('forfait_update_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('forfait_id');
                $table->string('type_forfait', 20);
                $table->json('old_values');
                $table->json('new_values');
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->index(['type_forfait', 'forfait_id'], 'fuh_type_forfait_idx');
                $table->index('updated_by', 'fuh_updated_by_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('forfait_update_histories');

        Schema::table('forfait_pros', function (Blueprint $table) {
            $table->dropColumn(['reduction_type', 'reduction', 'montant_apres_reduction']);
        });
    }
};
