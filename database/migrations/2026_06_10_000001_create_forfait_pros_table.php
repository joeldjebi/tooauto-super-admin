<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('forfait_pros')) {
            Schema::create('forfait_pros', function (Blueprint $table) {
                $table->id();
                $table->string('nom', 300)->unique();
                $table->integer('duree');
                $table->integer('prix');
                $table->text('avantages');
                $table->tinyInteger('statut')->default(1);
                $table->timestamps();
            });

            DB::table('forfait_pros')->insert([
                [
                    'nom' => 'Free',
                    'duree' => 1,
                    'prix' => 0,
                    'avantages' => 'avantage 1,avantage 2',
                    'statut' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'nom' => 'Premium',
                    'duree' => 1,
                    'prix' => 4900,
                    'avantages' => 'avantage 1,avantage 2',
                    'statut' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    public function down(): void
    {
        // No destructive rollback: this table may already exist in production with real subscriptions.
    }
};
