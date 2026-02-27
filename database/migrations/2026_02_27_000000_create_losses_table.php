<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('losses', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->date('loss_date');
            $table->unsignedBigInteger('responsible_employee_id')->nullable();
            $table->string('responsible_name')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->integer('total_quantity')->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();

            $table->foreign('responsible_employee_id')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('losses');
    }
};
