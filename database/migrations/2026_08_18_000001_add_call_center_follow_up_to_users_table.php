<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'call_center_deja_appele')) {
                $table->boolean('call_center_deja_appele')->default(false)->after('statut');
            }

            if (! Schema::hasColumn('users', 'call_center_commentaire')) {
                $table->text('call_center_commentaire')->nullable()->after('call_center_deja_appele');
            }

            if (! Schema::hasColumn('users', 'call_center_called_at')) {
                $table->timestamp('call_center_called_at')->nullable()->after('call_center_commentaire');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'call_center_called_at')) {
                $table->dropColumn('call_center_called_at');
            }

            if (Schema::hasColumn('users', 'call_center_commentaire')) {
                $table->dropColumn('call_center_commentaire');
            }

            if (Schema::hasColumn('users', 'call_center_deja_appele')) {
                $table->dropColumn('call_center_deja_appele');
            }
        });
    }
};
