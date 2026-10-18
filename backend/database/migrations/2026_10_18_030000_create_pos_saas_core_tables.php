<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Subscription Plans (SaaS)
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('regular_price', 12, 2)->default(0.00);
            $table->decimal('offer_price', 12, 2)->default(0.00);
            $table->integer('duration_days')->default(30); // 30, 365, or 36500 for lifetime
            $table->integer('product_limit')->default(100);
            $table->integer('invoice_limit')->default(500);
            $table->integer('user_limit')->default(3);
            $table->integer('warehouse_limit')->default(1);
            $table->boolean('hrm_enabled')->default(true);
            $table->boolean('whatsapp_marketing_enabled')->default(true);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Shop Categories
        Schema::create('shop_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Shops (Tenants)
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('category_id')->nullable()->constrained('shop_categories')->nullOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->timestamp('plan_expires_at')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('currency_symbol', 10)->default('$');
            $table->string('currency_code', 10)->default('USD');
            $table->string('tax_number')->nullable(); // VAT / GST
            $table->string('logo_url')->nullable();
            $table->enum('thermal_paper_size', ['mm58', 'mm80'])->default('mm58');
            $table->text('receipt_header')->nullable();
            $table->text('receipt_footer')->nullable();
            $table->enum('status', ['active', 'suspended', 'expired'])->default('active');
            $table->timestamps();
        });

        // Add tenant relationship and roles to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('shop_id')->nullable()->after('id')->constrained('shops')->cascadeOnDelete();
            $table->string('role')->default('cashier')->after('email'); // super_admin, store_owner, manager, cashier
            $table->string('phone')->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('phone');
            $table->json('permissions')->nullable()->after('is_active');
        });

        // 4. Warehouses
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // 5. Product Categories, Brands, Units
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->string('name'); // Pcs, Kg, Meter, Box
            $table->string('short_name');
            $table->timestamps();
        });

        // 6. Products
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('name');
            $table->string('code')->index(); // Barcode or SKU
            $table->decimal('purchase_price', 12, 2)->default(0.00); // Cost Price
            $table->decimal('retailer_price', 12, 2)->default(0.00); // Standard Retail POS Price
            $table->decimal('dealer_price', 12, 2)->default(0.00);   // Dealer tier price
            $table->decimal('wholesale_price', 12, 2)->default(0.00);// Wholesaler tier price
            $table->decimal('stock', 12, 2)->default(0.00);
            $table->decimal('alert_quantity', 12, 2)->default(5.00);
            $table->decimal('tax_rate', 5, 2)->default(0.00); // %
            $table->string('image_url')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 7. Parties (Customers & Suppliers)
        Schema::create('parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->enum('type', ['customer', 'supplier'])->default('customer');
            $table->enum('customer_tier', ['retailer', 'dealer', 'wholesaler'])->default('retailer');
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->decimal('opening_balance', 12, 2)->default(0.00);
            $table->decimal('current_balance', 12, 2)->default(0.00); // positive = they owe us, negative = we owe them
            $table->decimal('credit_limit', 12, 2)->default(0.00);
            $table->timestamps();
        });

        // 8. Sales & Sale Items (POS Invoices)
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('party_id')->nullable()->constrained('parties')->nullOnDelete(); // Customer
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Cashier
            $table->string('invoice_number')->unique();
            $table->dateTime('sale_date');
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->decimal('paid_amount', 12, 2)->default(0.00);
            $table->decimal('due_amount', 12, 2)->default(0.00);
            $table->decimal('profit_amount', 12, 2)->default(0.00);
            $table->enum('payment_method', ['cash', 'card', 'bank', 'due', 'split'])->default('cash');
            $table->enum('status', ['completed', 'pending', 'cancelled'])->default('completed');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('product_name');
            $table->decimal('quantity', 12, 2);
            $table->decimal('purchase_price', 12, 2); // For calculating real-time profit
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });

        // 9. Purchases & Purchase Items
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('party_id')->nullable()->constrained('parties')->nullOnDelete(); // Supplier
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('purchase_number')->unique();
            $table->dateTime('purchase_date');
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->decimal('paid_amount', 12, 2)->default(0.00);
            $table->decimal('due_amount', 12, 2)->default(0.00);
            $table->enum('payment_method', ['cash', 'bank', 'due'])->default('cash');
            $table->enum('status', ['received', 'pending'])->default('received');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('product_name');
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });

        // 10. Quotations & Quotation Items
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('party_id')->nullable()->constrained('parties')->nullOnDelete();
            $table->string('quotation_number')->unique();
            $table->dateTime('quotation_date');
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->enum('status', ['pending', 'converted', 'expired'])->default('pending');
            $table->timestamps();
        });

        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('product_name');
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });

        // 11. Returns (Sale Returns & Purchase Returns)
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->enum('type', ['sale_return', 'purchase_return']);
            $table->string('return_number')->unique();
            $table->foreignId('reference_sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->foreignId('reference_purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
            $table->foreignId('party_id')->nullable()->constrained('parties')->nullOnDelete();
            $table->decimal('total_refund', 12, 2)->default(0.00);
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained('returns')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });

        // 12. Due Collections / Payments (Customer Dues & Supplier Payments)
        Schema::create('due_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('party_id')->constrained('parties')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('voucher_number')->unique();
            $table->decimal('amount', 12, 2);
            $table->dateTime('payment_date');
            $table->enum('payment_method', ['cash', 'card', 'bank'])->default('cash');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // 13. Ledgers (Audit Trail / Financial Bookkeeping)
        Schema::create('ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('party_id')->constrained('parties')->cascadeOnDelete();
            $table->enum('transaction_type', ['sale', 'purchase', 'sale_return', 'purchase_return', 'due_payment', 'supplier_payment']);
            $table->string('reference_no')->nullable();
            $table->decimal('debit', 12, 2)->default(0.00);
            $table->decimal('credit', 12, 2)->default(0.00);
            $table->decimal('balance', 12, 2)->default(0.00);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 14. Expenses & Incomes
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->string('for_what')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('income_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('income_categories')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('income_date');
            $table->string('from_where')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // 15. HRM (Human Resource Management)
        Schema::create('hrm_designations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->string('title');
            $table->timestamps();
        });

        Schema::create('hrm_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained('hrm_designations')->nullOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->decimal('salary', 12, 2)->default(0.00);
            $table->date('joining_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('hrm_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('hrm_employees')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->string('month_year'); // e.g., "October 2026"
            $table->enum('payment_method', ['cash', 'bank'])->default('cash');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // 16. Offline Payment Verification for SaaS Subscription upgrades
        Schema::create('payment_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method'); // Bank, Wire, Bkash
            $table->string('transaction_number');
            $table->string('proof_image_url')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_verifications');
        Schema::dropIfExists('hrm_salaries');
        Schema::dropIfExists('hrm_employees');
        Schema::dropIfExists('hrm_designations');
        Schema::dropIfExists('incomes');
        Schema::dropIfExists('income_categories');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('ledgers');
        Schema::dropIfExists('due_payments');
        Schema::dropIfExists('return_items');
        Schema::dropIfExists('returns');
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('parties');
        Schema::dropIfExists('products');
        Schema::dropIfExists('units');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('warehouses');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['shop_id']);
            $table->dropColumn(['shop_id', 'role', 'phone', 'is_active', 'permissions']);
        });

        Schema::dropIfExists('shops');
        Schema::dropIfExists('shop_categories');
        Schema::dropIfExists('plans');
    }
};
