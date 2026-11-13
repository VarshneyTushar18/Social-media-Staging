<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_sites', function (Blueprint $table): void {
            $table->string('subdomain_slug', 63)->nullable()->unique()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('website_sites', function (Blueprint $table): void {
            $table->dropUnique(['subdomain_slug']);
            $table->dropColumn('subdomain_slug');
        });
    }
};
