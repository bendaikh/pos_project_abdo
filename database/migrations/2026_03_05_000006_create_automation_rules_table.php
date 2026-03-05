<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "Commander farine", "Préparer production", etc.
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Condition type
            $table->enum('condition_type', [
                'stock_level',      // Stock article < minimum
                'sales_threshold',  // Ventes article > X
                'production_event', // Production terminée
                'time_based',       // Fin de journée
                'custom'            // Custom condition
            ])->default('stock_level');
            
            // Condition details (JSON for flexibility)
            $table->json('condition_data')->nullable();
            
            // Action: Create task
            $table->string('task_subject'); // "Commander farine"
            $table->text('task_description')->nullable();
            $table->enum('task_priority', ['faible', 'moyenne', 'urgente'])->default('moyenne');
            
            // Assigned to (employee_id or role-based)
            $table->foreignId('assigned_to_employee_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->string('assigned_to_role')->nullable(); // 'manager', 'responsable_achat', etc.
            
            // Execution settings
            $table->boolean('is_repeatable')->default(false);
            $table->string('repeat_interval')->nullable(); // 'daily', 'weekly', 'monthly'
            $table->dateTime('last_triggered_at')->nullable();
            $table->integer('execution_count')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_rules');
    }
};
