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
        if (!Schema::hasTable('option_variants')) {
            Schema::create('option_variants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('option_id')->constrained('options')->cascadeOnDelete();
                $table->string('name');
                $table->decimal('price_impact', 10, 2)->default(0);
                $table->string('color')->nullable();
                $table->longText('image')->nullable(); // For base64 encoded images
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->index('option_id');
                $table->index('is_active');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('option_variants');
    }
};
