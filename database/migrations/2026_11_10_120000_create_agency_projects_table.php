<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agency_projects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('owner_user_id');
            $table->string('name', 255);
            $table->string('website', 500)->nullable();
            $table->string('industry', 120)->nullable();
            $table->text('goals')->nullable();
            $table->string('target_audience', 500)->nullable();
            $table->string('monthly_budget', 120)->nullable();
            $table->string('brand_tone', 120)->nullable();
            $table->json('enabled_agents')->nullable();
            $table->string('active_agent', 40)->default('social');
            $table->timestamp('setup_completed_at')->nullable();
            $table->timestamps();

            $table->index('owner_user_id', 'agency_projects_owner_user_id_index');
        });

        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'active_agency_project_id')) {
                $table->unsignedBigInteger('active_agency_project_id')->nullable()->after('plan_expires_at');
                $table->index('active_agency_project_id', 'users_active_agency_project_id_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'active_agency_project_id')) {
                $table->dropIndex('users_active_agency_project_id_index');
                $table->dropColumn('active_agency_project_id');
            }
        });

        Schema::dropIfExists('agency_projects');
    }
};
