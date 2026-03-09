<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customers') || Schema::hasColumn('customers', 'activity')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->string('activity')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('customers') || !Schema::hasColumn('customers', 'activity')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('activity');
        });
    }
};
