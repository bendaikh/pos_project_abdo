<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('variants', function (Blueprint $table) {
            $table->decimal('cost_price', 10, 2)->default(0)->after('price_impact');
            $table->string('sku', 100)->nullable()->after('cost_price');
            $table->string('barcode', 100)->nullable()->after('sku');
            $table->string('template_name', 100)->nullable()->after('barcode');
            $table->string('template_value', 100)->nullable()->after('template_name');
        });
    }

    public function down(): void
    {
        Schema::table('variants', function (Blueprint $table) {
            $table->dropColumn(['cost_price', 'sku', 'barcode', 'template_name', 'template_value']);
        });
    }
};
