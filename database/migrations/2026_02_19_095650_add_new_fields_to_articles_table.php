<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('color')->nullable()->after('photo');
            $table->string('price_type')->default('fixed')->after('sell_price'); // 'fixed' or 'variable'
            $table->boolean('is_composite')->default(false)->after('is_on_sale');
            $table->boolean('has_tax')->default(true)->after('is_composite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['color', 'price_type', 'is_composite', 'has_tax']);
        });
    }
};
