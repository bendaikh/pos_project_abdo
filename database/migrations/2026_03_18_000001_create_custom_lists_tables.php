<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('custom_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('custom_lists')->cascadeOnDelete();
            $table->string('label');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['list_id', 'label']);
            $table->index(['list_id', 'is_active', 'sort_order']);
        });

        $listId = DB::table('custom_lists')->insertGetId([
            'name' => 'mode_de_service',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('custom_list_items')->insert([
            [
                'list_id' => $listId,
                'label' => 'Sur place',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'list_id' => $listId,
                'label' => 'Emporté',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'list_id' => $listId,
                'label' => 'Livraison',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_list_items');
        Schema::dropIfExists('custom_lists');
    }
};
