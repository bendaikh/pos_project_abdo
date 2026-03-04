<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->integer('month');
            $table->integer('year');
            $table->enum('week_number', ['1', '2', '3', '4', '5'])->nullable(); // For weekly payroll
            
            // A) Période de paie
            $table->date('period_start');
            $table->date('period_end');
            
            // B) Calcul base - Common fields
            $table->decimal('base_salary', 12, 2)->default(0);
            
            // Hours / Days worked (based on pay_type)
            $table->decimal('normal_hours', 10, 2)->default(0); // For hourly
            $table->decimal('overtime_hours', 10, 2)->default(0); // For hourly
            $table->integer('worked_days', false)->default(0); // For daily
            $table->integer('absent_days', false)->default(0); // For daily
            $table->decimal('worked_weeks', 8, 2)->default(0); // For weekly
            $table->decimal('extra_days', 8, 2)->default(0); // For weekly
            
            // Calculated amounts
            $table->decimal('normal_hours_amount', 12, 2)->default(0); // normal hours × rate
            $table->decimal('overtime_amount', 12, 2)->default(0); // OT hours × rate × multiplier
            $table->decimal('base_amount', 12, 2)->default(0); // Base calculation (varies by type)
            $table->decimal('absence_deduction', 12, 2)->default(0); // Absence retenue
            
            // C) Adjustments
            $table->decimal('bonus', 12, 2)->default(0);
            $table->decimal('prime', 12, 2)->default(0);
            $table->decimal('advance', 12, 2)->default(0);
            $table->decimal('retention', 12, 2)->default(0); // Retenues (casse, pénalité, etc.)
            $table->text('adjustment_notes')->nullable();
            
            // D) Totals
            $table->decimal('gross_amount', 12, 2)->default(0); // Brut
            $table->decimal('total_deductions', 12, 2)->default(0); // Total déductions
            $table->decimal('net_amount', 12, 2)->default(0); // Net à payer
            
            // E) Payment info
            $table->enum('payment_method', ['cash', 'transfer', 'check'])->default('transfer');
            $table->enum('payment_status', ['pending', 'paid', 'partially_paid'])->default('pending');
            $table->date('payment_date')->nullable();
            $table->text('comments')->nullable();
            
            $table->timestamps();
            
            $table->unique(['employee_id', 'month', 'year']);
            $table->index(['employee_id', 'year', 'month']);
            $table->index(['payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
