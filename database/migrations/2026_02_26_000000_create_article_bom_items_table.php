<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_bom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->onDelete('cascade');
            $table->foreignId('component_id')->constrained('articles')->onDelete('cascade');
            $table->decimal('quantity', 10, 3);
            $table->string('unit', 20)->nullable();
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['article_id', 'component_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_bom_items');
    }
};
