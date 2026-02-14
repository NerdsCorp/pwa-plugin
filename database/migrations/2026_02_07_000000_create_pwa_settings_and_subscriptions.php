<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pwa_settings')) {
            Schema::create('pwa_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->json('value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pwa_push_subscriptions')) {
            Schema::create('pwa_push_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->string('notifiable_type');
                $table->unsignedBigInteger('notifiable_id');
                $table->string('endpoint', 2048);
                $table->string('endpoint_hash', 64);
                $table->string('public_key');
                $table->string('auth_token');
                $table->string('content_encoding')->default('aesgcm');
                $table->string('user_agent')->nullable();
                $table->timestamps();

                $table->index(['notifiable_type', 'notifiable_id']);
                $table->unique(['endpoint', 'notifiable_type', 'notifiable_id'], 'pwa_endpoint_notifiable_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pwa_push_subscriptions');
        Schema::dropIfExists('pwa_settings');
    }
};
