<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customers') || Schema::hasColumn('customers', 'photo_url')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->longText('photo_url')->nullable()->after('country');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('customers') || !Schema::hasColumn('customers', 'photo_url')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('photo_url');
        });
    }
};
