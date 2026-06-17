<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('printers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('connection_type', 32)->default('usb');
            $table->string('ip_address')->nullable();
            $table->string('subnet_mask')->nullable();
            $table->string('gateway')->nullable();
            $table->unsignedSmallInteger('port')->nullable();
            $table->string('usage', 32)->default('ticket_client');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('ticket_config')->nullable();
            $table->json('kitchen_config')->nullable();
            $table->json('advanced_config')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('printers');
    }
};
