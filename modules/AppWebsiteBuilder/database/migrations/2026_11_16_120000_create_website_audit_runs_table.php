<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_audit_runs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('owner_user_id');
            $table->unsignedBigInteger('agency_project_id')->nullable();
            $table->json('result');
            $table->timestamps();

            $table->index('owner_user_id');
            $table->index(['owner_user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_audit_runs');
    }
};