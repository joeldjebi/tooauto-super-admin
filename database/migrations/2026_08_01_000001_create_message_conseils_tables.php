<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_conseils', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('image_url', 500)->nullable();
            $table->string('action_url', 500)->nullable();
            $table->json('filters')->nullable();
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->timestamp('sent_at')->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->unsignedInteger('total_targets')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('failure_count')->default(0);
            $table->text('last_error')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('message_conseil_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_conseil_id')->constrained('message_conseils')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('fcm_token', 600)->nullable();
            $table->string('status', 30)->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_conseil_logs');
        Schema::dropIfExists('message_conseils');
    }
};
