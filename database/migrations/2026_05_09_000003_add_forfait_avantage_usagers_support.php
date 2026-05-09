<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('forfait_avantage_usagers')) {
            Schema::create('forfait_avantage_usagers', function (Blueprint $table) {
                $table->id();
                $table->text('avantages');
                $table->boolean('available')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasColumn('forfait_usagers', 'forfait_avantage_usager_id')) {
            Schema::table('forfait_usagers', function (Blueprint $table) {
                $table->unsignedBigInteger('forfait_avantage_usager_id')->nullable()->after('statut');

                $table->foreign('forfait_avantage_usager_id', 'fu_avantage_usager_fk')
                    ->references('id')
                    ->on('forfait_avantage_usagers')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('forfait_usagers', 'forfait_avantage_usager_id')) {
            Schema::table('forfait_usagers', function (Blueprint $table) {
                $table->dropForeign('fu_avantage_usager_fk');
                $table->dropColumn('forfait_avantage_usager_id');
            });
        }
    }
};
