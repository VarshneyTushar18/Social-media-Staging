<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'onboarding_plan_chosen_at')) {
                $table->timestamp('onboarding_plan_chosen_at')->nullable()->after('plan_expires_at');
            }
        });

        if (Schema::hasColumn('users', 'onboarding_plan_chosen_at')) {
            DB::table('users')
                ->whereNotNull('plan_id')
                ->whereNull('onboarding_plan_chosen_at')
                ->update([
                    'onboarding_plan_chosen_at' => DB::raw('COALESCE(plan_started_at, created_at)'),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'onboarding_plan_chosen_at')) {
                $table->dropColumn('onboarding_plan_chosen_at');
            }
        });
    }
};
