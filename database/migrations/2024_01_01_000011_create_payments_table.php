<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->enum('payment_type', ['cash', 'card', 'check', 'mobile', 'other'])->default('cash');
            $table->decimal('amount', 10, 2);
            $table->decimal('received_amount', 10, 2)->nullable(); // For cash payments
            $table->decimal('change_amount', 10, 2)->default(0);
            $table->string('reference')->nullable(); // Card reference, check number, etc.
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
