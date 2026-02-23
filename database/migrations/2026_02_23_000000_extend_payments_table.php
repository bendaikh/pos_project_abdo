<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Add fields for different payment types
            $table->string('transaction_number')->nullable()->after('reference'); // For card, virement, chèque
            $table->string('piece_number')->nullable()->after('transaction_number'); // For virement simple, chèque, crédit
            $table->date('issue_date')->nullable()->after('piece_number'); // Date d'émission
            $table->string('bank_name')->nullable()->after('issue_date'); // Bank name
            $table->date('due_date')->nullable()->after('bank_name'); // Date d'échéance
            $table->enum('payment_status', ['pending', 'completed', 'failed'])->default('completed')->after('due_date');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'transaction_number',
                'piece_number',
                'issue_date',
                'bank_name',
                'due_date',
                'payment_status'
            ]);
        });
    }
};
