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
            $table->boolean('is_automated')->default(false)->after('created_by');
            $table->enum('automation_type', ['low_stock', 'recurring'])->nullable()->after('is_automated');
            $table->foreignId('source_article_id')->nullable()->after('automation_type')->constrained('articles')->nullOnDelete();

            $table->enum('recurrence_frequency', ['weekly', 'monthly', 'quarterly'])->nullable()->after('source_article_id');
            $table->date('recurrence_until')->nullable()->after('recurrence_frequency');
            $table->foreignId('recurrence_parent_id')->nullable()->after('recurrence_until')->constrained('tasks')->nullOnDelete();
            $table->timestamp('last_generated_at')->nullable()->after('recurrence_parent_id');

            $table->index('is_automated');
            $table->index('automation_type');
            $table->index('recurrence_frequency');
            $table->index(['recurrence_parent_id', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['recurrence_parent_id', 'due_date']);
            $table->dropIndex(['recurrence_frequency']);
            $table->dropIndex(['automation_type']);
            $table->dropIndex(['is_automated']);

            $table->dropConstrainedForeignId('recurrence_parent_id');
            $table->dropColumn('recurrence_until');
            $table->dropColumn('recurrence_frequency');

            $table->dropConstrainedForeignId('source_article_id');
            $table->dropColumn('automation_type');
            $table->dropColumn('is_automated');
            $table->dropColumn('last_generated_at');
        });
    }
};
