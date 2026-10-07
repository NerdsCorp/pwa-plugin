<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pwa_push_subscriptions') || Schema::hasColumn('pwa_push_subscriptions', 'device_name')) {
            return;
        }

        Schema::table('pwa_push_subscriptions', function (Blueprint $table): void {
            $table->string('device_name', 100)->nullable();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('pwa_push_subscriptions') && Schema::hasColumn('pwa_push_subscriptions', 'device_name')) {
            Schema::table('pwa_push_subscriptions', function (Blueprint $table): void {
                $table->dropColumn('device_name');
            });
        }
    }
};
