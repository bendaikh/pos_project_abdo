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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('time');
            $table->integer('duration')->default(60)->comment('Duration in minutes');
            $table->string('subject');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->foreignId('responsible_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->string('location')->nullable();
            $table->enum('location_type', ['magasin', 'sur_place', 'livraison', 'autre'])->default('magasin');
            $table->enum('status', ['en_cours', 'confirme', 'termine', 'annule'])->default('confirme');
            $table->text('notes')->nullable();
            
            // Reminder fields
            $table->boolean('reminder_enabled')->default(false);
            $table->enum('reminder_channel', ['sms', 'whatsapp', 'notification', 'email'])->nullable();
            $table->enum('reminder_timing', ['24h', '2h', '30min', 'custom'])->nullable();
            $table->integer('reminder_custom_value')->nullable();
            $table->enum('reminder_custom_unit', ['minutes', 'hours', 'days'])->nullable();
            $table->text('reminder_message')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('date');
            $table->index('status');
            $table->index(['date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
