<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Customer::query();

        if ($request->has('active')) {
            if ($request->boolean('active')) {
                $query->active();
            } else {
                $query->where('is_active', false);
            }
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

        $query->orderBy('name');
        $paginate = $request->boolean('paginate', true);
        $customers = $paginate
            ? $query->paginate($request->get('per_page', 20))
            : $query->get();

        return response()
            ->json($customers)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'activity' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'photo_url' => 'nullable|string|max:8000000',
            'photo' => 'nullable|string|max:8000000',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated = $this->normalizePhotoPayload($validated);
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
            'activity' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'photo_url' => 'nullable|string|max:8000000',
            'photo' => 'nullable|string|max:8000000',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated = $this->normalizePhotoPayload($validated);
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

    private function normalizePhotoPayload(array $validated): array
    {
        $photoUrl = $validated['photo_url'] ?? null;
        $legacyPhoto = $validated['photo'] ?? null;

        if (($photoUrl === null || $photoUrl === '') && $legacyPhoto !== null && $legacyPhoto !== '') {
            $photoUrl = $legacyPhoto;
        }

        unset($validated['photo']);

        if ($photoUrl === null || $photoUrl === '') {
            $validated['photo_url'] = null;
            return $validated;
        }

        $photoUrl = trim((string) $photoUrl);

        $isHttpUrl = filter_var($photoUrl, FILTER_VALIDATE_URL) !== false;
        $isDataImage = str_starts_with($photoUrl, 'data:image/');
        $isRelativePath = str_starts_with($photoUrl, '/')
            || str_starts_with($photoUrl, 'storage/')
            || str_starts_with($photoUrl, 'uploads/');

        if (!$isHttpUrl && !$isDataImage && !$isRelativePath) {
            throw ValidationException::withMessages([
                'photo_url' => ['Format image client invalide'],
            ]);
        }

        $validated['photo_url'] = $photoUrl;

        return $validated;
    }
}
