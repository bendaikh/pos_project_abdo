<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    /**
     * Display a listing of appointments.
     */
    public function index(Request $request)
    {
        $query = Appointment::with(['customer', 'responsible', 'creator']);

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->byStatus($request->status);
        }

        // Filter by customer
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by responsible
        if ($request->has('responsible_id')) {
            $query->where('responsible_id', $request->responsible_id);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($customerQuery) use ($search) {
                      $customerQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'date');
        $sortOrder = $request->get('sort_order', 'asc');
        
        if ($sortBy === 'date') {
            $query->orderBy('date', $sortOrder)->orderBy('time', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Paginate or get all
        if ($request->has('paginate') && $request->paginate === 'true') {
            $appointments = $query->paginate($request->get('per_page', 15));
        } else {
            $appointments = $query->get();
        }

        return response()->json($appointments);
    }

    /**
     * Get upcoming appointments.
     */
    public function upcoming()
    {
        $appointments = Appointment::with(['customer', 'responsible'])
            ->upcoming()
            ->take(10)
            ->get();

        return response()->json($appointments);
    }

    /**
     * Store a newly created appointment.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'time' => 'required',
            'duration' => 'nullable|integer|min:1',
            'subject' => 'required|string|max:255',
            'customer_id' => 'required|exists:customers,id',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'responsible_id' => 'nullable|exists:employees,id',
            'location' => 'nullable|string|max:255',
            'location_type' => 'nullable|in:magasin,sur_place,livraison,autre',
            'status' => 'nullable|in:en_cours,confirme,termine,annule',
            'notes' => 'nullable|string',
            'reminder_enabled' => 'nullable|boolean',
            'reminder_channel' => 'nullable|in:sms,whatsapp,notification,email',
            'reminder_timing' => 'nullable|in:24h,2h,30min,custom',
            'reminder_custom_value' => 'nullable|integer|min:1',
            'reminder_custom_unit' => 'nullable|in:minutes,hours,days',
            'reminder_message' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        
        // Get customer phone if not provided
        if (!isset($data['phone']) || !isset($data['whatsapp'])) {
            $customer = Customer::find($data['customer_id']);
            $data['phone'] = $data['phone'] ?? $customer->phone;
            $data['whatsapp'] = $data['whatsapp'] ?? $customer->whatsapp ?? $customer->phone;
        }

        // Set default reminder message if enabled
        if ($data['reminder_enabled'] ?? false) {
            if (!isset($data['reminder_message'])) {
                $customer = Customer::find($data['customer_id']);
                $data['reminder_message'] = "Bonjour {$customer->name}, rappel : {$data['subject']} le {$data['date']} à {$data['time']}.";
            }
        }

        $data['created_by'] = auth()->id();
        
        $appointment = Appointment::create($data);
        $appointment->load(['customer', 'responsible', 'creator']);

        return response()->json([
            'message' => 'Rendez-vous créé avec succès',
            'appointment' => $appointment
        ], 201);
    }

    /**
     * Display the specified appointment.
     */
    public function show($id)
    {
        $appointment = Appointment::with(['customer', 'responsible', 'creator'])->findOrFail($id);
        return response()->json($appointment);
    }

    /**
     * Update the specified appointment.
     */
    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'date' => 'sometimes|required|date',
            'time' => 'sometimes|required',
            'duration' => 'nullable|integer|min:1',
            'subject' => 'sometimes|required|string|max:255',
            'customer_id' => 'sometimes|required|exists:customers,id',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'responsible_id' => 'nullable|exists:employees,id',
            'location' => 'nullable|string|max:255',
            'location_type' => 'nullable|in:magasin,sur_place,livraison,autre',
            'status' => 'nullable|in:en_cours,confirme,termine,annule',
            'notes' => 'nullable|string',
            'reminder_enabled' => 'nullable|boolean',
            'reminder_channel' => 'nullable|in:sms,whatsapp,notification,email',
            'reminder_timing' => 'nullable|in:24h,2h,30min,custom',
            'reminder_custom_value' => 'nullable|integer|min:1',
            'reminder_custom_unit' => 'nullable|in:minutes,hours,days',
            'reminder_message' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $appointment->update($validator->validated());
        $appointment->load(['customer', 'responsible', 'creator']);

        return response()->json([
            'message' => 'Rendez-vous mis à jour avec succès',
            'appointment' => $appointment
        ]);
    }

    /**
     * Remove the specified appointment.
     */
    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();

        return response()->json([
            'message' => 'Rendez-vous supprimé avec succès'
        ]);
    }

    /**
     * Get appointments statistics.
     */
    public function statistics(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        $total = Appointment::dateRange($startDate, $endDate)->count();
        $enCours = Appointment::dateRange($startDate, $endDate)->byStatus('en_cours')->count();
        $confirme = Appointment::dateRange($startDate, $endDate)->byStatus('confirme')->count();
        $termine = Appointment::dateRange($startDate, $endDate)->byStatus('termine')->count();
        $annule = Appointment::dateRange($startDate, $endDate)->byStatus('annule')->count();

        return response()->json([
            'total' => $total,
            'en_cours' => $enCours,
            'confirme' => $confirme,
            'termine' => $termine,
            'annule' => $annule,
        ]);
    }

    /**
     * Get appointments needing reminders.
     */
    public function needingReminders()
    {
        $appointments = Appointment::with(['customer'])
            ->where('reminder_enabled', true)
            ->whereNull('reminder_sent_at')
            ->whereIn('status', ['en_cours', 'confirme'])
            ->where('date', '>=', now()->toDateString())
            ->get()
            ->filter(function ($appointment) {
                return $appointment->needsReminder();
            });

        return response()->json($appointments->values());
    }

    /**
     * Mark reminder as sent.
     */
    public function markReminderSent($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->reminder_sent_at = now();
        $appointment->save();

        return response()->json([
            'message' => 'Rappel marqué comme envoyé',
            'appointment' => $appointment
        ]);
    }
}
