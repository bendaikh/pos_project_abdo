<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create custom list for incident types
        $incidentTypesListId = DB::table('custom_lists')->insertGetId([
            'name' => 'incident_types',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert default incident types
        $incidentTypes = [
            ['label' => 'Dysfonctionnement POS', 'sort_order' => 1],
            ['label' => 'Réparation matériel', 'sort_order' => 2],
            ['label' => 'Erreur de caisse', 'sort_order' => 3],
            ['label' => 'Problème système', 'sort_order' => 4],
            ['label' => 'Maintenance', 'sort_order' => 5],
            ['label' => 'Demande interne', 'sort_order' => 6],
        ];

        foreach ($incidentTypes as $type) {
            DB::table('custom_list_items')->insert([
                'list_id' => $incidentTypesListId,
                'label' => $type['label'],
                'is_active' => true,
                'sort_order' => $type['sort_order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create custom list for incident priorities
        $prioritiesListId = DB::table('custom_lists')->insertGetId([
            'name' => 'incident_priorities',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert default priorities with color metadata
        $priorities = [
            ['label' => 'Urgente', 'value' => 'urgente', 'metadata' => json_encode(['color' => '#EF4444', 'bg_color' => '#FEE2E2']), 'sort_order' => 1],
            ['label' => 'Moyenne', 'value' => 'moyenne', 'metadata' => json_encode(['color' => '#F59E0B', 'bg_color' => '#FEF3C7']), 'sort_order' => 2],
            ['label' => 'Faible', 'value' => 'faible', 'metadata' => json_encode(['color' => '#10B981', 'bg_color' => '#D1FAE5']), 'sort_order' => 3],
        ];

        foreach ($priorities as $priority) {
            DB::table('custom_list_items')->insert([
                'list_id' => $prioritiesListId,
                'label' => $priority['label'],
                'value' => $priority['value'],
                'metadata' => $priority['metadata'],
                'is_active' => true,
                'sort_order' => $priority['sort_order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create incident type assignments table (for auto-assignment rules)
        Schema::create('incident_type_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_type_id')->constrained('custom_list_items')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['incident_type_id']);
        });

        // Create incident tickets table
        Schema::create('incident_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('incident_type_id')->constrained('custom_list_items')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('priority_id')->constrained('custom_list_items')->cascadeOnDelete();
            $table->foreignId('responsible_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('reported_by_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('status')->default('en_attente');
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status']);
            $table->index(['created_at']);
            $table->index(['responsible_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_tickets');
        Schema::dropIfExists('incident_type_assignments');

        // Remove custom lists
        $lists = DB::table('custom_lists')
            ->whereIn('name', ['incident_types', 'incident_priorities'])
            ->pluck('id');

        DB::table('custom_list_items')->whereIn('list_id', $lists)->delete();
        DB::table('custom_lists')->whereIn('id', $lists)->delete();
    }
};
