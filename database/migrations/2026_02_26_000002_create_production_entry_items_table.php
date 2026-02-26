<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_entry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_entry_id')->constrained()->onDelete('cascade');
            $table->foreignId('article_id')->constrained('articles')->onDelete('restrict');
            $table->decimal('quantity', 10, 3);
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_entry_items');
    }
};
