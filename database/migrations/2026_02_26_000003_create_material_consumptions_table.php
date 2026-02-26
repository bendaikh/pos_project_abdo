<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_consumptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_entry_id')->nullable()->constrained('production_entries')->onDelete('set null');
            $table->foreignId('produced_article_id')->nullable()->constrained('articles')->onDelete('set null');
            $table->foreignId('article_id')->constrained('articles')->onDelete('cascade');
            $table->enum('reason', ['production', 'loss', 'adjustment'])->default('production');
            $table->decimal('quantity', 10, 3);
            $table->string('unit', 20)->nullable();
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->integer('stock_before')->default(0);
            $table->integer('stock_after')->default(0);
            $table->dateTime('consumed_at');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->unsignedBigInteger('store_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_consumptions');
    }
};
