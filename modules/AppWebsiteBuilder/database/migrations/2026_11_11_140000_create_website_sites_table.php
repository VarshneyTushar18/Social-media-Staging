<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_sites', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('owner_user_id');
            $table->unsignedBigInteger('agency_project_id')->nullable();
            $table->string('name', 255);
            $table->string('site_type', 120)->nullable();
            $table->string('build_method', 40)->default('scratch');
            $table->string('status', 40)->default('draft');
            $table->json('generated_content')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('owner_user_id');
            $table->index('agency_project_id');
            $table->index(['agency_project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_sites');
    }
};
