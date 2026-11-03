<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('call_center_users') || Schema::hasColumn('call_center_users', 'role')) {
            return;
        }

        Schema::table('call_center_users', function (Blueprint $table) {
            // agent | superviseur : le superviseur est cree par le super admin et accede aux rapports de l'equipe.
            $table->string('role', 20)->default('agent')->after('email');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('call_center_users') && Schema::hasColumn('call_center_users', 'role')) {
            Schema::table('call_center_users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }
};
