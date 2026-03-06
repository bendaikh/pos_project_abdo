<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add columns to payments table if they don't exist
        if (!Schema::hasColumn('payments', 'is_deferred')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->boolean('is_deferred')->default(false)->after('payment_status');
                $table->string('collection_status')->default('pending')->after('is_deferred'); // pending, collected, cancelled
                $table->dateTime('collected_at')->nullable()->after('collection_status');
                $table->string('collected_by')->nullable()->after('collected_at');
                $table->text('collection_notes')->nullable()->after('collected_by');
            });
        }

        // Create payment collections table for tracking
        Schema::create('payment_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('action', ['created', 'scheduled', 'collected', 'partially_collected', 'failed', 'cancelled', 'rescheduled']);
            $table->decimal('amount', 10, 2);
            $table->date('scheduled_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Create payment reminders table
        Schema::create('payment_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            $table->date('reminder_date');
            $table->enum('status', ['pending', 'sent', 'dismissed'])->default('pending');
            $table->integer('days_before')->default(1); // 1 day before due date
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_reminders');
        Schema::dropIfExists('payment_collections');
        
        if (Schema::hasColumn('payments', 'is_deferred')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn(['is_deferred', 'collection_status', 'collected_at', 'collected_by', 'collection_notes']);
            });
        }
    }
};
