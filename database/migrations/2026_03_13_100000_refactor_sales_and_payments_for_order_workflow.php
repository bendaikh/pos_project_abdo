<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'payment_status_code')) {
                $table->string('payment_status_code')->default('to_pay')->after('payment_status');
                $table->index('payment_status_code');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('sale_id')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('payments', 'transfer_mode')) {
                $table->string('transfer_mode')->nullable()->after('payment_type');
            }
            if (!Schema::hasColumn('payments', 'paid_at')) {
                $table->dateTime('paid_at')->nullable()->after('due_date');
            }
            if (!Schema::hasColumn('payments', 'confirmed_at')) {
                $table->dateTime('confirmed_at')->nullable()->after('paid_at');
            }
            if (!Schema::hasColumn('payments', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('confirmed_at')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('payments', 'validated_by')) {
                $table->foreignId('validated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }
        });

        DB::table('sales')
            ->where('payment_status', 'unpaid')
            ->update(['payment_status_code' => 'to_pay']);

        DB::table('sales')
            ->where('payment_status', 'partial')
            ->update(['payment_status_code' => 'to_pay']);

        DB::table('sales')
            ->where('payment_status', 'paid')
            ->update(['payment_status_code' => 'paid']);

        DB::statement('
            UPDATE payments
            SET customer_id = (
                SELECT customer_id
                FROM sales
                WHERE sales.id = payments.sale_id
            )
            WHERE customer_id IS NULL
        ');

        DB::statement("
            UPDATE payments
            SET transfer_mode = CASE
                WHEN payment_type = 'virement' AND (notes LIKE '%[VIREMENT_INSTANT]%') THEN 'instant'
                WHEN payment_type = 'virement' THEN 'simple'
                ELSE transfer_mode
            END
            WHERE transfer_mode IS NULL
        ");

        DB::statement("
            UPDATE payments
            SET paid_at = COALESCE(paid_at, created_at)
            WHERE paid_at IS NULL
        ");

        DB::statement("
            UPDATE payments
            SET confirmed_at = CASE
                WHEN confirmed_at IS NOT NULL THEN confirmed_at
                WHEN payment_status = 'completed' AND (collection_status = 'collected' OR COALESCE(is_deferred, 0) = 0)
                    THEN COALESCE(collected_at, created_at)
                ELSE confirmed_at
            END
        ");

        DB::statement("
            UPDATE payments
            SET created_by = (
                SELECT user_id
                FROM sales
                WHERE sales.id = payments.sale_id
            )
            WHERE created_by IS NULL
        ");

        DB::statement("
            UPDATE payments
            SET validated_by = created_by
            WHERE validated_by IS NULL
              AND payment_status = 'completed'
              AND (collection_status = 'collected' OR COALESCE(is_deferred, 0) = 0)
        ");
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'validated_by')) {
                $table->dropConstrainedForeignId('validated_by');
            }
            if (Schema::hasColumn('payments', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
            if (Schema::hasColumn('payments', 'confirmed_at')) {
                $table->dropColumn('confirmed_at');
            }
            if (Schema::hasColumn('payments', 'paid_at')) {
                $table->dropColumn('paid_at');
            }
            if (Schema::hasColumn('payments', 'transfer_mode')) {
                $table->dropColumn('transfer_mode');
            }
            if (Schema::hasColumn('payments', 'customer_id')) {
                $table->dropConstrainedForeignId('customer_id');
            }
        });

        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'payment_status_code')) {
                $table->dropIndex(['payment_status_code']);
                $table->dropColumn('payment_status_code');
            }
        });
    }
};
