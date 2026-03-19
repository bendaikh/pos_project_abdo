<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\CustomList;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomServiceModesTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_mode_custom_list_is_seeded_and_exposed_by_api(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/custom-lists/mode_de_service');

        $response->assertOk()
            ->assertJsonPath('name', 'mode_de_service')
            ->assertJsonPath('is_active', true)
            ->assertJsonPath('items.0.label', 'Sur place')
            ->assertJsonPath('items.1.label', 'Emporté')
            ->assertJsonPath('items.2.label', 'Livraison');
    }

    public function test_service_mode_custom_list_can_be_updated_with_order_and_activation(): void
    {
        $user = User::factory()->create();
        $list = CustomList::where('name', 'mode_de_service')->firstOrFail();
        $items = $list->items()->orderBy('sort_order')->get();

        $response = $this->actingAs($user)->putJson('/api/custom-lists/mode_de_service', [
            'is_active' => true,
            'items' => [
                [
                    'id' => $items[2]->id,
                    'label' => 'Livraison',
                    'is_active' => true,
                    'sort_order' => 1,
                ],
                [
                    'id' => $items[0]->id,
                    'label' => 'Sur place',
                    'is_active' => true,
                    'sort_order' => 2,
                ],
                [
                    'id' => $items[1]->id,
                    'label' => 'Emporté',
                    'is_active' => false,
                    'sort_order' => 3,
                ],
                [
                    'label' => 'Drive',
                    'is_active' => true,
                    'sort_order' => 4,
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('items.0.label', 'Livraison')
            ->assertJsonPath('items.2.is_active', false)
            ->assertJsonPath('items.3.label', 'Drive');

        $this->assertDatabaseHas('custom_list_items', [
            'list_id' => $list->id,
            'label' => 'Drive',
            'is_active' => true,
            'sort_order' => 4,
        ]);

        $this->assertDatabaseHas('custom_list_items', [
            'list_id' => $list->id,
            'label' => 'Emporté',
            'is_active' => false,
            'sort_order' => 3,
        ]);
    }

    public function test_sale_creation_persists_dynamic_service_mode_and_maps_legacy_operational_mode(): void
    {
        $user = User::factory()->create();
        $article = Article::create([
            'name' => 'Pizza',
            'sell_price' => 90,
            'unit' => 'piece',
            'is_active' => true,
        ]);

        $this->actingAs($user)->putJson('/api/custom-lists/mode_de_service', [
            'is_active' => true,
            'items' => [
                ['label' => 'Sur place', 'is_active' => true, 'sort_order' => 1],
                ['label' => 'Emporté', 'is_active' => true, 'sort_order' => 2],
                ['label' => 'Livraison', 'is_active' => true, 'sort_order' => 3],
                ['label' => 'Drive', 'is_active' => true, 'sort_order' => 4],
            ],
        ])->assertOk();

        $response = $this->actingAs($user)->postJson('/api/sales', [
            'origin' => 'menu_commande',
            'service_mode' => 'Drive',
            'order_status' => 'confirmee',
            'items' => [
                [
                    'article_id' => $article->id,
                    'quantity' => 1,
                    'unit_price' => 90,
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('service_mode', 'Drive')
            ->assertJsonPath('delivery_mode', 'pickup');

        $sale = Sale::firstOrFail();

        $this->assertSame('Drive', $sale->service_mode);
        $this->assertSame('pickup', $sale->delivery_mode);
    }

    public function test_sale_creation_maps_legacy_delivery_mode_codes_to_service_mode_labels(): void
    {
        $user = User::factory()->create();
        $article = Article::create([
            'name' => 'Tacos',
            'sell_price' => 45,
            'unit' => 'piece',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->postJson('/api/sales', [
            'origin' => 'menu_commande',
            'delivery_mode' => 'dine_in',
            'order_status' => 'confirmee',
            'items' => [
                [
                    'article_id' => $article->id,
                    'quantity' => 1,
                    'unit_price' => 45,
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('service_mode', 'Sur place')
            ->assertJsonPath('delivery_mode', 'dine_in');

        $sale = Sale::latest('id')->firstOrFail();
        $this->assertSame('Sur place', $sale->service_mode);
        $this->assertSame('dine_in', $sale->delivery_mode);
    }
}
