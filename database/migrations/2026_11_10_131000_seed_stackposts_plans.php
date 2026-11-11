<?php

declare(strict_types=1);

use Database\Seeders\StackPostsPlanSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        (new StackPostsPlanSeeder)->run();
    }

    public function down(): void
    {
        //
    }
};
