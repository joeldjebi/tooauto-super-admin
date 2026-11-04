<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Motifs et resultats d'appel ajoutes par les agents via l'option « Autre ».
 * Ils completent les listes fixes de App\Support\CallCenterOutcomes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('call_center_options')) {
            return;
        }

        Schema::create('call_center_options', function (Blueprint $table) {
            $table->id();
            $table->string('groupe', 20); // motif | resultat
            $table->string('code', 40);
            $table->string('label', 100);
            $table->boolean('contact')->default(true); // resultat : la personne a ete jointe
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['groupe', 'code'], 'cco_groupe_code_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_center_options');
    }
};
