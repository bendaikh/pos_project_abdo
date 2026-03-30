<?php

namespace Tests\Feature;

use App\Models\CustomList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomFinanceListsTest extends TestCase
{
    use RefreshDatabase;

    public function test_tax_and_discount_lists_store_requested_fields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->putJson('/api/custom-lists/taxes', [
            'is_active' => true,
            'items' => [
                [
                    'label' => 'TVA',
                    'value' => 'TVA',
                    'is_active' => true,
                    'sort_order' => 1,
                    'tax_type' => 'percentage',
                    'tax_rate' => 20,
                    'tax_is_default' => true,
                ],
                [
                    'label' => 'Écotaxe',
                    'value' => 'Écotaxe',
                    'is_active' => false,
                    'sort_order' => 2,
                    'tax_type' => 'fixed',
                    'tax_rate' => 5,
                    'tax_is_default' => false,
                ],
            ],
        ])->assertOk()
            ->assertJsonPath('items.0.label', 'TVA')
            ->assertJsonPath('items.0.tax_type', 'percentage')
            ->assertJsonPath('items.0.tax_rate', 20)
            ->assertJsonPath('items.0.tax_is_default', true)
            ->assertJsonPath('items.1.tax_type', 'fixed')
            ->assertJsonPath('items.1.is_active', false);

        $this->actingAs($user)->putJson('/api/custom-lists/remises', [
            'is_active' => true,
            'items' => [
                [
                    'label' => 'Fidélité',
                    'value' => 'Fidélité',
                    'is_active' => true,
                    'sort_order' => 1,
                    'discount_type' => 'percentage',
                    'discount_value' => 10,
                    'discount_limit' => 20,
                ],
                [
                    'label' => 'Bon achat',
                    'value' => 'Bon achat',
                    'is_active' => true,
                    'sort_order' => 2,
                    'discount_type' => 'fixed',
                    'discount_value' => 30,
                    'discount_limit' => 100,
                ],
            ],
        ])->assertOk()
            ->assertJsonPath('items.0.discount_type', 'percentage')
            ->assertJsonPath('items.0.discount_value', 10)
            ->assertJsonPath('items.0.discount_limit', 20)
            ->assertJsonPath('items.1.discount_type', 'fixed');

        $tax = CustomList::where('name', 'taxes')->firstOrFail()
            ->items()
            ->where('label', 'TVA')
            ->firstOrFail();

        $discount = CustomList::where('name', 'remises')->firstOrFail()
            ->items()
            ->where('label', 'Fidélité')
            ->firstOrFail();

        $this->assertSame('percentage', $tax->metadata['tax_type']);
        $this->assertEquals(20.0, $tax->metadata['tax_rate']);
        $this->assertTrue($tax->metadata['tax_is_default']);
        $this->assertSame('percentage', $discount->metadata['discount_type']);
        $this->assertEquals(10.0, $discount->metadata['discount_value']);
        $this->assertEquals(20.0, $discount->metadata['discount_limit']);
    }

    public function test_expense_custom_list_can_store_category_type_and_recurrence(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/custom-lists/depenses', [
            'is_active' => true,
            'items' => [
                [
                    'label' => 'Loyer local',
                    'value' => 'Loyer local',
                    'is_active' => true,
                    'sort_order' => 1,
                    'expense_category' => 'Charges fixes',
                    'expense_type' => 'fixed',
                    'expense_is_recurring' => true,
                    'expense_frequency' => 'monthly',
                ],
                [
                    'label' => 'Transport livraison',
                    'value' => 'Transport livraison',
                    'is_active' => true,
                    'sort_order' => 2,
                    'expense_category' => 'Logistique',
                    'expense_type' => 'variable',
                    'expense_is_recurring' => false,
                    'expense_frequency' => null,
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('name', 'depenses')
            ->assertJsonPath('items.0.expense_category', 'Charges fixes')
            ->assertJsonPath('items.0.expense_type', 'fixed')
            ->assertJsonPath('items.0.expense_is_recurring', true)
            ->assertJsonPath('items.0.expense_frequency', 'monthly')
            ->assertJsonPath('items.1.expense_type', 'variable')
            ->assertJsonPath('items.1.expense_is_recurring', false);

        $expense = CustomList::where('name', 'depenses')->firstOrFail()
            ->items()
            ->where('label', 'Loyer local')
            ->firstOrFail();

        $this->assertSame('Charges fixes', $expense->metadata['expense_category']);
        $this->assertSame('fixed', $expense->metadata['expense_type']);
        $this->assertTrue($expense->metadata['expense_is_recurring']);
        $this->assertSame('monthly', $expense->metadata['expense_frequency']);
    }

    public function test_expense_category_custom_list_can_store_separate_categories(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/custom-lists/categories_depenses', [
            'is_active' => true,
            'items' => [
                [
                    'label' => 'Charges fixes',
                    'value' => 'Charges fixes',
                    'is_active' => true,
                    'sort_order' => 1,
                ],
                [
                    'label' => 'Logistique',
                    'value' => 'Logistique',
                    'is_active' => false,
                    'sort_order' => 2,
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('name', 'categories_depenses')
            ->assertJsonPath('items.0.label', 'Charges fixes')
            ->assertJsonPath('items.1.is_active', false);

        $this->assertDatabaseHas('custom_lists', [
            'name' => 'categories_depenses',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('custom_list_items', [
            'label' => 'Charges fixes',
            'is_active' => true,
        ]);
    }

    public function test_recurring_expense_requires_frequency(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->putJson('/api/custom-lists/depenses', [
            'is_active' => true,
            'items' => [
                [
                    'label' => 'Abonnement logiciel',
                    'value' => 'Abonnement logiciel',
                    'is_active' => true,
                    'sort_order' => 1,
                    'expense_category' => 'Outils',
                    'expense_type' => 'fixed',
                    'expense_is_recurring' => true,
                ],
            ],
        ])->assertStatus(422)
            ->assertJsonValidationErrors('items.0.expense_frequency');
    }
}
