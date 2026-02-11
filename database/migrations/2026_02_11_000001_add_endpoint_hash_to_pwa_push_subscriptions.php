<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pwa_push_subscriptions')) {
            return;
        }

        Schema::table('pwa_push_subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('pwa_push_subscriptions', 'endpoint_hash')) {
                $table->string('endpoint_hash', 64)->nullable()->after('endpoint');
            }
        });

        DB::table('pwa_push_subscriptions')
            ->select(['id', 'endpoint'])
            ->whereNotNull('endpoint')
            ->where(function ($query) {
                $query->whereNull('endpoint_hash')->orWhere('endpoint_hash', '');
            })
            ->orderBy('id')
            ->chunkById(500, function ($subscriptions): void {
                foreach ($subscriptions as $subscription) {
                    DB::table('pwa_push_subscriptions')
                        ->where('id', $subscription->id)
                        ->update(['endpoint_hash' => hash('sha256', (string) $subscription->endpoint)]);
                }
            });

        Schema::table('pwa_push_subscriptions', function (Blueprint $table) {
            try {
                $table->dropUnique('pwa_push_subscriptions_endpoint_unique');
            } catch (Throwable) {
                // Ignore when the old unique index does not exist.
            }

            try {
                $table->dropUnique('pwa_push_subscriptions_endpoint_notifiable_type_notifiable_id_unique');
            } catch (Throwable) {
                // Ignore when the old composite unique index does not exist.
            }

            try {
                $table->dropUnique('endpoint_notifiable_unique');
            } catch (Throwable) {
                // Ignore when legacy custom index names are not present.
            }

            try {
                $table->unique('endpoint_hash');
            } catch (Throwable) {
                // Ignore when the unique index already exists.
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('pwa_push_subscriptions')) {
            return;
        }

        Schema::table('pwa_push_subscriptions', function (Blueprint $table) {
            try {
                $table->dropUnique('pwa_push_subscriptions_endpoint_hash_unique');
            } catch (Throwable) {
                // Ignore when the index does not exist.
            }

            if (Schema::hasColumn('pwa_push_subscriptions', 'endpoint_hash')) {
                $table->dropColumn('endpoint_hash');
            }

            // Do not restore endpoint-based unique indexes on rollback.
            // endpoint length (2048) exceeds InnoDB key limits under utf8mb4.
        });
    }
};
