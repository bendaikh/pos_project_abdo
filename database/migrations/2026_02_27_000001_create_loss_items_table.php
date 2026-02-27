<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loss_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loss_id');
            $table->unsignedBigInteger('article_id');
            $table->string('loss_type', 30)->default('loss');
            $table->integer('quantity');
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->integer('stock_before')->default(0);
            $table->integer('stock_after')->default(0);
            $table->timestamps();

            $table->foreign('loss_id')->references('id')->on('losses')->cascadeOnDelete();
            $table->foreign('article_id')->references('id')->on('articles')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loss_items');
    }
};
