<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\TaskAutomationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function runAutomation(TaskAutomationService $taskAutomationService)
    {
        if ($error = $this->ensureTasksTable()) {
            return $error;
        }

        $result = $taskAutomationService->runAll();

        return response()->json([
            'message' => 'Automatisation exécutée avec succès',
            'result' => $result,
        ]);
    }

    private function ensureTasksTable()
    {
        if (!Schema::hasTable('tasks')) {
            return response()->json([
                'message' => "La table 'tasks' est absente. Exécutez: php artisan migrate",
            ], 500);
        }

        return null;
    }

    /**
     * Display a listing of tasks.
     */
    public function index(Request $request)
    {
        if ($error = $this->ensureTasksTable()) {
            return $error;
        }

        $query = Task::with(['employee', 'creator']);

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->byStatus($request->status);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority !== '') {
            $query->byPriority($request->priority);
        }

        // Filter by employee
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter overdue
        if ($request->has('overdue') && $request->overdue === 'true') {
            $query->overdue();
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($employeeQuery) use ($search) {
                      $employeeQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'due_date');
        $sortOrder = $request->get('sort_order', 'asc');
        
        if ($sortBy === 'due_date') {
            $query->orderBy('due_date', $sortOrder)->orderBy('due_time', $sortOrder);
        } elseif ($sortBy === 'priority') {
            // Custom priority ordering: urgente > moyenne > faible
            $query->orderByRaw("FIELD(priority, 'urgente', 'moyenne', 'faible')");
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Paginate or get all
        if ($request->has('paginate') && $request->paginate === 'true') {
            $tasks = $query->paginate($request->get('per_page', 15));
        } else {
            $tasks = $query->get();
        }

        return response()->json($tasks);
    }

    /**
     * Get pending tasks.
     */
    public function pending()
    {
        if ($error = $this->ensureTasksTable()) {
            return $error;
        }

        $tasks = Task::with(['employee'])
            ->pending()
            ->take(10)
            ->get();

        return response()->json($tasks);
    }

    /**
     * Get overdue tasks.
     */
    public function overdue()
    {
        if ($error = $this->ensureTasksTable()) {
            return $error;
        }

        $tasks = Task::with(['employee'])
            ->overdue()
            ->get();

        return response()->json($tasks);
    }

    /**
     * Store a newly created task.
     */
    public function store(Request $request)
    {
        if ($error = $this->ensureTasksTable()) {
            return $error;
        }

        $supportsRecurrence = Schema::hasColumn('tasks', 'recurrence_enabled');

        $validator = Validator::make($request->all(), [
            'due_date' => 'required|date',
            'due_time' => 'nullable',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'employee_id' => 'required|exists:employees,id',
            'priority' => 'nullable|in:faible,moyenne,urgente',
            'status' => 'nullable|in:en_attente,en_cours,termine,annule',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max per file
            'reminder_enabled' => 'nullable|boolean',
            'reminder_channel' => 'nullable|in:notification,sms,whatsapp',
            'reminder_timing' => 'nullable|in:at_time,1h,30min,custom',
            'reminder_custom_value' => 'nullable|integer|min:1',
            'reminder_custom_unit' => 'nullable|in:minutes,hours,days',
            'reminder_repeat_until_validation' => 'nullable|boolean',
            'reminder_repeat_interval' => 'nullable|integer|min:1',
            'recurrence_enabled' => $supportsRecurrence ? 'nullable|boolean' : 'nullable',
            'recurrence_pattern' => $supportsRecurrence ? 'nullable|in:daily,weekly,monthly,quarterly,semiannual,yearly' : 'nullable',
            'recurrence_start_date' => $supportsRecurrence ? 'nullable|date' : 'nullable',
            'recurrence_end_date' => $supportsRecurrence ? 'nullable|date|after_or_equal:recurrence_start_date' : 'nullable',
            'recurrence_repeat_count' => $supportsRecurrence ? 'nullable|integer|min:1' : 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        if ($supportsRecurrence) {
            $data['recurrence_enabled'] = (bool) ($data['recurrence_enabled'] ?? false);
        }

        if ($supportsRecurrence && $data['recurrence_enabled']) {
            $data['recurrence_start_date'] = $data['recurrence_start_date'] ?? $data['due_date'];
            $data['due_date'] = $data['recurrence_start_date'];

            if (empty($data['recurrence_pattern'])) {
                return response()->json([
                    'errors' => ['recurrence_pattern' => ['La frequence est obligatoire pour une tache recurrente.']]
                ], 422);
            }

            $data['recurrence_frequency'] = in_array($data['recurrence_pattern'], ['weekly', 'monthly', 'quarterly'], true)
                ? $data['recurrence_pattern']
                : null;
            $data['recurrence_until'] = $data['recurrence_end_date'] ?? null;
        } elseif ($supportsRecurrence) {
            $data['recurrence_pattern'] = null;
            $data['recurrence_start_date'] = null;
            $data['recurrence_end_date'] = null;
            $data['recurrence_repeat_count'] = null;
            $data['recurrence_frequency'] = null;
            $data['recurrence_until'] = null;
        }

        if ($supportsRecurrence && !empty($data['recurrence_end_date']) && !empty($data['recurrence_repeat_count'])) {
            return response()->json([
                'errors' => ['recurrence_repeat_count' => ['Utilisez soit date de fin, soit nombre de repetitions.']]
            ], 422);
        }

        if (!$supportsRecurrence) {
            unset(
                $data['recurrence_enabled'],
                $data['recurrence_pattern'],
                $data['recurrence_start_date'],
                $data['recurrence_end_date'],
                $data['recurrence_repeat_count'],
                $data['recurrence_frequency'],
                $data['recurrence_until']
            );
        }
        
        // Handle file uploads
        if ($request->hasFile('attachments')) {
            $attachmentPaths = [];
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('task-attachments', 'public');
                $attachmentPaths[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ];
            }
            $data['attachments'] = $attachmentPaths;
        }

        $data['created_by'] = auth()->id();
        
        $task = Task::create($data);
        $task->load(['employee', 'creator']);

        return response()->json([
            'message' => 'Tâche créée avec succès',
            'task' => $task
        ], 201);
    }

    /**
     * Display the specified task.
     */
    public function show($id)
    {
        if ($error = $this->ensureTasksTable()) {
            return $error;
        }

        $task = Task::with(['employee', 'creator'])->findOrFail($id);
        return response()->json($task);
    }

    /**
     * Update the specified task.
     */
    public function update(Request $request, $id)
    {
        if ($error = $this->ensureTasksTable()) {
            return $error;
        }

        $task = Task::findOrFail($id);
        $supportsRecurrence = Schema::hasColumn('tasks', 'recurrence_enabled');

        $validator = Validator::make($request->all(), [
            'due_date' => 'sometimes|required|date',
            'due_time' => 'nullable',
            'subject' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'employee_id' => 'sometimes|required|exists:employees,id',
            'priority' => 'nullable|in:faible,moyenne,urgente',
            'status' => 'nullable|in:en_attente,en_cours,termine,annule',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
            'reminder_enabled' => 'nullable|boolean',
            'reminder_channel' => 'nullable|in:notification,sms,whatsapp',
            'reminder_timing' => 'nullable|in:at_time,1h,30min,custom',
            'reminder_custom_value' => 'nullable|integer|min:1',
            'reminder_custom_unit' => 'nullable|in:minutes,hours,days',
            'reminder_repeat_until_validation' => 'nullable|boolean',
            'reminder_repeat_interval' => 'nullable|integer|min:1',
            'recurrence_enabled' => $supportsRecurrence ? 'nullable|boolean' : 'nullable',
            'recurrence_pattern' => $supportsRecurrence ? 'nullable|in:daily,weekly,monthly,quarterly,semiannual,yearly' : 'nullable',
            'recurrence_start_date' => $supportsRecurrence ? 'nullable|date' : 'nullable',
            'recurrence_end_date' => $supportsRecurrence ? 'nullable|date|after_or_equal:recurrence_start_date' : 'nullable',
            'recurrence_repeat_count' => $supportsRecurrence ? 'nullable|integer|min:1' : 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        if ($supportsRecurrence && array_key_exists('recurrence_enabled', $data)) {
            $data['recurrence_enabled'] = (bool) $data['recurrence_enabled'];
        }

        $isRecurring = $supportsRecurrence
            ? ($data['recurrence_enabled'] ?? $task->recurrence_enabled)
            : false;

        if ($supportsRecurrence && $isRecurring) {
            $startDate = $data['recurrence_start_date']
                ?? ($data['due_date'] ?? optional($task->recurrence_start_date)->toDateString() ?? optional($task->due_date)->toDateString());

            $data['recurrence_start_date'] = $startDate;
            if ($startDate && !isset($data['due_date'])) {
                $data['due_date'] = $startDate;
            }

            $pattern = $data['recurrence_pattern'] ?? $task->recurrence_pattern;
            if (empty($pattern)) {
                return response()->json([
                    'errors' => ['recurrence_pattern' => ['La frequence est obligatoire pour une tache recurrente.']]
                ], 422);
            }

            $data['recurrence_frequency'] = in_array($pattern, ['weekly', 'monthly', 'quarterly'], true)
                ? $pattern
                : null;
            $data['recurrence_until'] = $data['recurrence_end_date'] ?? optional($task->recurrence_end_date)->toDateString();
        } elseif ($supportsRecurrence) {
            $data['recurrence_pattern'] = null;
            $data['recurrence_start_date'] = null;
            $data['recurrence_end_date'] = null;
            $data['recurrence_repeat_count'] = null;
            $data['recurrence_frequency'] = null;
            $data['recurrence_until'] = null;
        }

        $endDate = $supportsRecurrence ? ($data['recurrence_end_date'] ?? optional($task->recurrence_end_date)->toDateString()) : null;
        $repeatCount = $supportsRecurrence ? ($data['recurrence_repeat_count'] ?? $task->recurrence_repeat_count) : null;
        if ($supportsRecurrence && !empty($endDate) && !empty($repeatCount)) {
            return response()->json([
                'errors' => ['recurrence_repeat_count' => ['Utilisez soit date de fin, soit nombre de repetitions.']]
            ], 422);
        }

        if (!$supportsRecurrence) {
            unset(
                $data['recurrence_enabled'],
                $data['recurrence_pattern'],
                $data['recurrence_start_date'],
                $data['recurrence_end_date'],
                $data['recurrence_repeat_count'],
                $data['recurrence_frequency'],
                $data['recurrence_until']
            );
        }

        // Handle new file uploads
        if ($request->hasFile('attachments')) {
            $existingAttachments = $task->attachments ?? [];
            $newAttachments = [];
            
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('task-attachments', 'public');
                $newAttachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ];
            }
            
            $data['attachments'] = array_merge($existingAttachments, $newAttachments);
        }

        // Auto-set completed_at when status changes to termine
        if (isset($data['status']) && $data['status'] === 'termine' && !$task->completed_at) {
            $data['completed_at'] = now();
        }

        $task->update($data);
        $task->load(['employee', 'creator']);

        return response()->json([
            'message' => 'Tâche mise à jour avec succès',
            'task' => $task
        ]);
    }

    /**
     * Remove the specified task.
     */
    public function destroy($id)
    {
        if ($error = $this->ensureTasksTable()) {
            return $error;
        }

        $task = Task::findOrFail($id);
        
        // Delete attachments
        if ($task->attachments) {
            foreach ($task->attachments as $attachment) {
                if (isset($attachment['path'])) {
                    Storage::disk('public')->delete($attachment['path']);
                }
            }
        }
        
        $task->delete();

        return response()->json([
            'message' => 'Tâche supprimée avec succès'
        ]);
    }

    /**
     * Mark task as completed.
     */
    public function markCompleted($id)
    {
        if ($error = $this->ensureTasksTable()) {
            return $error;
        }

        $task = Task::findOrFail($id);
        $task->markCompleted();
        $task->load(['employee', 'creator']);

        return response()->json([
            'message' => 'Tâche marquée comme terminée',
            'task' => $task
        ]);
    }

    /**
     * Get tasks statistics.
     */
    public function statistics(Request $request)
    {
        if ($error = $this->ensureTasksTable()) {
            return $error;
        }

        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        $total = Task::dateRange($startDate, $endDate)->count();
        $enAttente = Task::dateRange($startDate, $endDate)->byStatus('en_attente')->count();
        $enCours = Task::dateRange($startDate, $endDate)->byStatus('en_cours')->count();
        $termine = Task::dateRange($startDate, $endDate)->byStatus('termine')->count();
        $overdue = Task::dateRange($startDate, $endDate)->overdue()->count();

        $urgente = Task::dateRange($startDate, $endDate)->byPriority('urgente')->count();
        $moyenne = Task::dateRange($startDate, $endDate)->byPriority('moyenne')->count();
        $faible = Task::dateRange($startDate, $endDate)->byPriority('faible')->count();

        return response()->json([
            'total' => $total,
            'en_attente' => $enAttente,
            'en_cours' => $enCours,
            'termine' => $termine,
            'overdue' => $overdue,
            'urgente' => $urgente,
            'moyenne' => $moyenne,
            'faible' => $faible,
        ]);
    }

    /**
     * Get tasks needing reminders.
     */
    public function needingReminders()
    {
        if ($error = $this->ensureTasksTable()) {
            return $error;
        }

        $tasks = Task::with(['employee'])
            ->where('reminder_enabled', true)
            ->whereIn('status', ['en_attente', 'en_cours'])
            ->where('due_date', '>=', now()->toDateString())
            ->get()
            ->filter(function ($task) {
                return $task->needsReminder();
            });

        return response()->json($tasks->values());
    }

    /**
     * Mark reminder as sent.
     */
    public function markReminderSent($id)
    {
        if ($error = $this->ensureTasksTable()) {
            return $error;
        }

        $task = Task::findOrFail($id);
        $task->reminder_sent_at = now();
        $task->save();

        return response()->json([
            'message' => 'Rappel marqué comme envoyé',
            'task' => $task
        ]);
    }

    /**
     * Delete attachment from task.
     */
    public function deleteAttachment(Request $request, $id)
    {
        if ($error = $this->ensureTasksTable()) {
            return $error;
        }

        $task = Task::findOrFail($id);
        $attachmentIndex = $request->input('index');

        if ($task->attachments && isset($task->attachments[$attachmentIndex])) {
            $attachment = $task->attachments[$attachmentIndex];
            
            // Delete file from storage
            if (isset($attachment['path'])) {
                Storage::disk('public')->delete($attachment['path']);
            }
            
            // Remove from array
            $attachments = $task->attachments;
            unset($attachments[$attachmentIndex]);
            $task->attachments = array_values($attachments);
            $task->save();

            return response()->json([
                'message' => 'Pièce jointe supprimée avec succès',
                'task' => $task
            ]);
        }

        return response()->json(['message' => 'Pièce jointe non trouvée'], 404);
    }
}
