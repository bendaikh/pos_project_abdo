<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique()->nullable(); // Article ID/Code
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('subcategory_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('sell_price', 10, 2);
            $table->decimal('buy_price', 10, 2)->nullable();
            $table->string('unit')->default('piece'); // piece, kg, lb, doz, etc.
            $table->boolean('manage_stock')->default(false);
            $table->integer('stock_quantity')->default(0);
            $table->integer('stock_alert_threshold')->default(10);
            $table->string('photo')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
