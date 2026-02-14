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

        $this->dropUniqueIndexIfExists('pwa_push_subscriptions', 'pwa_push_subscriptions_endpoint_unique');
        $this->dropUniqueIndexIfExists('pwa_push_subscriptions', 'pwa_endpoint_notifiable_unique');
        $this->dropUniqueIndexIfExists('pwa_push_subscriptions', 'endpoint_notifiable_unique');
        $this->addUniqueIndexIfMissing('pwa_push_subscriptions', 'endpoint_hash', 'pwa_push_subscriptions_endpoint_hash_unique');
    }

    public function down(): void
    {
        if (!Schema::hasTable('pwa_push_subscriptions')) {
            return;
        }

        $this->dropUniqueIndexIfExists('pwa_push_subscriptions', 'pwa_push_subscriptions_endpoint_hash_unique');

        Schema::table('pwa_push_subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('pwa_push_subscriptions', 'endpoint_hash')) {
                $table->dropColumn('endpoint_hash');
            }

            // Do not restore endpoint-based unique indexes on rollback.
            // endpoint length (2048) exceeds InnoDB key limits under utf8mb4.
        });
    }

    private function addUniqueIndexIfMissing(string $table, string $column, string $indexName): void
    {
        if ($this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($column, $indexName): void {
            $table->unique($column, $indexName);
        });
    }

    private function dropUniqueIndexIfExists(string $table, string $indexName): void
    {
        if (!$this->indexExists($table, $indexName)) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            DB::statement(sprintf('drop index "%s"', str_replace('"', '""', $indexName)));

            return;
        }

        Schema::table($table, function (Blueprint $table) use ($indexName): void {
            $table->dropUnique($indexName);
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return DB::table('sqlite_master')
                ->where('type', 'index')
                ->where('tbl_name', $table)
                ->where('name', $indexName)
                ->exists();
        }

        if ($driver === 'pgsql') {
            $result = DB::selectOne(
                'SELECT COUNT(*) AS count FROM pg_indexes WHERE schemaname = current_schema() AND tablename = ? AND indexname = ?',
                [$table, $indexName]
            );

            return ((int) ($result->count ?? 0)) > 0;
        }

        if ($driver === 'sqlsrv') {
            $result = DB::selectOne(
                'SELECT COUNT(*) AS count FROM sys.indexes WHERE name = ? AND object_id = OBJECT_ID(?)',
                [$indexName, $table]
            );

            return ((int) ($result->count ?? 0)) > 0;
        }

        // MySQL / MariaDB
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::raw('DATABASE()'))
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }
};
