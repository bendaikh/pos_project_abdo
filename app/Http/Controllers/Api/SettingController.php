<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = Setting::all()->groupBy('group')->map(function ($group) {
            return $group->mapWithKeys(function ($setting) {
                return [$setting->key => $this->castValue($setting->value, $setting->type)];
            });
        });

        return response()->json($settings);
    }

    public function byGroup(string $group): JsonResponse
    {
        $settings = Setting::where('group', $group)->get()->mapWithKeys(function ($setting) {
            return [$setting->key => $this->castValue($setting->value, $setting->type)];
        });

        return response()->json($settings);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable',
            'settings.*.type' => 'nullable|string|in:string,boolean,number,json',
            'settings.*.group' => 'nullable|string',
        ]);

        foreach ($validated['settings'] as $item) {
            $value = $item['value'];
            
            // Convert value to string for storage
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } elseif (is_array($value)) {
                $value = json_encode($value);
            }

            Setting::updateOrCreate(
                ['key' => $item['key']],
                [
                    'value' => $value,
                    'type' => $item['type'] ?? 'string',
                    'group' => $item['group'] ?? 'general',
                ]
            );
        }

        Setting::clearCache();

        return response()->json(['message' => 'Paramètres mis à jour avec succès']);
    }

    private function castValue($value, string $type)
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'number' => is_numeric($value) ? (float) $value : 0,
            'json' => json_decode($value, true),
            default => $value,
        };
    }
}
