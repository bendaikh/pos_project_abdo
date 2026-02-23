<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create variants table (article-specific variants)
        Schema::create('variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "Petit", "Moyen", "Grand"
            $table->decimal('price_impact', 10, 2)->default(0); // Additional price for this variant
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Update articles table to add has_variants field
        Schema::table('articles', function (Blueprint $table) {
            $table->boolean('has_variants')->default(false)->after('has_options');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('has_variants');
        });

        Schema::dropIfExists('variants');
    }
};
