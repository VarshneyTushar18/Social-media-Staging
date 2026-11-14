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
            $table->string('custom_domain', 253)->nullable()->after('subdomain_slug');
            $table->timestamp('custom_domain_connected_at')->nullable()->after('custom_domain');
            $table->unique('custom_domain');
        });
    }

    public function down(): void
    {
        Schema::table('website_sites', function (Blueprint $table): void {
            $table->dropUnique(['custom_domain']);
            $table->dropColumn(['custom_domain', 'custom_domain_connected_at']);
        });
    }
};
