<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            if (! Schema::hasColumn('stores', 'activity')) {
                $table->string('activity')->nullable()->after('name');
            }
            if (! Schema::hasColumn('stores', 'owner_name')) {
                $table->string('owner_name')->nullable()->after('owner_id');
            }
            if (! Schema::hasColumn('stores', 'payment_amount')) {
                $table->decimal('payment_amount', 12, 2)->nullable()->after('phone');
            }
            if (! Schema::hasColumn('stores', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('payment_amount');
            }
            if (! Schema::hasColumn('stores', 'due_date')) {
                $table->date('due_date')->nullable()->after('payment_method');
            }
            if (! Schema::hasColumn('stores', 'opening_date')) {
                $table->date('opening_date')->nullable()->after('due_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            foreach (['activity', 'owner_name', 'payment_amount', 'payment_method', 'due_date', 'opening_date'] as $column) {
                if (Schema::hasColumn('stores', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
