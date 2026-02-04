<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Customer::query();

        if ($request->has('active')) {
            $query->active();
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('with_stats')) {
            $query->withCount(['sales as completed_sales_count' => function ($q) {
                $q->where('status', 'completed');
            }])
            ->withSum(['sales as total_spent' => function ($q) {
                $q->where('status', 'completed');
            }], 'total');
        }

        $customers = $query->orderBy('name')->paginate($request->get('per_page', 20));

        return response()->json($customers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $customer = Customer::create($validated);

        return response()->json($customer, 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        $customer->loadCount(['sales as completed_sales_count' => function ($q) {
            $q->where('status', 'completed');
        }]);
        
        $customer->loadSum(['sales as total_spent' => function ($q) {
            $q->where('status', 'completed');
        }], 'total');

        return response()->json($customer);
    }

    public function update(Request $request, Customer $customer): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $customer->update($validated);

        return response()->json($customer);
    }

    public function history(Customer $customer): JsonResponse
    {
        $sales = $customer->sales()
            ->with(['items.article'])
            ->latest()
            ->paginate(20);

        return response()->json($sales);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        return response()->json(null, 204);
    }
}
