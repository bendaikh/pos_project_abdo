<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DeliveryAgent;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;
use App\Services\DeliveryCommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryAgentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_marking_order_as_delivered_captures_percentage_commission_snapshot(): void
    {
        $user = User::factory()->create();
        $deliveryAgent = DeliveryAgent::create([
            'name' => 'Ali',
            'type' => 'internal',
            'phone' => '0600000001',
            'commission_type' => 'percentage',
            'commission_value' => 10,
            'status' => 'active',
            'active' => true,
        ]);

        $sale = $this->createSale([
            'total' => 200,
            'delivery_agent_id' => $deliveryAgent->id,
        ]);

        $response = $this->actingAs($user)->postJson("/api/sales/{$sale->id}/status", [
            'order_status' => 'livree',
            'comment' => 'Commande livrée par Ali',
        ]);

        $response->assertOk()
            ->assertJsonPath('delivery_commission_type', 'percentage')
            ->assertJsonPath('delivery_commission_value_snapshot', '10.00')
            ->assertJsonPath('delivery_commission_amount', '20.00')
            ->assertJsonPath('delivery_agent.id', $deliveryAgent->id);
    }

    public function test_completing_sale_captures_fixed_commission_snapshot(): void
    {
        $user = User::factory()->create();
        $deliveryAgent = DeliveryAgent::create([
            'name' => 'Glovo',
            'type' => 'platform',
            'phone' => '0600000002',
            'platform_name' => 'Glovo',
            'commission_type' => 'fixed',
            'commission_value' => 15,
            'status' => 'active',
            'active' => true,
        ]);

        $sale = $this->createSale([
            'total' => 200,
            'delivery_mode' => 'delivery',
            'delivery_agent_id' => $deliveryAgent->id,
        ]);

        Payment::create(Payment::persistable([
            'sale_id' => $sale->id,
            'customer_id' => $sale->customer_id,
            'payment_type' => 'cash',
            'amount' => 200,
            'received_amount' => 200,
            'change_amount' => 0,
            'payment_status' => 'completed',
            'paid_at' => now(),
            'confirmed_at' => now(),
            'is_deferred' => false,
            'collection_status' => 'collected',
            'collected_at' => now(),
            'created_by' => $user->id,
            'validated_by' => $user->id,
        ]));

        $response = $this->actingAs($user)->postJson("/api/sales/{$sale->id}/complete");

        $response->assertOk()
            ->assertJsonPath('order_status', 'livree')
            ->assertJsonPath('delivery_commission_type', 'fixed')
            ->assertJsonPath('delivery_commission_value_snapshot', '15.00')
            ->assertJsonPath('delivery_commission_amount', '15.00')
            ->assertJsonPath('delivery_agent.id', $deliveryAgent->id);
    }

    public function test_delivery_agents_report_aggregates_deliveries_and_financial_follow_up(): void
    {
        $user = User::factory()->create();
        $deliveryAgent = DeliveryAgent::create([
            'name' => 'Ali',
            'type' => 'internal',
            'phone' => '0600000003',
            'commission_type' => 'percentage',
            'commission_value' => 10,
            'status' => 'active',
            'active' => true,
        ]);

        $paidSale = $this->createSale([
            'total' => 100,
            'order_status' => 'livree',
            'delivery_mode' => 'delivery',
            'delivery_agent_id' => $deliveryAgent->id,
        ]);
        app(DeliveryCommissionService::class)->captureSnapshot($paidSale, true);

        $unpaidSale = $this->createSale([
            'total' => 50,
            'order_status' => 'livree',
            'delivery_mode' => 'delivery',
            'delivery_agent_id' => $deliveryAgent->id,
        ]);
        app(DeliveryCommissionService::class)->captureSnapshot($unpaidSale, true);

        Payment::create(Payment::persistable([
            'sale_id' => $paidSale->id,
            'customer_id' => $paidSale->customer_id,
            'payment_type' => 'cash',
            'amount' => 100,
            'received_amount' => 100,
            'change_amount' => 0,
            'payment_status' => 'completed',
            'paid_at' => now(),
            'confirmed_at' => now(),
            'is_deferred' => false,
            'collection_status' => 'collected',
            'collected_at' => now(),
            'created_by' => $user->id,
            'validated_by' => $user->id,
        ]));

        $response = $this->actingAs($user)->getJson('/api/delivery-agents/report');

        $response->assertOk()
            ->assertJsonPath('totals.agents_count', 1)
            ->assertJsonPath('totals.orders_count', 2)
            ->assertJsonPath('totals.total_delivery_amount', 150)
            ->assertJsonPath('totals.total_commission_amount', 15)
            ->assertJsonPath('totals.total_collected_amount', 100)
            ->assertJsonPath('totals.total_remaining_amount', 50)
            ->assertJsonPath('rows.0.delivery_agent_id', $deliveryAgent->id)
            ->assertJsonPath('rows.0.orders_count', 2);
    }

    public function test_deactivate_endpoint_marks_delivery_agent_inactive_without_deleting_it(): void
    {
        $user = User::factory()->create();
        $deliveryAgent = DeliveryAgent::create([
            'name' => 'Uber Eats',
            'type' => 'platform',
            'phone' => '0600000004',
            'platform_name' => 'Uber Eats',
            'commission_type' => 'fixed',
            'commission_value' => 12,
            'status' => 'active',
            'active' => true,
        ]);

        $response = $this->actingAs($user)->postJson("/api/delivery-agents/{$deliveryAgent->id}/deactivate");

        $response->assertOk()
            ->assertJsonPath('message', 'Livreur désactivé avec succès.')
            ->assertJsonPath('delivery_agent.id', $deliveryAgent->id)
            ->assertJsonPath('delivery_agent.status', 'inactive')
            ->assertJsonPath('delivery_agent.active', false);

        $this->assertDatabaseHas('delivery_agents', [
            'id' => $deliveryAgent->id,
            'status' => 'inactive',
            'active' => false,
        ]);
    }

    public function test_destroy_endpoint_deletes_delivery_agent_record(): void
    {
        $user = User::factory()->create();
        $deliveryAgent = DeliveryAgent::create([
            'name' => 'Glovo',
            'type' => 'platform',
            'phone' => '0600000005',
            'platform_name' => 'Glovo',
            'commission_type' => 'fixed',
            'commission_value' => 15,
            'status' => 'active',
            'active' => true,
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/delivery-agents/{$deliveryAgent->id}");

        $response->assertOk()
            ->assertJsonPath('message', 'Livreur supprimé avec succès.')
            ->assertJsonPath('delivery_agent.id', $deliveryAgent->id);

        $this->assertDatabaseMissing('delivery_agents', [
            'id' => $deliveryAgent->id,
        ]);
    }

    private function createSale(array $overrides = []): Sale
    {
        $total = $overrides['total'] ?? 200;
        $customer = Customer::create([
            'name' => 'Client livraison',
            'phone' => '0600000099',
            'is_active' => true,
        ]);

        return Sale::create(array_merge([
            'reference' => 'TRX-LIV-' . uniqid(),
            'user_id' => User::factory()->create()->id,
            'customer_id' => $customer->id,
            'subtotal' => $total,
            'discount_amount' => 0,
            'discount_percent' => 0,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'total' => $total,
            'status' => 'pending',
            'order_status' => 'confirmee',
            'payment_status' => 'unpaid',
            'payment_status_code' => 'to_pay',
            'delivery_mode' => 'pickup',
            'origin' => 'livraison',
        ], $overrides));
    }
}
