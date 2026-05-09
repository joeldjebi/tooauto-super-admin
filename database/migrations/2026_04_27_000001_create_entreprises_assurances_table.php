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
        Schema::create('entreprises_assurances', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 150);
            $table->string('logo')->nullable();
            $table->string('situation_geographique', 255)->nullable();
            $table->string('lien_map', 500)->nullable();
            $table->string('site_internet', 255)->nullable();
            $table->json('telephones')->nullable();
            $table->timestamps();

            $table->unique('nom');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entreprises_assurances');
    }
};
