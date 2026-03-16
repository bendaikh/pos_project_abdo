<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_agents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['internal', 'platform'])->default('internal');
            $table->string('phone')->nullable();
            $table->enum('commission_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('commission_value', 10, 2)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('platform_name')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index(['platform_name', 'active']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('delivery_agent_id')
                ->nullable()
                ->after('employee_id')
                ->constrained('delivery_agents')
                ->nullOnDelete();
            $table->string('delivery_agent_name_snapshot')->nullable()->after('delivery_agent_id');
            $table->string('delivery_platform_name_snapshot')->nullable()->after('delivery_agent_name_snapshot');
            $table->enum('delivery_commission_type', ['percentage', 'fixed'])->nullable()->after('delivery_platform_name_snapshot');
            $table->decimal('delivery_commission_value_snapshot', 10, 2)->nullable()->after('delivery_commission_type');
            $table->decimal('delivery_commission_amount', 10, 2)->nullable()->after('delivery_commission_value_snapshot');
            $table->timestamp('delivery_commission_calculated_at')->nullable()->after('delivery_commission_amount');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('delivery_agent_id');
            $table->dropColumn([
                'delivery_agent_name_snapshot',
                'delivery_platform_name_snapshot',
                'delivery_commission_type',
                'delivery_commission_value_snapshot',
                'delivery_commission_amount',
                'delivery_commission_calculated_at',
            ]);
        });

        Schema::dropIfExists('delivery_agents');
    }
};
