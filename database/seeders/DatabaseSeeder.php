<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Article;
use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('Admin@12345'),
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        // Create sample categories
        $categories = [
            ['name' => 'Fruits & Légumes', 'color' => '#22c55e', 'icon' => 'apple'],
            ['name' => 'Boissons', 'color' => '#3b82f6', 'icon' => 'cup'],
            ['name' => 'Boulangerie', 'color' => '#f59e0b', 'icon' => 'bread'],
            ['name' => 'Produits Ménagers', 'color' => '#8b5cf6', 'icon' => 'home'],
            ['name' => 'Snacks', 'color' => '#ef4444', 'icon' => 'cookie'],
            ['name' => 'Viandes', 'color' => '#dc2626', 'icon' => 'meat'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // Create sample articles
        $articles = [
            ['name' => 'Apple', 'sell_price' => 0.99, 'buy_price' => 0.50, 'category_id' => 1, 'unit' => 'lb', 'is_favorite' => true],
            ['name' => 'Bananas', 'sell_price' => 0.69, 'buy_price' => 0.35, 'category_id' => 1, 'unit' => 'lb', 'is_favorite' => true],
            ['name' => 'Broccoli', 'sell_price' => 1.50, 'buy_price' => 0.80, 'category_id' => 1, 'unit' => 'lb'],
            ['name' => 'Tomatoes', 'sell_price' => 1.00, 'buy_price' => 0.50, 'category_id' => 1, 'unit' => 'lb', 'manage_stock' => true, 'stock_quantity' => 3000],
            ['name' => 'Orange Juice', 'sell_price' => 3.50, 'buy_price' => 2.00, 'category_id' => 2, 'is_favorite' => true],
            ['name' => 'Yogurt', 'sell_price' => 1.50, 'buy_price' => 0.80, 'category_id' => 2],
            ['name' => 'Ground Beef', 'sell_price' => 5.00, 'buy_price' => 3.50, 'category_id' => 6, 'unit' => 'lb', 'manage_stock' => true, 'stock_quantity' => 1500],
            ['name' => 'Chicken Leg', 'sell_price' => 2.50, 'buy_price' => 1.50, 'category_id' => 6, 'unit' => 'lb'],
            ['name' => 'Eggs', 'sell_price' => 2.50, 'buy_price' => 1.50, 'category_id' => 1, 'unit' => 'doz'],
            ['name' => 'Jasmine Rice', 'sell_price' => 1.50, 'buy_price' => 0.80, 'category_id' => 1, 'unit' => 'lb'],
            ['name' => 'Spinach', 'sell_price' => 2.00, 'buy_price' => 1.00, 'category_id' => 1],
            ['name' => 'Toilet Paper', 'sell_price' => 6.99, 'buy_price' => 4.00, 'category_id' => 4],
            ['name' => 'Toothpaste', 'sell_price' => 4.00, 'buy_price' => 2.50, 'category_id' => 4],
            ['name' => 'All-Purpose Cleaner', 'sell_price' => 3.50, 'buy_price' => 2.00, 'category_id' => 4, 'is_favorite' => true],
        ];

        foreach ($articles as $article) {
            Article::create($article);
        }

        // Create sample customer
        Customer::create([
            'name' => 'Client Anonyme',
            'email' => null,
            'phone' => null,
        ]);

        Customer::create([
            'name' => 'Ahmed El Mansouri',
            'email' => 'ahmed@example.com',
            'phone' => '+212 600 000 000',
        ]);

        // Create default settings
        $settings = [
            // General
            ['key' => 'store_name', 'value' => 'GreenPOS', 'type' => 'string', 'group' => 'general'],
            ['key' => 'store_address', 'value' => '123 Main Street', 'type' => 'string', 'group' => 'general'],
            ['key' => 'store_phone', 'value' => '+212 600 000 000', 'type' => 'string', 'group' => 'general'],
            ['key' => 'store_country', 'value' => 'Morocco', 'type' => 'string', 'group' => 'general'],
            
            // Currency
            ['key' => 'currency_code', 'value' => 'DHS', 'type' => 'string', 'group' => 'currency'],
            ['key' => 'currency_symbol', 'value' => 'DHS', 'type' => 'string', 'group' => 'currency'],
            ['key' => 'currency_position', 'value' => 'after', 'type' => 'string', 'group' => 'currency'],
            
            // Tax
            ['key' => 'tax_enabled', 'value' => 'true', 'type' => 'boolean', 'group' => 'tax'],
            ['key' => 'tax_rate', 'value' => '20', 'type' => 'number', 'group' => 'tax'],
            ['key' => 'tax_name', 'value' => 'TVA', 'type' => 'string', 'group' => 'tax'],
            
            // Receipt
            ['key' => 'receipt_header', 'value' => 'Merci pour votre achat!', 'type' => 'string', 'group' => 'receipt'],
            ['key' => 'receipt_footer', 'value' => 'À bientôt!', 'type' => 'string', 'group' => 'receipt'],
            ['key' => 'receipt_show_logo', 'value' => 'true', 'type' => 'boolean', 'group' => 'receipt'],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
