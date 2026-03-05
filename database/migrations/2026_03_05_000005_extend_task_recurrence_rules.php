<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'recurrence_enabled')) {
                $table->boolean('recurrence_enabled')->default(false)->after('source_article_id');
            }

            if (!Schema::hasColumn('tasks', 'recurrence_pattern')) {
                $table->string('recurrence_pattern', 20)->nullable()->after('recurrence_enabled');
            }

            if (!Schema::hasColumn('tasks', 'recurrence_start_date')) {
                $table->date('recurrence_start_date')->nullable()->after('recurrence_pattern');
            }

            if (!Schema::hasColumn('tasks', 'recurrence_end_date')) {
                $table->date('recurrence_end_date')->nullable()->after('recurrence_start_date');
            }

            if (!Schema::hasColumn('tasks', 'recurrence_repeat_count')) {
                $table->unsignedInteger('recurrence_repeat_count')->nullable()->after('recurrence_end_date');
            }

            $table->index('recurrence_enabled');
            $table->index('recurrence_pattern');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['recurrence_pattern']);
            $table->dropIndex(['recurrence_enabled']);

            if (Schema::hasColumn('tasks', 'recurrence_repeat_count')) {
                $table->dropColumn('recurrence_repeat_count');
            }

            if (Schema::hasColumn('tasks', 'recurrence_end_date')) {
                $table->dropColumn('recurrence_end_date');
            }

            if (Schema::hasColumn('tasks', 'recurrence_start_date')) {
                $table->dropColumn('recurrence_start_date');
            }

            if (Schema::hasColumn('tasks', 'recurrence_pattern')) {
                $table->dropColumn('recurrence_pattern');
            }

            if (Schema::hasColumn('tasks', 'recurrence_enabled')) {
                $table->dropColumn('recurrence_enabled');
            }
        });
    }
};
