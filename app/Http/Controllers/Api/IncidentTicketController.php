<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IncidentTicket;
use App\Models\IncidentTypeAssignment;
use App\Models\CustomList;
use App\Models\CustomListItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IncidentTicketController extends Controller
{
    public function index(Request $request)
    {
        $query = IncidentTicket::with(['incidentType', 'priority', 'responsible', 'reportedBy', 'creator']);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->byStatus($request->status);
        }

        // Filter by incident type
        if ($request->has('incident_type_id') && $request->incident_type_id !== '') {
            $query->where('incident_type_id', $request->incident_type_id);
        }

        // Filter by priority
        if ($request->has('priority_id') && $request->priority_id !== '') {
            $query->where('priority_id', $request->priority_id);
        }

        // Filter by responsible
        if ($request->has('responsible_id') && $request->responsible_id !== '') {
            $query->where('responsible_id', $request->responsible_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        // Search
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('responsible', function ($employeeQuery) use ($search) {
                      $employeeQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate or get all
        if ($request->has('paginate') && $request->paginate === 'true') {
            $tickets = $query->paginate($request->get('per_page', 15));
        } else {
            $tickets = $query->get();
        }

        return response()->json($tickets);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'incident_type_id' => 'required|exists:custom_list_items,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority_id' => 'required|exists:custom_list_items,id',
            'responsible_id' => 'nullable|exists:employees,id',
            'reported_by_id' => 'nullable|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // If no responsible is provided, try to get from auto-assignment
        if (empty($data['responsible_id'])) {
            $assignedEmployee = IncidentTypeAssignment::getResponsibleForType($data['incident_type_id']);
            if ($assignedEmployee) {
                $data['responsible_id'] = $assignedEmployee->id;
            } else {
                return response()->json([
                    'errors' => ['responsible_id' => ['Aucun responsable assigné pour ce type d\'incident. Veuillez sélectionner un responsable.']]
                ], 422);
            }
        }

        $data['created_by'] = auth()->id();
        $data['status'] = IncidentTicket::STATUS_EN_ATTENTE;

        $ticket = IncidentTicket::create($data);
        $ticket->load(['incidentType', 'priority', 'responsible', 'reportedBy', 'creator']);

        return response()->json([
            'message' => 'Ticket créé avec succès',
            'ticket' => $ticket
        ], 201);
    }

    public function show($id)
    {
        $ticket = IncidentTicket::with(['incidentType', 'priority', 'responsible', 'reportedBy', 'creator'])
            ->findOrFail($id);

        return response()->json($ticket);
    }

    public function update(Request $request, $id)
    {
        $ticket = IncidentTicket::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'incident_type_id' => 'sometimes|required|exists:custom_list_items,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'priority_id' => 'sometimes|required|exists:custom_list_items,id',
            'responsible_id' => 'sometimes|required|exists:employees,id',
            'reported_by_id' => 'nullable|exists:employees,id',
            'status' => 'sometimes|in:en_attente,en_cours,resolu,abandonne',
            'resolution_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Auto-set resolved_at when status changes to resolved
        if (isset($data['status']) && $data['status'] === IncidentTicket::STATUS_RESOLU && !$ticket->resolved_at) {
            $data['resolved_at'] = now();
        }

        $ticket->update($data);
        $ticket->load(['incidentType', 'priority', 'responsible', 'reportedBy', 'creator']);

        return response()->json([
            'message' => 'Ticket mis à jour avec succès',
            'ticket' => $ticket
        ]);
    }

    public function destroy($id)
    {
        $ticket = IncidentTicket::findOrFail($id);
        $ticket->delete();

        return response()->json([
            'message' => 'Ticket supprimé avec succès'
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $ticket = IncidentTicket::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:en_attente,en_cours,resolu,abandonne',
            'resolution_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = ['status' => $request->status];

        if ($request->status === IncidentTicket::STATUS_RESOLU) {
            $data['resolved_at'] = now();
            $data['resolution_notes'] = $request->resolution_notes;
        }

        $ticket->update($data);
        $ticket->load(['incidentType', 'priority', 'responsible', 'reportedBy', 'creator']);

        return response()->json([
            'message' => 'Statut mis à jour avec succès',
            'ticket' => $ticket
        ]);
    }

    public function statistics(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        $total = IncidentTicket::dateRange($startDate, $endDate)->count();
        $enAttente = IncidentTicket::dateRange($startDate, $endDate)->byStatus('en_attente')->count();
        $enCours = IncidentTicket::dateRange($startDate, $endDate)->byStatus('en_cours')->count();
        $resolu = IncidentTicket::dateRange($startDate, $endDate)->byStatus('resolu')->count();
        $abandonne = IncidentTicket::dateRange($startDate, $endDate)->byStatus('abandonne')->count();

        // Count by type
        $byType = IncidentTicket::dateRange($startDate, $endDate)
            ->selectRaw('incident_type_id, count(*) as count')
            ->groupBy('incident_type_id')
            ->with('incidentType')
            ->get();

        // Count by priority
        $byPriority = IncidentTicket::dateRange($startDate, $endDate)
            ->selectRaw('priority_id, count(*) as count')
            ->groupBy('priority_id')
            ->with('priority')
            ->get();

        return response()->json([
            'total' => $total,
            'en_attente' => $enAttente,
            'en_cours' => $enCours,
            'resolu' => $resolu,
            'abandonne' => $abandonne,
            'by_type' => $byType,
            'by_priority' => $byPriority,
        ]);
    }

    public function pending()
    {
        $tickets = IncidentTicket::with(['incidentType', 'priority', 'responsible'])
            ->pending()
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return response()->json($tickets);
    }

    public function getIncidentTypes()
    {
        $list = CustomList::where('name', 'incident_types')->first();
        if (!$list) {
            return response()->json([]);
        }

        $items = CustomListItem::where('list_id', $list->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json($items);
    }

    public function getIncidentPriorities()
    {
        $list = CustomList::where('name', 'incident_priorities')->first();
        if (!$list) {
            return response()->json([]);
        }

        $items = CustomListItem::where('list_id', $list->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json($items);
    }
}
