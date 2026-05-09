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
        Schema::create('sous_categorie_services', function (Blueprint $table) {
            $table->id();
            $table->string('libelle')->unique();
            $table->string('image')->nullable();
            $table->boolean('statut')->default(true);
            $table->boolean('is_pro')->default(false);
            $table->tinyInteger('pro_or_usager')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sous_categorie_services');
    }
};
