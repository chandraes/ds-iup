<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    public function up(): void
    {
        if (Artisan::call('customers:protect-files') !== 0) {
            throw new RuntimeException('Customer file protection failed. Rerun migration after fixing storage permissions.');
        }
    }

    public function down(): void
    {
        // Private customer files must not be made public by a schema rollback.
    }
};
