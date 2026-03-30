<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalePaymentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_immediate_confirmed_payment_marks_sale_as_paye(): void
    {
        $user = User::factory()->create();
        $sale = $this->createSale();

        $response = $this->actingAs($user)->postJson("/api/sales/{$sale->id}/payments", [
            'payment_type' => 'cash',
            'amount' => 720,
            'received_amount' => 720,
        ]);

        $response->assertCreated()
            ->assertJsonPath('sale.payment_status_code', 'paid')
            ->assertJsonPath('sale.payment_status_label', 'Payé')
            ->assertJsonPath('sale.payment_summary.paid_confirmed_amount', 720)
            ->assertJsonPath('sale.payment_summary.pending_collection_amount', 0)
            ->assertJsonPath('sale.payment_summary.remaining_amount', 0)
            ->assertJsonPath('payment.workflow_status_code', 'paid')
            ->assertJsonPath('payment.workflow_status_label', 'Payé');
    }

    public function test_partial_immediate_payment_without_deferred_balance_stays_a_payer(): void
    {
        $user = User::factory()->create();
        $sale = $this->createSale();

        $response = $this->actingAs($user)->postJson("/api/sales/{$sale->id}/payments", [
            'payment_type' => 'cash',
            'amount' => 100,
            'received_amount' => 100,
        ]);

        $response->assertCreated()
            ->assertJsonPath('sale.payment_status_code', 'to_pay')
            ->assertJsonPath('sale.payment_status_label', 'À payer')
            ->assertJsonPath('sale.payment_summary.paid_confirmed_amount', 100)
            ->assertJsonPath('sale.payment_summary.pending_collection_amount', 0)
            ->assertJsonPath('sale.payment_summary.remaining_amount', 620);
    }

    public function test_decimal_mixed_payments_can_cover_the_exact_remaining_amount_without_false_overpay(): void
    {
        $user = User::factory()->create();
        $sale = $this->createSale(total: 100.00);

        $firstPayment = $this->actingAs($user)->postJson("/api/sales/{$sale->id}/payments", [
            'payment_type' => 'cash',
            'amount' => 33.33,
            'received_amount' => 33.33,
        ]);

        $firstPayment->assertCreated()
            ->assertJsonPath('sale.payment_summary.remaining_amount', 66.67);

        $secondPayment = $this->actingAs($user)->postJson("/api/sales/{$sale->id}/payments", [
            'payment_type' => 'card',
            'amount' => 66.67,
            'transaction_number' => 'TX-66-67',
        ]);

        $secondPayment->assertCreated()
            ->assertJsonPath('sale.payment_status_code', 'paid')
            ->assertJsonPath('sale.payment_summary.remaining_amount', 0);
    }

    public function test_deferred_payment_covering_remaining_balance_marks_sale_as_a_encaisser_then_encaisse(): void
    {
        $user = User::factory()->create();
        $sale = $this->createSale();

        $cashResponse = $this->actingAs($user)->postJson("/api/sales/{$sale->id}/payments", [
            'payment_type' => 'cash',
            'amount' => 100,
            'received_amount' => 100,
        ]);

        $cashResponse->assertCreated();

        $deferredResponse = $this->actingAs($user)->postJson("/api/sales/{$sale->id}/payments", [
            'payment_type' => 'cheque',
            'amount' => 620,
            'transaction_number' => 'CHQ-001',
            'issue_date' => '2026-03-13',
            'due_date' => '2026-03-20',
            'notes' => 'Cheque client',
        ]);

        $deferredResponse->assertCreated()
            ->assertJsonPath('sale.payment_status_code', 'to_collect')
            ->assertJsonPath('sale.payment_status_label', 'À encaisser')
            ->assertJsonPath('sale.payment_summary.paid_confirmed_amount', 100)
            ->assertJsonPath('sale.payment_summary.pending_collection_amount', 620)
            ->assertJsonPath('sale.payment_summary.remaining_amount', 0)
            ->assertJsonPath('payment.workflow_status_code', 'to_collect')
            ->assertJsonPath('payment.workflow_status_label', 'À encaisser');

        $paymentId = $deferredResponse->json('payment.id');

        $collectedResponse = $this->actingAs($user)->postJson("/api/payment-collections/{$paymentId}/status", [
            'status' => 'collected',
            'notes' => 'Cheque encaissé',
        ]);

        $collectedResponse->assertOk()
            ->assertJsonPath('payment.workflow_status_code', 'collected')
            ->assertJsonPath('payment.workflow_status_label', 'Encaissé')
            ->assertJsonPath('payment.sale.payment_status_code', 'collected')
            ->assertJsonPath('payment.sale.payment_status_label', 'Encaissé');
    }

    public function test_failed_deferred_payment_marks_sale_as_impaye(): void
    {
        $user = User::factory()->create();
        $sale = $this->createSale();

        $deferredResponse = $this->actingAs($user)->postJson("/api/sales/{$sale->id}/payments", [
            'payment_type' => 'cheque',
            'amount' => 720,
            'transaction_number' => 'CHQ-002',
            'issue_date' => '2026-03-13',
            'due_date' => '2026-03-20',
            'notes' => 'Cheque client',
        ]);

        $paymentId = $deferredResponse->json('payment.id');

        $failedResponse = $this->actingAs($user)->postJson("/api/payment-collections/{$paymentId}/status", [
            'status' => 'cancelled',
            'notes' => 'Cheque rejeté',
        ]);

        $failedResponse->assertOk()
            ->assertJsonPath('payment.workflow_status_code', 'unpaid')
            ->assertJsonPath('payment.workflow_status_label', 'Impayé')
            ->assertJsonPath('payment.sale.payment_status_code', 'unpaid')
            ->assertJsonPath('payment.sale.payment_status_label', 'Impayé')
            ->assertJsonPath('payment.sale.payment_summary.remaining_amount', 720);
    }

    public function test_suivi_encaissement_lists_sales_needing_follow_up_including_pos_deferred_payments(): void
    {
        $user = User::factory()->create();

        $clientOrderUnpaid = $this->createSale(origin: 'menu_commande');
        $clientOrderDeferred = $this->createSale(origin: 'livraison');
        $clientOrderPaidImmediate = $this->createSale(origin: 'menu_commande');
        $clientOrderCollected = $this->createSale(origin: 'livraison');
        $posSale = $this->createSale(origin: 'pos');

        $this->actingAs($user)->postJson("/api/sales/{$clientOrderUnpaid->id}/payments", [
            'payment_type' => 'cash',
            'amount' => 100,
            'received_amount' => 100,
        ])->assertCreated();

        $this->actingAs($user)->postJson("/api/sales/{$clientOrderDeferred->id}/payments", [
            'payment_type' => 'cheque',
            'amount' => 720,
            'transaction_number' => 'CHQ-100',
            'issue_date' => '2026-03-13',
            'due_date' => '2026-03-20',
        ])->assertCreated();

        $this->actingAs($user)->postJson("/api/sales/{$clientOrderPaidImmediate->id}/payments", [
            'payment_type' => 'cash',
            'amount' => 720,
            'received_amount' => 720,
        ])->assertCreated();

        $this->actingAs($user)->postJson("/api/sales/{$clientOrderCollected->id}/payments", [
            'payment_type' => 'cheque',
            'amount' => 720,
            'transaction_number' => 'CHQ-200',
            'issue_date' => '2026-03-13',
            'due_date' => '2026-03-20',
        ])->assertCreated();

        $paymentId = Payment::where('sale_id', $clientOrderCollected->id)->firstOrFail()->id;
        $this->actingAs($user)->postJson("/api/payment-collections/{$paymentId}/status", [
            'status' => 'collected',
        ])->assertOk();

        $this->actingAs($user)->postJson("/api/sales/{$posSale->id}/payments", [
            'payment_type' => 'cheque',
            'amount' => 720,
            'transaction_number' => 'CHQ-POS',
            'issue_date' => '2026-03-13',
            'due_date' => '2026-03-20',
        ])->assertCreated();

        $response = $this->actingAs($user)->getJson('/api/payment-collections/deferred');

        $response->assertOk();

        $items = $response->json();
        $saleIds = collect($items)->pluck('sale_id')->all();

        $this->assertContains($clientOrderUnpaid->id, $saleIds);
        $this->assertContains($clientOrderDeferred->id, $saleIds);
        $this->assertNotContains($clientOrderPaidImmediate->id, $saleIds);
        $this->assertNotContains($clientOrderCollected->id, $saleIds);
        $this->assertContains($posSale->id, $saleIds);

        $deferredItem = collect($items)->firstWhere('sale_id', $clientOrderDeferred->id);
        $unpaidItem = collect($items)->firstWhere('sale_id', $clientOrderUnpaid->id);
        $posDeferredItem = collect($items)->firstWhere('sale_id', $posSale->id);

        $this->assertSame('to_collect', $deferredItem['workflow_status_code']);
        $this->assertSame('cheque', $deferredItem['payment_type']);
        $this->assertNotNull($deferredItem['payment_id']);
        $this->assertSame('to_pay', $unpaidItem['workflow_status_code']);
        $this->assertSame('balance_due', $unpaidItem['payment_type']);
        $this->assertSame('to_collect', $posDeferredItem['workflow_status_code']);
        $this->assertSame('cheque', $posDeferredItem['payment_type']);
        $this->assertNotNull($posDeferredItem['payment_id']);
        $this->assertSame($posSale->reference, $posDeferredItem['sale_reference_display']);
    }

    private function createSale(string $origin = 'menu_commande', float $total = 720): Sale
    {
        $customer = Customer::create([
            'name' => 'Client test',
            'phone' => '0600000000',
            'is_active' => true,
        ]);

        return Sale::create([
            'reference' => 'TRX-TST-' . uniqid(),
            'user_id' => User::factory()->create()->id,
            'customer_id' => $customer->id,
            'subtotal' => $total,
            'discount_amount' => 0,
            'discount_percent' => 0,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'total' => $total,
            'status' => 'pending',
            'order_status' => 'livree',
            'payment_status' => 'unpaid',
            'payment_status_code' => 'to_pay',
            'delivery_mode' => 'pickup',
            'origin' => $origin,
        ]);
    }
}
