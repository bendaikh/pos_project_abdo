<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if (
            $driver === 'mysql'
            && Schema::hasTable('payments')
            && Schema::hasColumn('payments', 'payment_type')
        ) {
            DB::statement("ALTER TABLE payments MODIFY payment_type ENUM('cash','card','check','cheque','virement','credit','mobile','other') NOT NULL DEFAULT 'cash'");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if (
            $driver === 'mysql'
            && Schema::hasTable('payments')
            && Schema::hasColumn('payments', 'payment_type')
        ) {
            DB::statement("ALTER TABLE payments MODIFY payment_type ENUM('cash','card','check','mobile','other') NOT NULL DEFAULT 'cash'");
        }
    }
};
