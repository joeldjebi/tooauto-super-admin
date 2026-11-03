<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Journal des appels du call center : une ligne par appel / action d'un agent.
 * C'est la source de tous les rapports (activite, relances d'alertes, acquisition, objectifs).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('call_center_interactions')) {
            return;
        }

        Schema::create('call_center_interactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('call_center_user_id');
            $table->string('cible_type', 40);
            $table->unsignedBigInteger('cible_id');
            $table->unsignedBigInteger('alert_id')->nullable();
            $table->string('canal', 20)->default('appel');
            $table->string('motif', 40);
            $table->string('resultat', 40);
            $table->unsignedSmallInteger('duree_minutes')->nullable();
            $table->text('commentaire')->nullable();
            $table->dateTime('rappel_le')->nullable();
            $table->timestamp('rappel_fait_at')->nullable();
            $table->timestamps();

            $table->index(['call_center_user_id', 'created_at'], 'cci_agent_date_idx');
            $table->index(['cible_type', 'cible_id', 'created_at'], 'cci_cible_idx');
            $table->index('alert_id', 'cci_alert_idx');
            $table->index(['rappel_le', 'rappel_fait_at'], 'cci_rappel_idx');
            $table->index(['created_at', 'resultat'], 'cci_date_resultat_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_center_interactions');
    }
};
