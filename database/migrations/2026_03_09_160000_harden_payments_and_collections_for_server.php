<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payments')) {
            return;
        }

        $hasIsDeferred = Schema::hasColumn('payments', 'is_deferred');
        $hasCollectionStatus = Schema::hasColumn('payments', 'collection_status');
        $hasCollectedAt = Schema::hasColumn('payments', 'collected_at');
        $hasCollectedBy = Schema::hasColumn('payments', 'collected_by');
        $hasCollectionNotes = Schema::hasColumn('payments', 'collection_notes');

        if (!$hasIsDeferred || !$hasCollectionStatus || !$hasCollectedAt || !$hasCollectedBy || !$hasCollectionNotes) {
            Schema::table('payments', function (Blueprint $table) use (
                $hasIsDeferred,
                $hasCollectionStatus,
                $hasCollectedAt,
                $hasCollectedBy,
                $hasCollectionNotes
            ) {
                if (!$hasIsDeferred) {
                    $table->boolean('is_deferred')->default(false);
                }
                if (!$hasCollectionStatus) {
                    $table->string('collection_status')->default('pending');
                }
                if (!$hasCollectedAt) {
                    $table->dateTime('collected_at')->nullable();
                }
                if (!$hasCollectedBy) {
                    $table->string('collected_by')->nullable();
                }
                if (!$hasCollectionNotes) {
                    $table->text('collection_notes')->nullable();
                }
            });
        }

        DB::statement("UPDATE payments SET is_deferred = 0 WHERE is_deferred IS NULL");
        DB::statement("
            UPDATE payments
            SET collection_status = CASE
                WHEN COALESCE(is_deferred, 0) = 1 THEN 'pending'
                ELSE 'collected'
            END
            WHERE collection_status IS NULL OR TRIM(collection_status) = ''
        ");
        DB::statement("
            UPDATE payments
            SET collection_status = 'collected'
            WHERE COALESCE(is_deferred, 0) = 1
                AND payment_status = 'completed'
                AND collected_at IS NOT NULL
        ");

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE payments MODIFY collection_status VARCHAR(255) NOT NULL DEFAULT 'pending'");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE payments ALTER COLUMN collection_status SET DEFAULT 'pending'");
            DB::statement("ALTER TABLE payments ALTER COLUMN collection_status SET NOT NULL");
        }

        if (!Schema::hasTable('payment_collections')) {
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
        }
    }

    public function down(): void
    {
        // No destructive rollback for server hardening migration.
    }
};
