<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Taille", "Sauce"
            $table->enum('type', ['fixed', 'multiple'])->default('fixed'); // fixed = single choice, multiple = multi-select
            $table->json('values'); // e.g., ["S", "M", "L"] or ["sauce haute", "sauce bigui"]
            $table->decimal('extra_price', 10, 2)->default(0); // additional price for option
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('options');
    }
};
