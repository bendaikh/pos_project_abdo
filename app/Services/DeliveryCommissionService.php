<?php

namespace App\Services;

use App\Models\DeliveryAgent;
use App\Models\Sale;

class DeliveryCommissionService
{
    public function calculateAmount(?DeliveryAgent $deliveryAgent, float $saleTotal): float
    {
        if (!$deliveryAgent) {
            return 0.0;
        }

        $saleTotal = round(max(0, $saleTotal), 2);
        $commissionValue = round((float) $deliveryAgent->commission_value, 2);

        if ($deliveryAgent->commission_type === 'fixed') {
            return $commissionValue;
        }

        return round($saleTotal * ($commissionValue / 100), 2);
    }

    public function captureSnapshot(Sale $sale, bool $force = false): Sale
    {
        if ($sale->order_status !== 'livree' && !$force) {
            return $sale;
        }

        $sale->loadMissing('deliveryAgent');
        $deliveryAgent = $sale->deliveryAgent;

        if (!$deliveryAgent) {
            return $sale;
        }

        $snapshot = [
            'delivery_agent_name_snapshot' => $deliveryAgent->name,
            'delivery_platform_name_snapshot' => $deliveryAgent->platform_name,
            'delivery_commission_type' => $deliveryAgent->commission_type,
            'delivery_commission_value_snapshot' => (float) $deliveryAgent->commission_value,
            'delivery_commission_amount' => $this->calculateAmount($deliveryAgent, (float) $sale->total),
            'delivery_commission_calculated_at' => now(),
        ];

        $sale->forceFill($snapshot);

        if ($sale->isDirty(array_keys($snapshot))) {
            $sale->saveQuietly();
        }

        return $sale;
    }
}
