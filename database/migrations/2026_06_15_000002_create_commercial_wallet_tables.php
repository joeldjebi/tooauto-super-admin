<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('commercial_commission_settings')) {
            Schema::create('commercial_commission_settings', function (Blueprint $table) {
                $table->id();
                $table->string('type', 20)->default('fixed');
                $table->decimal('value', 15, 2)->default(0);
                $table->tinyInteger('statut')->default(1);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('commercial_wallets')) {
            Schema::create('commercial_wallets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('commercial_id')->unique();
                $table->decimal('balance', 15, 2)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('commercial_wallet_transactions')) {
            Schema::create('commercial_wallet_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('commercial_wallet_id')->nullable();
                $table->unsignedBigInteger('commercial_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('abonnement_usager_id')->nullable();
                $table->unsignedBigInteger('forfait_id')->nullable();
                $table->string('direction', 20);
                $table->string('type', 50);
                $table->decimal('amount', 15, 2);
                $table->decimal('balance_before', 15, 2)->default(0);
                $table->decimal('balance_after', 15, 2)->default(0);
                $table->string('commission_type', 20)->nullable();
                $table->decimal('commission_value', 15, 2)->nullable();
                $table->string('reference')->nullable();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->index(['commercial_id', 'created_at']);
                $table->unique(['abonnement_usager_id', 'type'], 'uniq_wallet_subscription_type');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('commercial_wallet_transactions');
        Schema::dropIfExists('commercial_wallets');
        Schema::dropIfExists('commercial_commission_settings');
    }
};
