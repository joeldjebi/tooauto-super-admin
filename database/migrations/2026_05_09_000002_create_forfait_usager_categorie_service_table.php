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
        Schema::create('forfait_usager_categorie_service', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('forfait_usager_id');
            $table->unsignedBigInteger('categorie_service_id');
            $table->timestamps();

            $table->foreign('forfait_usager_id')
                ->references('id')
                ->on('forfait_usagers')
                ->onDelete('cascade');

            $table->foreign('categorie_service_id')
                ->references('id')
                ->on('categorie_services')
                ->onDelete('cascade');

            $table->unique(['forfait_usager_id', 'categorie_service_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forfait_usager_categorie_service');
    }
};
