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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->date('due_date');
            $table->time('due_time')->nullable();
            $table->string('subject');
            $table->text('description')->nullable();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->enum('priority', ['faible', 'moyenne', 'urgente'])->default('moyenne');
            $table->enum('status', ['en_attente', 'en_cours', 'termine', 'annule'])->default('en_attente');
            $table->json('attachments')->nullable();
            
            // Reminder fields
            $table->boolean('reminder_enabled')->default(false);
            $table->enum('reminder_channel', ['notification', 'sms', 'whatsapp'])->nullable();
            $table->enum('reminder_timing', ['at_time', '1h', '30min', 'custom'])->nullable();
            $table->integer('reminder_custom_value')->nullable();
            $table->enum('reminder_custom_unit', ['minutes', 'hours', 'days'])->nullable();
            $table->boolean('reminder_repeat_until_validation')->default(false);
            $table->integer('reminder_repeat_interval')->nullable()->comment('Interval in minutes');
            $table->timestamp('reminder_sent_at')->nullable();
            
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('due_date');
            $table->index('status');
            $table->index('priority');
            $table->index(['due_date', 'status']);
            $table->index(['employee_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
