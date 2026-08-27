<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pwa_notification_preferences')) {
            return;
        }

        Schema::create('pwa_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->string('channel')->default('other');
            $table->boolean('enabled')->default(true);
            $table->string('digest_mode')->default('instant');
            $table->boolean('quiet_hours_enabled')->default(false);
            $table->string('quiet_hours_start')->nullable()->default('22:00');
            $table->string('quiet_hours_end')->nullable()->default('07:00');
            $table->unsignedInteger('max_per_day')->default(10);
            $table->unsignedInteger('sent_count_24h')->default(0);
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamp('last_digest_sent_at')->nullable();
            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id', 'channel'], 'pwa_notification_preferences_scope_index');
            $table->unique(['notifiable_type', 'notifiable_id', 'channel'], 'pwa_notification_preferences_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pwa_notification_preferences');
    }
};
