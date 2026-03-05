<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Add automation_rule_id to track which rule created this task
            $table->foreignId('automation_rule_id')->nullable()->after('created_by')->constrained('automation_rules')->onDelete('set null');
            $table->boolean('created_by_rule')->default(false)->after('automation_rule_id');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['automation_rule_id']);
            $table->dropColumn(['automation_rule_id', 'created_by_rule']);
        });
    }
};
