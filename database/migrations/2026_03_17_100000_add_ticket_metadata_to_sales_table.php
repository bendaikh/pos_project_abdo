<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('ticket_type')->nullable()->after('origin');
            $table->string('ticket_name')->nullable()->after('ticket_type');
            $table->string('ticket_group')->nullable()->after('ticket_name');
            $table->dateTime('appointment_at')->nullable()->after('pickup_date');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'ticket_type',
                'ticket_name',
                'ticket_group',
                'appointment_at',
            ]);
        });
    }
};
