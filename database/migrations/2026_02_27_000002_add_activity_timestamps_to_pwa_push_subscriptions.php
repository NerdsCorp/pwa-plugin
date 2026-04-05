<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Throwable;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pwa_push_subscriptions')) {
            return;
        }

        Schema::table('pwa_push_subscriptions', function (Blueprint $table): void {
            if (!Schema::hasColumn('pwa_push_subscriptions', 'last_synced_at')) {
                $table->timestamp('last_synced_at')->nullable()->after('user_agent');
                $table->index('last_synced_at', 'pwa_push_subscriptions_last_synced_at_index');
            }

            if (!Schema::hasColumn('pwa_push_subscriptions', 'last_push_sent_at')) {
                $table->timestamp('last_push_sent_at')->nullable()->after('last_synced_at');
                $table->index('last_push_sent_at', 'pwa_push_subscriptions_last_push_sent_at_index');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('pwa_push_subscriptions')) {
            return;
        }

        Schema::table('pwa_push_subscriptions', function (Blueprint $table): void {
            if (Schema::hasColumn('pwa_push_subscriptions', 'last_push_sent_at')) {
                try {
                    $table->dropIndex('pwa_push_subscriptions_last_push_sent_at_index');
                } catch (Throwable) {
                    // Ignore if the index was never created.
                }
                $table->dropColumn('last_push_sent_at');
            }

            if (Schema::hasColumn('pwa_push_subscriptions', 'last_synced_at')) {
                try {
                    $table->dropIndex('pwa_push_subscriptions_last_synced_at_index');
                } catch (Throwable) {
                    // Ignore if the index was never created.
                }
                $table->dropColumn('last_synced_at');
            }
        });
    }
};
