<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('order_number')->nullable()->unique()->after('reference');
            $table->string('origin')->default('pos')->after('delivery_mode');
            $table->string('customer_activity')->nullable()->after('origin');
            $table->date('pickup_date')->nullable()->after('customer_activity');
            $table->text('delivery_address')->nullable()->after('pickup_date');
            $table->string('order_status')->default('confirmee')->after('status');
        });

        Schema::create('sale_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('status')->nullable();
            $table->string('action');
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        Schema::create('sale_item_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('sale_item_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('article_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('quantity', 10, 3);
            $table->string('condition')->default('bon_etat');
            $table->boolean('reintegrate_stock')->default(false);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_item_returns');
        Schema::dropIfExists('sale_logs');

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'order_number',
                'origin',
                'customer_activity',
                'pickup_date',
                'delivery_address',
                'order_status',
            ]);
        });
    }
};
