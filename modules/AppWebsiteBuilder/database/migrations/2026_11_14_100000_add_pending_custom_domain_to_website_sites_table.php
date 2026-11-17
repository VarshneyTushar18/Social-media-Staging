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
            $table->string('pending_custom_domain', 253)->nullable()->after('custom_domain_connected_at');
            $table->string('publish_wizard_step', 32)->nullable()->after('pending_custom_domain');
        });
    }

    public function down(): void
    {
        Schema::table('website_sites', function (Blueprint $table): void {
            $table->dropColumn(['pending_custom_domain', 'publish_wizard_step']);
        });
    }
};