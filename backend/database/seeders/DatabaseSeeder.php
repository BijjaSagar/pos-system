<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Super Admin Account (Matching credentials provided by user)
        $superAdmin = User::create([
            'name' => 'Super Administrator',
            'email' => 'acnooteam@gmail.com',
            'password' => Hash::make('112233'),
            'role' => 'super_admin',
            'phone' => '+1234567890',
            'is_active' => true,
        ]);

        // 2. Default SaaS Subscription Plans
        $freePlanId = DB::table('plans')->insertGetId([
            'name' => 'Free Trial',
            'slug' => 'free-trial',
            'regular_price' => 0.00,
            'offer_price' => 0.00,
            'duration_days' => 14,
            'product_limit' => 50,
            'invoice_limit' => 100,
            'user_limit' => 2,
            'warehouse_limit' => 1,
            'hrm_enabled' => false,
            'whatsapp_marketing_enabled' => false,
            'is_active' => true,
            'description' => '14-day full featured trial for new businesses',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $proPlanId = DB::table('plans')->insertGetId([
            'name' => 'Professional Monthly',
            'slug' => 'pro-monthly',
            'regular_price' => 29.00,
            'offer_price' => 19.00,
            'duration_days' => 30,
            'product_limit' => 500,
            'invoice_limit' => 2000,
            'user_limit' => 5,
            'warehouse_limit' => 2,
            'hrm_enabled' => true,
            'whatsapp_marketing_enabled' => true,
            'is_active' => true,
            'description' => 'Ideal for growing retail stores and small chains',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $enterprisePlanId = DB::table('plans')->insertGetId([
            'name' => 'Enterprise Lifetime',
            'slug' => 'enterprise-lifetime',
            'regular_price' => 299.00,
            'offer_price' => 199.00,
            'duration_days' => 36500,
            'product_limit' => 10000,
            'invoice_limit' => 100000,
            'user_limit' => 20,
            'warehouse_limit' => 10,
            'hrm_enabled' => true,
            'whatsapp_marketing_enabled' => true,
            'is_active' => true,
            'description' => 'Lifetime unlimited access with all advanced modules',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Shop Categories
        $categories = [
            ['name' => 'Electronics & Gadgets', 'icon' => 'computer'],
            ['name' => 'Grocery & Supermarket', 'icon' => 'shopping_cart'],
            ['name' => 'Clothing & Fashion', 'icon' => 'checkroom'],
            ['name' => 'Pharmacy & Healthcare', 'icon' => 'local_pharmacy'],
            ['name' => 'Restaurant & Cafe', 'icon' => 'restaurant'],
        ];
        foreach ($categories as $cat) {
            DB::table('shop_categories')->insert(array_merge($cat, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // 4. Demo Store / Shop
        $shopId = DB::table('shops')->insertGetId([
            'name' => 'Acnoo Flagship Retail Store',
            'category_id' => 1,
            'plan_id' => $enterprisePlanId,
            'plan_expires_at' => now()->addYears(10),
            'phone' => '+1 (555) 019-2834',
            'email' => 'store@pospro.app',
            'address' => '742 Evergreen Terrace, Springfield',
            'currency_symbol' => '$',
            'currency_code' => 'USD',
            'tax_number' => 'US-VAT-9988221',
            'thermal_paper_size' => 'mm58',
            'receipt_header' => 'Welcome to Acnoo POS Store!',
            'receipt_footer' => 'Goods once sold are returnable within 7 days with receipt. Thank you!',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5. Store Owner & Cashier Users
        $storeOwner = User::create([
            'name' => 'John Store Manager',
            'email' => 'store@pospro.app',
            'password' => Hash::make('112233'),
            'role' => 'store_owner',
            'shop_id' => $shopId,
            'phone' => '+1 (555) 019-2834',
            'is_active' => true,
        ]);

        $cashier = User::create([
            'name' => 'Emma Cashier',
            'email' => 'cashier@pospro.app',
            'password' => Hash::make('112233'),
            'role' => 'cashier',
            'shop_id' => $shopId,
            'phone' => '+1 (555) 019-5566',
            'is_active' => true,
        ]);

        // 6. Default Warehouse
        $warehouseId = DB::table('warehouses')->insertGetId([
            'shop_id' => $shopId,
            'name' => 'Main Outlet Warehouse',
            'phone' => '+1 (555) 019-2834',
            'address' => 'In-store storage rack A1-B4',
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 7. Master Categories, Brands, Units
        $catElectronics = DB::table('categories')->insertGetId([
            'shop_id' => $shopId,
            'name' => 'Smartphones & Accessories',
            'code' => 'CAT-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $catAudio = DB::table('categories')->insertGetId([
            'shop_id' => $shopId,
            'name' => 'Audio & Headphones',
            'code' => 'CAT-02',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $brandApple = DB::table('brands')->insertGetId(['shop_id' => $shopId, 'name' => 'Apple', 'created_at' => now(), 'updated_at' => now()]);
        $brandSony = DB::table('brands')->insertGetId(['shop_id' => $shopId, 'name' => 'Sony', 'created_at' => now(), 'updated_at' => now()]);
        $brandAnker = DB::table('brands')->insertGetId(['shop_id' => $shopId, 'name' => 'Anker', 'created_at' => now(), 'updated_at' => now()]);

        $unitPcs = DB::table('units')->insertGetId(['shop_id' => $shopId, 'name' => 'Pieces', 'short_name' => 'Pcs', 'created_at' => now(), 'updated_at' => now()]);
        $unitBox = DB::table('units')->insertGetId(['shop_id' => $shopId, 'name' => 'Box', 'short_name' => 'Box', 'created_at' => now(), 'updated_at' => now()]);

        // 8. Sample Products with Multi-Tier Pricing (Retailer, Dealer, Wholesaler)
        $p1 = DB::table('products')->insertGetId([
            'shop_id' => $shopId,
            'category_id' => $catAudio,
            'brand_id' => $brandSony,
            'unit_id' => $unitPcs,
            'name' => 'Sony WH-1000XM5 Wireless Headphones',
            'code' => '890103001',
            'purchase_price' => 240.00,
            'retailer_price' => 349.00,
            'dealer_price' => 310.00,
            'wholesale_price' => 290.00,
            'stock' => 25.00,
            'alert_quantity' => 5.00,
            'tax_rate' => 5.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $p2 = DB::table('products')->insertGetId([
            'shop_id' => $shopId,
            'category_id' => $catElectronics,
            'brand_id' => $brandAnker,
            'unit_id' => $unitPcs,
            'name' => 'Anker 65W Fast USB-C Charger',
            'code' => '890103002',
            'purchase_price' => 18.00,
            'retailer_price' => 39.99,
            'dealer_price' => 32.00,
            'wholesale_price' => 28.00,
            'stock' => 120.00,
            'alert_quantity' => 15.00,
            'tax_rate' => 5.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $p3 = DB::table('products')->insertGetId([
            'shop_id' => $shopId,
            'category_id' => $catElectronics,
            'brand_id' => $brandApple,
            'unit_id' => $unitPcs,
            'name' => 'Apple MagSafe Battery Pack',
            'code' => '890103003',
            'purchase_price' => 65.00,
            'retailer_price' => 99.00,
            'dealer_price' => 88.00,
            'wholesale_price' => 80.00,
            'stock' => 45.00,
            'alert_quantity' => 8.00,
            'tax_rate' => 5.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 9. Parties: Customers (Walk-In, Retailer, Wholesaler, Dealer) & Suppliers
        $cWalkIn = DB::table('parties')->insertGetId([
            'shop_id' => $shopId,
            'type' => 'customer',
            'customer_tier' => 'retailer',
            'name' => 'Walk-In Customer',
            'phone' => '0000000000',
            'current_balance' => 0.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cWholesale = DB::table('parties')->insertGetId([
            'shop_id' => $shopId,
            'type' => 'customer',
            'customer_tier' => 'wholesaler',
            'name' => 'Apex Electronics Wholesale LLC',
            'phone' => '+1 (555) 777-8899',
            'email' => 'orders@apexelectronics.com',
            'address' => 'Industrial District, Warehouse 9',
            'opening_balance' => 0.00,
            'current_balance' => 450.00, // Due balance
            'credit_limit' => 5000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sGlobal = DB::table('parties')->insertGetId([
            'shop_id' => $shopId,
            'type' => 'supplier',
            'name' => 'Global Distribution Hub',
            'phone' => '+1 (555) 234-5678',
            'email' => 'sales@globalhub.com',
            'current_balance' => -1200.00, // We owe them
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 10. Sample Completed Sale
        $saleId = DB::table('sales')->insertGetId([
            'shop_id' => $shopId,
            'warehouse_id' => $warehouseId,
            'party_id' => $cWalkIn,
            'user_id' => $cashier->id,
            'invoice_number' => 'INV-20261018-001',
            'sale_date' => now(),
            'subtotal' => 388.99,
            'discount_amount' => 10.00,
            'tax_amount' => 18.95,
            'total_amount' => 397.94,
            'paid_amount' => 400.00,
            'due_amount' => 0.00,
            'profit_amount' => 130.99,
            'payment_method' => 'cash',
            'status' => 'completed',
            'note' => 'Thermal receipt printed on 58mm',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('sale_items')->insert([
            [
                'sale_id' => $saleId,
                'product_id' => $p1,
                'product_name' => 'Sony WH-1000XM5 Wireless Headphones',
                'quantity' => 1,
                'purchase_price' => 240.00,
                'unit_price' => 349.00,
                'subtotal' => 349.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sale_id' => $saleId,
                'product_id' => $p2,
                'product_name' => 'Anker 65W Fast USB-C Charger',
                'quantity' => 1,
                'purchase_price' => 18.00,
                'unit_price' => 39.99,
                'subtotal' => 39.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
