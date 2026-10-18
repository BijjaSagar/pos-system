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
        $superAdmin = User::updateOrCreate(
            ['email' => 'acnooteam@gmail.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('112233'),
                'role' => 'super_admin',
                'phone' => '+1234567890',
                'is_active' => true,
            ]
        );

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

        // 4. Default Store / Shop (NesCafe Solapur)
        $shopId = DB::table('shops')->insertGetId([
            'name' => 'NesCafe Solapur',
            'category_id' => 5, // Restaurant & Cafe
            'plan_id' => $enterprisePlanId,
            'plan_expires_at' => now()->addYears(10),
            'phone' => '+91 98220 12345',
            'email' => 'store@pospro.app',
            'address' => 'Station Road, Solapur, Maharashtra 413001',
            'currency_symbol' => '₹',
            'currency_code' => 'INR',
            'currency_position' => 'prefix',
            'tax_number' => '27AAECN1234F1Z8',
            'logo_url' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=200&auto=format&fit=crop&q=80',
            'primary_color' => '#D97706',
            'thermal_paper_size' => 'mm58',
            'receipt_header' => "☕ NesCafe Solapur - Fresh Brews & Bites\nGSTIN: 27AAECN1234F1Z8",
            'receipt_footer' => "Thank you for visiting NesCafe!\nFollow us @nescafe_solapur | Free Wi-Fi: NesCafe_Guest",
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5. Store Owner, Manager, Employee, and Cashier Users
        $storeOwner = User::updateOrCreate(
            ['email' => 'store@pospro.app'],
            [
                'name' => 'Store Owner',
                'password' => Hash::make('112233'),
                'role' => 'store_owner',
                'shop_id' => $shopId,
                'phone' => '+91 98220 12345',
                'is_active' => true,
            ]
        );

        $manager = User::updateOrCreate(
            ['email' => 'david.manager@pospro.app'],
            [
                'name' => 'David Miller (Manager)',
                'password' => Hash::make('112233'),
                'role' => 'manager',
                'shop_id' => $shopId,
                'phone' => '+91 98220 54321',
                'is_active' => true,
            ]
        );

        $employee = User::updateOrCreate(
            ['email' => 'carlos.employee@pospro.app'],
            [
                'name' => 'Carlos Gomez (Employee)',
                'password' => Hash::make('112233'),
                'role' => 'employee',
                'shop_id' => $shopId,
                'phone' => '+91 98220 98765',
                'is_active' => true,
            ]
        );

        $cashier = User::updateOrCreate(
            ['email' => 'cashier@pospro.app'],
            [
                'name' => 'Emma Cashier',
                'password' => Hash::make('112233'),
                'role' => 'cashier',
                'shop_id' => $shopId,
                'phone' => '+91 98220 11223',
                'is_active' => true,
            ]
        );

        $nescafeManager = User::updateOrCreate(
            ['email' => 'nescafe@pospro.app'],
            [
                'name' => 'NesCafe Solapur Manager',
                'password' => Hash::make('112233'),
                'role' => 'store_owner',
                'shop_id' => $shopId,
                'phone' => '+91 98220 12345',
                'is_active' => true,
            ]
        );

        $barista = User::updateOrCreate(
            ['email' => 'barista@pospro.app'],
            [
                'name' => 'Solapur Barista Cashier',
                'password' => Hash::make('112233'),
                'role' => 'cashier',
                'shop_id' => $shopId,
                'phone' => '+91 98220 44556',
                'is_active' => true,
            ]
        );

        // 6. Default Warehouse
        $warehouseId = DB::table('warehouses')->insertGetId([
            'shop_id' => $shopId,
            'name' => 'Main Cafe Counter & Kitchen',
            'phone' => '+91 98220 12345',
            'address' => 'Station Road, Solapur',
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 7. Master Categories, Brands, Units
        $catHot = DB::table('categories')->insertGetId([
            'shop_id' => $shopId,
            'name' => 'Hot Coffees & Tea',
            'code' => 'HOT-BEV',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $catCold = DB::table('categories')->insertGetId([
            'shop_id' => $shopId,
            'name' => 'Cold Coffees & Frappes',
            'code' => 'COLD-COF',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $catSandwich = DB::table('categories')->insertGetId([
            'shop_id' => $shopId,
            'name' => 'Burgers & Sandwiches',
            'code' => 'BURG-SND',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $catSnacks = DB::table('categories')->insertGetId([
            'shop_id' => $shopId,
            'name' => 'Snacks & Quick Bites',
            'code' => 'SNACKS',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $brandNescafe = DB::table('brands')->insertGetId(['shop_id' => $shopId, 'name' => 'NesCafe', 'created_at' => now(), 'updated_at' => now()]);
        $brandFresh = DB::table('brands')->insertGetId(['shop_id' => $shopId, 'name' => 'Kitchen Fresh', 'created_at' => now(), 'updated_at' => now()]);

        $unitCup = DB::table('units')->insertGetId(['shop_id' => $shopId, 'name' => 'Cup', 'short_name' => 'Cup', 'created_at' => now(), 'updated_at' => now()]);
        $unitPlate = DB::table('units')->insertGetId(['shop_id' => $shopId, 'name' => 'Plate', 'short_name' => 'Plt', 'created_at' => now(), 'updated_at' => now()]);
        $unitPcs = DB::table('units')->insertGetId(['shop_id' => $shopId, 'name' => 'Pieces', 'short_name' => 'Pcs', 'created_at' => now(), 'updated_at' => now()]);

        // 8. Cafe Products (NesCafe Solapur Menu)
        $p1 = DB::table('products')->insertGetId([
            'shop_id' => $shopId,
            'category_id' => $catHot,
            'brand_id' => $brandNescafe,
            'unit_id' => $unitCup,
            'name' => 'Classic NesCafe Hot Coffee',
            'code' => 'COF-001',
            'purchase_price' => 15.00,
            'retailer_price' => 40.00,
            'dealer_price' => 40.00,
            'wholesale_price' => 40.00,
            'stock' => 200.00,
            'alert_quantity' => 20.00,
            'tax_rate' => 5.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $p2 = DB::table('products')->insertGetId([
            'shop_id' => $shopId,
            'category_id' => $catHot,
            'brand_id' => $brandNescafe,
            'unit_id' => $unitCup,
            'name' => 'Special Cappuccino with Foam',
            'code' => 'COF-002',
            'purchase_price' => 25.00,
            'retailer_price' => 70.00,
            'dealer_price' => 70.00,
            'wholesale_price' => 70.00,
            'stock' => 200.00,
            'alert_quantity' => 20.00,
            'tax_rate' => 5.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $p3 = DB::table('products')->insertGetId([
            'shop_id' => $shopId,
            'category_id' => $catHot,
            'brand_id' => $brandNescafe,
            'unit_id' => $unitCup,
            'name' => 'Cafe Mocha Dark Chocolate',
            'code' => 'COF-003',
            'purchase_price' => 35.00,
            'retailer_price' => 90.00,
            'dealer_price' => 90.00,
            'wholesale_price' => 90.00,
            'stock' => 200.00,
            'alert_quantity' => 20.00,
            'tax_rate' => 5.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $p4 = DB::table('products')->insertGetId([
            'shop_id' => $shopId,
            'category_id' => $catCold,
            'brand_id' => $brandNescafe,
            'unit_id' => $unitCup,
            'name' => 'Signature Thick Cold Coffee',
            'code' => 'COLD-001',
            'purchase_price' => 30.00,
            'retailer_price' => 80.00,
            'dealer_price' => 80.00,
            'wholesale_price' => 80.00,
            'stock' => 200.00,
            'alert_quantity' => 20.00,
            'tax_rate' => 5.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $p5 = DB::table('products')->insertGetId([
            'shop_id' => $shopId,
            'category_id' => $catCold,
            'brand_id' => $brandNescafe,
            'unit_id' => $unitCup,
            'name' => 'Cold Coffee with Ice Cream',
            'code' => 'COLD-002',
            'purchase_price' => 40.00,
            'retailer_price' => 110.00,
            'dealer_price' => 110.00,
            'wholesale_price' => 110.00,
            'stock' => 200.00,
            'alert_quantity' => 20.00,
            'tax_rate' => 5.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $p6 = DB::table('products')->insertGetId([
            'shop_id' => $shopId,
            'category_id' => $catCold,
            'brand_id' => $brandNescafe,
            'unit_id' => $unitCup,
            'name' => 'Oreo Crunch Frappe',
            'code' => 'COLD-003',
            'purchase_price' => 50.00,
            'retailer_price' => 130.00,
            'dealer_price' => 130.00,
            'wholesale_price' => 130.00,
            'stock' => 200.00,
            'alert_quantity' => 20.00,
            'tax_rate' => 5.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $p7 = DB::table('products')->insertGetId([
            'shop_id' => $shopId,
            'category_id' => $catSandwich,
            'brand_id' => $brandFresh,
            'unit_id' => $unitPlate,
            'name' => 'Cheese Chilli Toast Sandwich',
            'code' => 'SND-001',
            'purchase_price' => 35.00,
            'retailer_price' => 80.00,
            'dealer_price' => 80.00,
            'wholesale_price' => 80.00,
            'stock' => 200.00,
            'alert_quantity' => 20.00,
            'tax_rate' => 5.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $p8 = DB::table('products')->insertGetId([
            'shop_id' => $shopId,
            'category_id' => $catSandwich,
            'brand_id' => $brandFresh,
            'unit_id' => $unitPlate,
            'name' => 'Veg Grilled Paneer Sandwich',
            'code' => 'SND-002',
            'purchase_price' => 45.00,
            'retailer_price' => 110.00,
            'dealer_price' => 110.00,
            'wholesale_price' => 110.00,
            'stock' => 200.00,
            'alert_quantity' => 20.00,
            'tax_rate' => 5.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $p9 = DB::table('products')->insertGetId([
            'shop_id' => $shopId,
            'category_id' => $catSnacks,
            'brand_id' => $brandFresh,
            'unit_id' => $unitPlate,
            'name' => 'Crispy Salted French Fries',
            'code' => 'SNK-001',
            'purchase_price' => 30.00,
            'retailer_price' => 70.00,
            'dealer_price' => 70.00,
            'wholesale_price' => 70.00,
            'stock' => 200.00,
            'alert_quantity' => 20.00,
            'tax_rate' => 5.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $p10 = DB::table('products')->insertGetId([
            'shop_id' => $shopId,
            'category_id' => $catSnacks,
            'brand_id' => $brandFresh,
            'unit_id' => $unitPlate,
            'name' => 'Peri Peri Cheesy Fries',
            'code' => 'SNK-002',
            'purchase_price' => 45.00,
            'retailer_price' => 100.00,
            'dealer_price' => 100.00,
            'wholesale_price' => 100.00,
            'stock' => 200.00,
            'alert_quantity' => 20.00,
            'tax_rate' => 5.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 9. Parties: Customers & Suppliers
        $cWalkIn = DB::table('parties')->insertGetId([
            'shop_id' => $shopId,
            'type' => 'customer',
            'customer_tier' => 'retailer',
            'name' => 'Walk-In Customer',
            'phone' => '9999999999',
            'current_balance' => 0.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cCorporate = DB::table('parties')->insertGetId([
            'shop_id' => $shopId,
            'type' => 'customer',
            'customer_tier' => 'wholesaler',
            'name' => 'Solapur Tech Park Corporate Account',
            'phone' => '+91 98221 00000',
            'email' => 'catering@solapurtechpark.in',
            'address' => 'MIDC Area, Solapur',
            'opening_balance' => 0.00,
            'current_balance' => 850.00, // Due balance
            'credit_limit' => 10000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sMilkDairy = DB::table('parties')->insertGetId([
            'shop_id' => $shopId,
            'type' => 'supplier',
            'name' => 'Solapur Fresh Dairy & Bakery Suppliers',
            'phone' => '+91 98222 33445',
            'email' => 'orders@solapurdairy.in',
            'current_balance' => -2500.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 10. Sample Completed Sale (Thermal Receipt)
        $saleId = DB::table('sales')->insertGetId([
            'shop_id' => $shopId,
            'warehouse_id' => $warehouseId,
            'party_id' => $cWalkIn,
            'user_id' => $cashier->id,
            'invoice_number' => 'INV-20261018-001',
            'sale_date' => now(),
            'subtotal' => 150.00,
            'discount_amount' => 0.00,
            'tax_amount' => 7.50,
            'total_amount' => 157.50,
            'paid_amount' => 160.00,
            'due_amount' => 0.00,
            'profit_amount' => 92.50,
            'payment_method' => 'cash',
            'status' => 'completed',
            'note' => 'Thermal receipt printed on 58mm at counter',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('sale_items')->insert([
            [
                'sale_id' => $saleId,
                'product_id' => $p1,
                'product_name' => 'Classic NesCafe Hot Coffee',
                'quantity' => 2,
                'purchase_price' => 15.00,
                'unit_price' => 40.00,
                'subtotal' => 80.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sale_id' => $saleId,
                'product_id' => $p2,
                'product_name' => 'Special Cappuccino with Foam',
                'quantity' => 1,
                'purchase_price' => 25.00,
                'unit_price' => 70.00,
                'subtotal' => 70.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
