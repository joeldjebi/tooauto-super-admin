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
        Schema::table('categorie_services', function (Blueprint $table) {
            $table->unsignedBigInteger('sous_categorie_service_id')->nullable()->after('pro_or_usager');
            $table->foreign('sous_categorie_service_id')
                ->references('id')
                ->on('sous_categorie_services')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categorie_services', function (Blueprint $table) {
            $table->dropForeign(['sous_categorie_service_id']);
            $table->dropColumn('sous_categorie_service_id');
        });
    }
};
