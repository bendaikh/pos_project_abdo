<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'service_mode')) {
                $table->string('service_mode')->nullable()->after('delivery_mode');
                $table->index('service_mode');
            }
        });

        DB::table('sales')
            ->whereNull('service_mode')
            ->update([
                'service_mode' => DB::raw("
                    CASE delivery_mode
                        WHEN 'dine_in' THEN 'Sur place'
                        WHEN 'delivery' THEN 'Livraison'
                        ELSE 'Emporté'
                    END
                "),
            ]);
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'service_mode')) {
                $table->dropIndex(['service_mode']);
                $table->dropColumn('service_mode');
            }
        });
    }
};
