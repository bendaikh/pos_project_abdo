<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleTicketMetadataTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_store_accepts_ticket_metadata_and_appointment_datetime(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'name' => 'Client Ticket',
            'phone' => '0612345678',
            'is_active' => true,
        ]);
        $article = Article::create([
            'name' => 'Gateau',
            'sell_price' => 120,
            'unit' => 'piece',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->postJson('/api/sales', [
            'customer_id' => $customer->id,
            'origin' => 'menu_commande',
            'ticket_type' => 'commande',
            'ticket_name' => 'Commande mariage',
            'ticket_group' => 'Evenements',
            'appointment_at' => '2026-03-20 15:30:00',
            'delivery_mode' => 'pickup',
            'order_status' => 'en_preparation',
            'notes' => 'Preparation atelier',
            'items' => [
                [
                    'article_id' => $article->id,
                    'quantity' => 2,
                    'unit_price' => 120,
                    'variant_price' => 0,
                    'options_price' => 0,
                    'discount_amount' => 0,
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('ticket_type', 'commande')
            ->assertJsonPath('ticket_name', 'Commande mariage')
            ->assertJsonPath('ticket_group', 'Evenements');

        $this->assertNotNull($response->json('appointment_at'));
        $this->assertStringStartsWith('2026-03-20', (string) $response->json('pickup_date'));

        $sale = Sale::firstOrFail();

        $this->assertSame('commande', $sale->ticket_type);
        $this->assertSame('Commande mariage', $sale->ticket_name);
        $this->assertSame('Evenements', $sale->ticket_group);
        $this->assertSame('2026-03-20', optional($sale->pickup_date)->format('Y-m-d'));
        $this->assertSame('2026-03-20 15:30:00', optional($sale->appointment_at)->format('Y-m-d H:i:s'));
    }
}
