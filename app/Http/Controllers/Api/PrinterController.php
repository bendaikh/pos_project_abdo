<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Printer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PrinterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Printer::query()->orderBy('name');

        if ($request->filled('usage')) {
            $query->where('usage', $request->usage);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%");
            });
        }

        $printers = $request->boolean('paginate', true)
            ? $query->paginate($request->integer('per_page', 20))
            : $query->get();

        return response()->json($printers);
    }

    public function store(Request $request): JsonResponse
    {
        $printer = Printer::create($this->validatedPayload($request));

        return response()->json($printer, 201);
    }

    public function show(Printer $printer): JsonResponse
    {
        return response()->json($printer);
    }

    public function update(Request $request, Printer $printer): JsonResponse
    {
        $printer->update($this->validatedPayload($request));

        return response()->json($printer->fresh());
    }

    public function destroy(Printer $printer): JsonResponse
    {
        $printer->delete();

        return response()->json(['message' => 'Imprimante supprimée']);
    }

    public function defaults(): JsonResponse
    {
        return response()->json([
            'ticket_config' => Printer::defaultTicketConfig(),
            'kitchen_config' => Printer::defaultKitchenConfig(),
            'advanced_config' => Printer::defaultAdvancedConfig(),
        ]);
    }

    private function validatedPayload(Request $request, ?Printer $printer = null): array
    {
        $validated = $request->validate([
            'name' => [$printer ? 'sometimes' : 'required', 'string', 'max:120'],
            'brand' => 'nullable|string|max:80',
            'model' => 'nullable|string|max:80',
            'connection_type' => ['nullable', Rule::in(['usb', 'network', 'ethernet', 'bluetooth'])],
            'ip_address' => 'nullable|string|max:45',
            'subnet_mask' => 'nullable|string|max:45',
            'gateway' => 'nullable|string|max:45',
            'port' => 'nullable|integer|min:1|max:65535',
            'usage' => ['nullable', Rule::in(['ticket_client', 'cuisine', 'both'])],
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'ticket_config' => 'nullable|array',
            'kitchen_config' => 'nullable|array',
            'advanced_config' => 'nullable|array',
        ]);

        if (! $printer) {
            $validated['ticket_config'] = $validated['ticket_config'] ?? Printer::defaultTicketConfig();
            $validated['kitchen_config'] = $validated['kitchen_config'] ?? Printer::defaultKitchenConfig();
            $validated['advanced_config'] = $validated['advanced_config'] ?? Printer::defaultAdvancedConfig();
        }

        return $validated;
    }
}
