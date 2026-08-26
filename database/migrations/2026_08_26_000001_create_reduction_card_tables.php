<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reduction_cards')) {
            Schema::create('reduction_cards', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('forfait_usager_id');
                $table->string('name');
                $table->enum('discount_type', ['percentage', 'fixed']);
                $table->decimal('discount_value', 12, 2);
                $table->text('description')->nullable();
                $table->boolean('statut')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->foreign('forfait_usager_id')->references('id')->on('forfait_usagers')->cascadeOnDelete();
                $table->unique('name');
                $table->index(['forfait_usager_id', 'statut']);
            });
        }

        if (!Schema::hasTable('user_reduction_cards')) {
            Schema::create('user_reduction_cards', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('reduction_card_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('abonnement_usager_id')->nullable();
                $table->unsignedBigInteger('forfait_usager_id')->nullable();
                $table->string('card_code', 50)->unique();
                $table->string('qr_code', 100)->unique();
                $table->date('date_debut')->nullable();
                $table->date('date_fin')->nullable();
                $table->boolean('statut')->default(true);
                $table->timestamps();

                $table->foreign('reduction_card_id')->references('id')->on('reduction_cards')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('abonnement_usager_id')->references('id')->on('abonnement_usagers')->nullOnDelete();
                $table->foreign('forfait_usager_id')->references('id')->on('forfait_usagers')->nullOnDelete();
                $table->unique(['reduction_card_id', 'abonnement_usager_id'], 'uniq_card_subscription');
                $table->index(['user_id', 'statut', 'date_fin']);
            });
        }

        if (!Schema::hasTable('reduction_card_histories')) {
            Schema::create('reduction_card_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_reduction_card_id');
                $table->unsignedBigInteger('reduction_card_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('abonnement_usager_id')->nullable();
                $table->unsignedBigInteger('forfait_usager_id')->nullable();
                $table->enum('discount_type', ['percentage', 'fixed']);
                $table->decimal('discount_value', 12, 2);
                $table->decimal('montant_initial', 12, 2)->default(0);
                $table->decimal('montant_reduction', 12, 2)->default(0);
                $table->decimal('montant_final', 12, 2)->default(0);
                $table->unsignedBigInteger('applied_by_id')->nullable();
                $table->enum('establishment_type', ['etablissement', 'lavage', 'station']);
                $table->unsignedBigInteger('establishment_id');
                $table->text('notes')->nullable();
                $table->timestamp('used_at')->nullable();
                $table->timestamps();

                $table->foreign('user_reduction_card_id')->references('id')->on('user_reduction_cards')->cascadeOnDelete();
                $table->foreign('reduction_card_id')->references('id')->on('reduction_cards')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('abonnement_usager_id')->references('id')->on('abonnement_usagers')->nullOnDelete();
                $table->foreign('forfait_usager_id')->references('id')->on('forfait_usagers')->nullOnDelete();
                $table->index(['establishment_type', 'establishment_id'], 'rch_establishment_idx');
                $table->index(['applied_by_id', 'used_at'], 'rch_applied_by_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reduction_card_histories');
        Schema::dropIfExists('user_reduction_cards');
        Schema::dropIfExists('reduction_cards');
    }
};
